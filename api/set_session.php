<?php
/**
 * Session Handler for API Login
 * Sets session after successful API authentication
 */

session_start();

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ]);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data.',
    ]);
    exit;
}

try {
    // Set session based on user type
    if (isset($data['user_type'])) {
        if ($data['user_type'] === 'student') {
            $_SESSION['student_id'] = $data['user_id'] ?? $data['student_id'] ?? '';
            $_SESSION['user_role'] = 'student';
            $_SESSION['fullname'] = $data['fullname'] ?? $data['name'] ?? '';
            $_SESSION['logged_in'] = true;
        } else if ($data['user_type'] === 'user') {
            $_SESSION['user_id'] = $data['user_id'] ?? '';
            $_SESSION['username'] = $data['username'] ?? '';
            $_SESSION['name'] = $data['fullname'] ?? $data['name'] ?? '';
            $_SESSION['fullname'] = $data['fullname'] ?? $data['name'] ?? '';
            $_SESSION['role'] = $data['role'] ?? 'user';
            $_SESSION['logged_in'] = true;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Session created successfully.',
    ]);
    exit;

} catch (Throwable $e) {
    error_log('Session Handler Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while creating session.',
    ]);
    exit;
}
?>


