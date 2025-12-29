<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_projects.php');
    exit;
}

$project_id = $_POST['project_id'] ?? 0;

if (!$project_id) {
    $_SESSION['project_error'] = 'Project ID is required.';
    header('Location: manage_projects.php');
    exit;
}

try {
    $pdo = getPDO();
    
    // Verify project belongs to student
    $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'student_id'");
    $has_student_id = $stmt->rowCount() > 0;
    
    if ($has_student_id) {
        $checkStmt = $pdo->prepare('SELECT project_id FROM projects WHERE project_id = :project_id AND student_id = :student_id');
        $checkStmt->execute([':project_id' => $project_id, ':student_id' => $student_id]);
    } else {
        $checkJunction = $pdo->query("SHOW TABLES LIKE 'student_projects'");
        if ($checkJunction->rowCount() > 0) {
            $checkStmt = $pdo->prepare('SELECT p.project_id FROM projects p 
                INNER JOIN student_projects sp ON p.project_id = sp.project_id 
                WHERE p.project_id = :project_id AND sp.student_id = :student_id');
            $checkStmt->execute([':project_id' => $project_id, ':student_id' => $student_id]);
        } else {
            $checkStmt = $pdo->prepare('SELECT project_id FROM projects WHERE project_id = :project_id');
            $checkStmt->execute([':project_id' => $project_id]);
        }
    }
    
    if (!$checkStmt->fetch()) {
        $_SESSION['project_error'] = 'Project not found or you do not have permission to edit it.';
        header('Location: manage_projects.php');
        exit;
    }
    
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $live_demo_url = trim($_POST['live_demo_url'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $figma_url = trim($_POST['figma_url'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Validate required fields
    if (empty($title)) {
        $_SESSION['project_error'] = 'Project title is required.';
        header('Location: edit_project.php?id=' . $project_id);
        exit;
    }
    
    if (empty($description)) {
        $_SESSION['project_error'] = 'Project description is required.';
        header('Location: edit_project.php?id=' . $project_id);
        exit;
    }
    
    if (empty($category)) {
        $_SESSION['project_error'] = 'Project category is required.';
        header('Location: edit_project.php?id=' . $project_id);
        exit;
    }
    
    // Validate category
    $valid_categories = ['Web Development', 'Mobile App', 'Figma Design', 'UI/UX', 'Desktop App', 'Other'];
    if (!in_array($category, $valid_categories)) {
        $_SESSION['project_error'] = 'Invalid category selected.';
        header('Location: edit_project.php?id=' . $project_id);
        exit;
    }
    
    // Get current project data
    $currentStmt = $pdo->prepare('SELECT thumbnail, banner_image, slug FROM projects WHERE project_id = :project_id');
    $currentStmt->execute([':project_id' => $project_id]);
    $current = $currentStmt->fetch();
    
    // Generate new slug if title changed
    $slug = $current['slug'] ?? '';
    $new_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $new_slug = preg_replace('/-+/', '-', $new_slug);
    $new_slug = trim($new_slug, '-');
    
    if ($new_slug !== $slug) {
        // Ensure slug is unique
        $original_slug = $new_slug;
        $counter = 1;
        while (true) {
            $checkSlugStmt = $pdo->prepare('SELECT project_id FROM projects WHERE slug = :slug AND project_id != :project_id');
            $checkSlugStmt->execute([':slug' => $new_slug, ':project_id' => $project_id]);
            if ($checkSlugStmt->fetch()) {
                $new_slug = $original_slug . '-' . $counter;
                $counter++;
            } else {
                break;
            }
        }
        $slug = $new_slug;
    }
    
    // Handle thumbnail upload
    $thumbnail = $current['thumbnail'];
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['thumbnail'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['project_error'] = 'Invalid thumbnail file type. Only JPG, PNG, GIF, and WEBP are allowed.';
            header('Location: edit_project.php?id=' . $project_id);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['project_error'] = 'Thumbnail file size exceeds 5MB limit.';
            header('Location: edit_project.php?id=' . $project_id);
            exit;
        }
        
        // Delete old thumbnail
        if ($thumbnail && file_exists($upload_dir . $thumbnail)) {
            unlink($upload_dir . $thumbnail);
        }
        
        $new_filename = 'project_thumb_' . $student_id . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $thumbnail = $new_filename;
        }
    }
    
    // Handle banner image upload
    $banner_image = $current['banner_image'];
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['banner_image'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['project_error'] = 'Invalid banner file type. Only JPG, PNG, GIF, and WEBP are allowed.';
            header('Location: edit_project.php?id=' . $project_id);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['project_error'] = 'Banner file size exceeds 5MB limit.';
            header('Location: edit_project.php?id=' . $project_id);
            exit;
        }
        
        // Delete old banner
        if ($banner_image && file_exists($upload_dir . $banner_image)) {
            unlink($upload_dir . $banner_image);
        }
        
        $new_filename = 'project_banner_' . $student_id . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $banner_image = $new_filename;
        }
    }
    
    // Handle multiple project images upload
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Check if project_images table exists
    $checkImagesTable = $pdo->query("SHOW TABLES LIKE 'project_images'");
    if ($checkImagesTable->rowCount() > 0) {
        // Get current max sort_order
        $maxOrderStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) as max_order FROM project_images WHERE project_id = :project_id');
        $maxOrderStmt->execute([':project_id' => $project_id]);
        $maxOrderResult = $maxOrderStmt->fetch();
        $sort_order = ($maxOrderResult['max_order'] ?? -1) + 1;
        
        // Handle multiple images upload
        if (isset($_FILES['project_images']) && is_array($_FILES['project_images']['name'])) {
            $files = $_FILES['project_images'];
            $file_count = count($files['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file_ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($file_ext, $allowed_exts)) {
                        continue; // Skip invalid files
                    }
                    
                    if ($files['size'][$i] > 5 * 1024 * 1024) {
                        continue; // Skip files over 5MB
                    }
                    
                    $new_filename = 'project_img_' . $student_id . '_' . $project_id . '_' . time() . '_' . $i . '.' . $file_ext;
                    $target_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                        $imageStmt = $pdo->prepare('INSERT INTO project_images (project_id, image_path, alt_text, is_primary, sort_order) VALUES (:project_id, :image_path, :alt_text, :is_primary, :sort_order)');
                        $imageStmt->execute([
                            ':project_id' => $project_id,
                            ':image_path' => $new_filename,
                            ':alt_text' => null,
                            ':is_primary' => 0,
                            ':sort_order' => $sort_order++
                        ]);
                    }
                }
            }
        }
    }
    
    // Update project
    $sql = 'UPDATE projects SET title = :title, slug = :slug, description = :description, 
            short_description = :short_description, category = :category, technologies = :technologies, 
            figma_url = :figma_url, live_demo_url = :live_demo_url, github_url = :github_url, 
            thumbnail = :thumbnail, banner_image = :banner_image, is_featured = :is_featured, 
            is_published = :is_published WHERE project_id = :project_id';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
        ':description' => $description,
        ':short_description' => $short_description ?: null,
        ':category' => $category,
        ':technologies' => $technologies ?: null,
        ':figma_url' => $figma_url ?: null,
        ':live_demo_url' => $live_demo_url ?: null,
        ':github_url' => $github_url ?: null,
        ':thumbnail' => $thumbnail,
        ':banner_image' => $banner_image,
        ':is_featured' => $is_featured,
        ':is_published' => $is_published,
        ':project_id' => $project_id
    ]);
    
    $_SESSION['project_success'] = 'Project updated successfully!';
    header('Location: manage_projects.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['project_error'] = 'Error updating project: ' . $e->getMessage();
    header('Location: edit_project.php?id=' . $project_id);
    exit;
}
?>










