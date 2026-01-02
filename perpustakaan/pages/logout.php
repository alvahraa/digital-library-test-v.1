<?php
require_once __DIR__ . '/../includes/config.php';

// Hapus semua session variables
$_SESSION = array();

// Hapus session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hapus remember me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['logout_success'] = true;

// Redirect ke index with SweetAlert2 notification
redirect('../index.php');
?>