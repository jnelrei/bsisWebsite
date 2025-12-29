<?php
session_start();

// Destroy session
session_unset();
session_destroy();

// Clear any cookies
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redirect to index page
header('Location: ../index.php');
exit;
?>
















