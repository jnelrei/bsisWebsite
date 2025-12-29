<?php
session_start();

require_once __DIR__ . '/../db/database.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit;
}

$student_id = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($student_id === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter both student ID and password.';
    header('Location: ../../index.php');
    exit;
}

try {
    $pdo = getPDO();

    // Query the students table using student_id
    $stmt = $pdo->prepare('SELECT student_id, password FROM students WHERE student_id = :student_id LIMIT 1');
    $stmt->execute([':student_id' => $student_id]);
    $student = $stmt->fetch();

    if (!$student || !password_verify($password, $student['password'])) {
        $_SESSION['login_error'] = 'Invalid student ID or password.';
        header('Location: ../../index.php');
        exit;
    }

    // Login success: store student info in session
    $_SESSION['student_id'] = $student['student_id'];
    $_SESSION['user_role'] = 'student';

    // Redirect student to student main page after login
    header('Location: ../../student/main.php');
    exit;
} catch (Throwable $e) {
    // Log error in real apps; for now, simple message + redirect
    $_SESSION['login_error'] = 'An unexpected error occurred while logging in.';
    header('Location: ../../index.php');
    exit;
}


