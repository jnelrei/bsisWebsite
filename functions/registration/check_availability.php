<?php
session_start();

require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok'      => false,
        'message' => 'Method not allowed',
    ]);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');

if ($username === '' && $email === '') {
    http_response_code(400);
    echo json_encode([
        'ok'      => false,
        'message' => 'No username or email provided.',
    ]);
    exit;
}

try {
    $pdo = getPDO();

    $usernameTaken = false;
    $emailTaken    = false;

    if ($username !== '') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $usernameTaken = (int) $stmt->fetchColumn() > 0;
    }

    if ($email !== '') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $emailTaken = (int) $stmt->fetchColumn() > 0;
    }

    echo json_encode([
        'ok'            => true,
        'usernameTaken' => $usernameTaken,
        'emailTaken'    => $emailTaken,
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'message' => 'Error checking availability.',
    ]);
    exit;
}



