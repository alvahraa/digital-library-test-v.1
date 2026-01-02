<?php
require_once __DIR__ . '/../includes/config.php';  // Perbaikan: __DIR__ bukan _DIR_

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil dan bersihkan input
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    $remember = isset($_POST['remember']);
    
    // Validasi input tidak kosong
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password harus diisi!";
        redirect('../index.php');
    }
    
    // Panggil fungsi login dari config.php
    $result = login($username, $password);
    
    if ($result['success']) {
        // Set cookie jika remember me dicentang
        if ($remember) {
            $cookie_value = base64_encode($username . ':' . $_SESSION['user_id']);
            setcookie('remember_me', $cookie_value, time() + (86400 * 30), '/');
        }
        
        // Redirect ke dashboard
        $_SESSION['success'] = "Selamat datang, " . $_SESSION['fullname'] . "!";
        redirect('dashboard.php');
    } else {
        // Login gagal
        $_SESSION['error'] = $result['message'];
        redirect('../index.php');
    }
    
} else {
    // Akses langsung tanpa POST, redirect ke index
    redirect('../index.php');
}
?>