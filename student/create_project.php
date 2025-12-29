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
    header('Location: add_project.php');
    exit;
}

try {
    $pdo = getPDO();
    
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
        header('Location: add_project.php');
        exit;
    }
    
    if (empty($description)) {
        $_SESSION['project_error'] = 'Project description is required.';
        header('Location: add_project.php');
        exit;
    }
    
    if (empty($category)) {
        $_SESSION['project_error'] = 'Project category is required.';
        header('Location: add_project.php');
        exit;
    }
    
    // Validate category
    $valid_categories = ['Web Development', 'Mobile App', 'Figma Design', 'UI/UX', 'Desktop App', 'Other'];
    if (!in_array($category, $valid_categories)) {
        $_SESSION['project_error'] = 'Invalid category selected.';
        header('Location: add_project.php');
        exit;
    }
    
    // Generate slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Ensure slug is unique
    $original_slug = $slug;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare('SELECT project_id FROM projects WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        } else {
            break;
        }
    }
    
    // Handle thumbnail upload
    $thumbnail = null;
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
            header('Location: add_project.php');
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['project_error'] = 'Thumbnail file size exceeds 5MB limit.';
            header('Location: add_project.php');
            exit;
        }
        
        $new_filename = 'project_thumb_' . $student_id . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $thumbnail = $new_filename;
        }
    }
    
    // Handle banner image upload
    $banner_image = null;
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
            header('Location: add_project.php');
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['project_error'] = 'Banner file size exceeds 5MB limit.';
            header('Location: add_project.php');
            exit;
        }
        
        $new_filename = 'project_banner_' . $student_id . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $banner_image = $new_filename;
        }
    }
    
    // Check if projects table has student_id column
    $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'student_id'");
    $has_student_id = $stmt->rowCount() > 0;
    
    if ($has_student_id) {
        // Insert project with student_id
        $sql = 'INSERT INTO projects (title, slug, description, short_description, category, technologies, 
                figma_url, live_demo_url, github_url, thumbnail, banner_image, is_featured, is_published, student_id) 
                VALUES (:title, :slug, :description, :short_description, :category, :technologies, 
                :figma_url, :live_demo_url, :github_url, :thumbnail, :banner_image, :is_featured, :is_published, :student_id)';
        
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
            ':student_id' => $student_id
        ]);
        
        $project_id = $pdo->lastInsertId();
    } else {
        // Insert project without student_id
        $sql = 'INSERT INTO projects (title, slug, description, short_description, category, technologies, 
                figma_url, live_demo_url, github_url, thumbnail, banner_image, is_featured, is_published) 
                VALUES (:title, :slug, :description, :short_description, :category, :technologies, 
                :figma_url, :live_demo_url, :github_url, :thumbnail, :banner_image, :is_featured, :is_published)';
        
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
            ':is_published' => $is_published
        ]);
        
        $project_id = $pdo->lastInsertId();
        
        // Check if junction table exists and link project to student
        $checkJunction = $pdo->query("SHOW TABLES LIKE 'student_projects'");
        if ($checkJunction->rowCount() > 0) {
            try {
                $linkStmt = $pdo->prepare('INSERT INTO student_projects (student_id, project_id) VALUES (:student_id, :project_id)');
                $linkStmt->execute([':student_id' => $student_id, ':project_id' => $project_id]);
            } catch (Exception $e) {
                // Junction table might have different structure, ignore error
            }
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
        $sort_order = 0;
        $has_primary = false;
        
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
                    
                    // If this is the first image, make it primary
                    $is_primary = ($sort_order == 0) ? 1 : 0;
                    if ($is_primary) {
                        $has_primary = true;
                    }
                    
                    $new_filename = 'project_img_' . $student_id . '_' . $project_id . '_' . time() . '_' . $i . '.' . $file_ext;
                    $target_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                        $imageStmt = $pdo->prepare('INSERT INTO project_images (project_id, image_path, alt_text, is_primary, sort_order) VALUES (:project_id, :image_path, :alt_text, :is_primary, :sort_order)');
                        $imageStmt->execute([
                            ':project_id' => $project_id,
                            ':image_path' => $new_filename,
                            ':alt_text' => null,
                            ':is_primary' => $is_primary,
                            ':sort_order' => $sort_order++
                        ]);
                    }
                }
            }
        }
    }
    
    $_SESSION['project_success'] = 'Project created successfully!';
    header('Location: manage_projects.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['project_error'] = 'Error creating project: ' . $e->getMessage();
    header('Location: add_project.php');
    exit;
}
?>










