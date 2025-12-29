<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

$student_id = $_SESSION['student_id'];
$project_id = $_GET['id'] ?? 0;

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
        $checkStmt = $pdo->prepare('SELECT thumbnail, banner_image FROM projects WHERE project_id = :project_id AND student_id = :student_id');
        $checkStmt->execute([':project_id' => $project_id, ':student_id' => $student_id]);
    } else {
        $checkJunction = $pdo->query("SHOW TABLES LIKE 'student_projects'");
        if ($checkJunction->rowCount() > 0) {
            $checkStmt = $pdo->prepare('SELECT p.thumbnail, p.banner_image FROM projects p 
                INNER JOIN student_projects sp ON p.project_id = sp.project_id 
                WHERE p.project_id = :project_id AND sp.student_id = :student_id');
            $checkStmt->execute([':project_id' => $project_id, ':student_id' => $student_id]);
        } else {
            $checkStmt = $pdo->prepare('SELECT thumbnail, banner_image FROM projects WHERE project_id = :project_id');
            $checkStmt->execute([':project_id' => $project_id]);
        }
    }
    
    $project = $checkStmt->fetch();
    
    if (!$project) {
        $_SESSION['project_error'] = 'Project not found or you do not have permission to delete it.';
        header('Location: manage_projects.php');
        exit;
    }
    
    // Delete associated images
    $upload_dir = __DIR__ . '/uploads/';
    if ($project['thumbnail'] && file_exists($upload_dir . $project['thumbnail'])) {
        unlink($upload_dir . $project['thumbnail']);
    }
    if ($project['banner_image'] && file_exists($upload_dir . $project['banner_image'])) {
        unlink($upload_dir . $project['banner_image']);
    }
    
    // Delete project_images records and files
    $checkImagesTable = $pdo->query("SHOW TABLES LIKE 'project_images'");
    if ($checkImagesTable->rowCount() > 0) {
        $imagesStmt = $pdo->prepare('SELECT image_path FROM project_images WHERE project_id = :project_id');
        $imagesStmt->execute([':project_id' => $project_id]);
        $project_images = $imagesStmt->fetchAll();
        
        foreach ($project_images as $img) {
            if ($img['image_path'] && file_exists($upload_dir . $img['image_path'])) {
                unlink($upload_dir . $img['image_path']);
            }
        }
        
        // Note: CASCADE DELETE should handle database deletion, but we'll do it explicitly for safety
        $deleteImagesStmt = $pdo->prepare('DELETE FROM project_images WHERE project_id = :project_id');
        $deleteImagesStmt->execute([':project_id' => $project_id]);
    }
    
    // Delete from junction table if exists
    $checkJunction = $pdo->query("SHOW TABLES LIKE 'student_projects'");
    if ($checkJunction->rowCount() > 0) {
        try {
            $deleteJunction = $pdo->prepare('DELETE FROM student_projects WHERE project_id = :project_id');
            $deleteJunction->execute([':project_id' => $project_id]);
        } catch (Exception $e) {
            // Ignore junction table errors
        }
    }
    
    // Delete project
    $deleteStmt = $pdo->prepare('DELETE FROM projects WHERE project_id = :project_id');
    $deleteStmt->execute([':project_id' => $project_id]);
    
    $_SESSION['project_success'] = 'Project deleted successfully!';
    header('Location: manage_projects.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['project_error'] = 'Error deleting project: ' . $e->getMessage();
    header('Location: manage_projects.php');
    exit;
}
?>










