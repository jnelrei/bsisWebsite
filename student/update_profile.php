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
    header('Location: edit_profile.php');
    exit;
}

try {
    $pdo = getPDO();
    
    // Get form data
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    
    // Validate required fields
    if (empty($fullname)) {
        $_SESSION['profile_error'] = 'Full name is required.';
        header('Location: edit_profile.php');
        exit;
    }
    
    if (empty($email)) {
        $_SESSION['profile_error'] = 'Email is required.';
        header('Location: edit_profile.php');
        exit;
    }
    
    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['profile_picture'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Validate file type
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['profile_error'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
            header('Location: edit_profile.php');
            exit;
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['profile_error'] = 'File size exceeds 5MB limit.';
            header('Location: edit_profile.php');
            exit;
        }
        
        // Generate unique filename
        $new_filename = 'profile_' . $student_id . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Delete old profile picture if exists
            $stmt = $pdo->prepare('SELECT profile_picture FROM students WHERE student_id = :student_id');
            $stmt->execute([':student_id' => $student_id]);
            $old_student = $stmt->fetch();
            
            if ($old_student && !empty($old_student['profile_picture'])) {
                $old_file = $upload_dir . $old_student['profile_picture'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            
            $profile_picture = $new_filename;
        } else {
            $_SESSION['profile_error'] = 'Failed to upload profile picture.';
            header('Location: edit_profile.php');
            exit;
        }
    }
    
    // Build update query dynamically based on available columns
    $update_fields = [];
    $params = [':student_id' => $student_id];
    
    // Check which columns exist and update accordingly
    $stmt = $pdo->prepare("SHOW COLUMNS FROM students");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('fullname', $columns)) {
        $update_fields[] = 'fullname = :fullname';
        $params[':fullname'] = $fullname;
    } elseif (in_array('name', $columns)) {
        $update_fields[] = 'name = :name';
        $params[':name'] = $fullname;
    }
    
    if (in_array('email', $columns)) {
        $update_fields[] = 'email = :email';
        $params[':email'] = $email;
    }
    
    if (in_array('phone', $columns) && !empty($phone)) {
        $update_fields[] = 'phone = :phone';
        $params[':phone'] = $phone;
    }
    
    if (in_array('bio', $columns)) {
        $update_fields[] = 'bio = :bio';
        $params[':bio'] = $bio;
    } elseif (in_array('description', $columns)) {
        $update_fields[] = 'description = :description';
        $params[':description'] = $bio;
    }
    
    if (in_array('skills', $columns)) {
        $update_fields[] = 'skills = :skills';
        $params[':skills'] = $skills;
    }
    
    if (in_array('interests', $columns)) {
        $update_fields[] = 'interests = :interests';
        $params[':interests'] = $interests;
    }
    
    if ($profile_picture && in_array('profile_picture', $columns)) {
        $update_fields[] = 'profile_picture = :profile_picture';
        $params[':profile_picture'] = $profile_picture;
    }
    
    if (empty($update_fields)) {
        $_SESSION['profile_error'] = 'No fields to update.';
        header('Location: edit_profile.php');
        exit;
    }
    
    // Update student profile
    $sql = 'UPDATE students SET ' . implode(', ', $update_fields) . ' WHERE student_id = :student_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $_SESSION['profile_success'] = 'Profile updated successfully!';
    header('Location: edit_profile.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['profile_error'] = 'Error updating profile: ' . $e->getMessage();
    header('Location: edit_profile.php');
    exit;
}
?>




