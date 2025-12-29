<?php
session_start();
header('Content-Type: application/json');

$inputUsername = isset($_POST['username']) ? trim($_POST['username']) : '';
$inputPassword = isset($_POST['password']) ? $_POST['password'] : '';

if ($inputUsername === '' || $inputPassword === '') {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$host = 'localhost';
$dbname = 'bsis';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT superadmin_id, username, email, password, full_name, status FROM superadmins WHERE username = :ue OR email = :ue LIMIT 1');
    $stmt->execute([':ue' => $inputUsername]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Account not found']);
        exit;
    }

    if ($row['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Account is inactive']);
        exit;
    }

    if (!password_verify($inputPassword, $row['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    $_SESSION['superadmin_logged_in'] = true;
    $_SESSION['superadmin_id'] = $row['superadmin_id'];
    $_SESSION['superadmin_username'] = $row['username'];
    $_SESSION['superadmin_full_name'] = $row['full_name'];
    $_SESSION['superadmin_email'] = $row['email'];

    $upd = $pdo->prepare('UPDATE superadmins SET last_login = CURRENT_TIMESTAMP WHERE superadmin_id = :id');
    $upd->execute([':id' => $row['superadmin_id']]);

    echo json_encode(['success' => true, 'redirect' => '../bsisWebsite-main/superadmin/dashboard/main.php']);
    exit;
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}