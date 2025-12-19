<?php
session_start();

require_once __DIR__ . '/../db/database.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit;
}

$fullname         = trim($_POST['fullname'] ?? '');
$email            = trim($_POST['email'] ?? '');
$username         = trim($_POST['username'] ?? '');
$password         = $_POST['password'] ?? '';
$confirmPassword  = $_POST['confirm_password'] ?? '';

if ($fullname === '' || $email === '' || $username === '' || $password === '' || $confirmPassword === '') {
    $_SESSION['signup_error'] = 'All fields are required.';
    header('Location: ../../index.php');
    exit;
}

if ($password !== $confirmPassword) {
    $_SESSION['signup_error'] = 'Passwords do not match.';
    header('Location: ../../index.php');
    exit;
}

try {
    $pdo = getPDO();

    // Check if username or email already exists
    $checkStmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE username = :username OR email = :email');
    $checkStmt->execute([
        ':username' => $username,
        ':email'    => $email,
    ]);

    $exists = (int) $checkStmt->fetchColumn() > 0;

    if ($exists) {
        $_SESSION['signup_error'] = 'Username or email already exists.';
        header('Location: ../../index.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Adjust table and columns if your schema differs
    $insertStmt = $pdo->prepare(
        'INSERT INTO users (fullname, email, username, password, role, created_at)
         VALUES (:fullname, :email, :username, :password, :role, NOW())'
    );

    $insertStmt->execute([
        ':fullname' => $fullname,
        ':email'    => $email,
        ':username' => $username,
        ':password' => $hashedPassword,
        ':role'     => 'user',
    ]);

    $_SESSION['signup_success'] = 'Account created successfully. You can now log in.';
    header('Location: ../../index.php');
    exit;
} catch (Throwable $e) {
    // Log in real app
    $_SESSION['signup_error'] = 'An unexpected error occurred while creating your account.';
    header('Location: ../../index.php');
    exit;
}


