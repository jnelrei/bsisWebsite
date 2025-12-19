<?php
session_start();

require_once __DIR__ . '/../db/database.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter both username and password.';
    header('Location: ../../index.php');
    exit;
}

try {
    $pdo = getPDO();

    // Adjust table/column names to match your actual schema
    $stmt = $pdo->prepare('SELECT id, fullname, email, username, password, role FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: ../../index.php');
        exit;
    }

    // Login success: store minimal user info in session
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['fullname']  = $user['fullname'] ?? '';
    $_SESSION['user_role'] = $user['role'] ?? 'user';

    // Redirect student to student dashboard after login
    header('Location: ../../student/dashboard/main.php');
    exit;
} catch (Throwable $e) {
    // Log error in real apps; for now, simple message + redirect
    $_SESSION['login_error'] = 'An unexpected error occurred while logging in.';
    header('Location: ../../index.php');
    exit;
}


