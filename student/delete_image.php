<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

$student_id = $_SESSION['student_id'];
$project_id = $_GET['project_id'] ?? 0;
$image_id = $_GET['image_id'] ?? 0;

if (!$project_id || !$image_id) {
    $_SESSION['project_error'] = 'Project ID and Image ID are required.';
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
        $_SESSION['project_error'] = 'Project not found or you do not have permission to delete images.';
        header('Location: manage_projects.php');
        exit;
    }
    
    // Get image info
    $checkImagesTable = $pdo->query("SHOW TABLES LIKE 'project_images'");
    if ($checkImagesTable->rowCount() > 0) {
        $imageStmt = $pdo->prepare('SELECT image_path, is_primary FROM project_images WHERE image_id = :image_id AND project_id = :project_id');
        $imageStmt->execute([':image_id' => $image_id, ':project_id' => $project_id]);
        $image = $imageStmt->fetch();
        
        if ($image) {
            // Delete file
            $upload_dir = __DIR__ . '/uploads/';
            if ($image['image_path'] && file_exists($upload_dir . $image['image_path'])) {
                unlink($upload_dir . $image['image_path']);
            }
            
            // Delete from database
            $deleteStmt = $pdo->prepare('DELETE FROM project_images WHERE image_id = :image_id');
            $deleteStmt->execute([':image_id' => $image_id]);
            
            // If this was the primary image, set the first remaining image as primary
            if ($image['is_primary']) {
                $firstImage = $pdo->prepare('SELECT image_id FROM project_images WHERE project_id = :project_id ORDER BY sort_order ASC, image_id ASC LIMIT 1');
                $firstImage->execute([':project_id' => $project_id]);
                $first = $firstImage->fetch();
                if ($first) {
                    $pdo->prepare('UPDATE project_images SET is_primary = 1 WHERE image_id = :image_id')->execute([':image_id' => $first['image_id']]);
                }
            }
            
            $_SESSION['project_success'] = 'Image deleted successfully!';
        } else {
            $_SESSION['project_error'] = 'Image not found.';
        }
    }
    
    header('Location: edit_project.php?id=' . $project_id);
    exit;
    
} catch (Exception $e) {
    $_SESSION['project_error'] = 'Error deleting image: ' . $e->getMessage();
    header('Location: edit_project.php?id=' . $project_id);
    exit;
}
?>






