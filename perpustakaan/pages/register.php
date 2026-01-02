<?php
require_once __DIR__ . '/../includes/config.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil dan bersihkan input
    $fullname = clean_input($_POST['fullname']);
    $email = clean_input($_POST['email']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $confirm_password = clean_input($_POST['confirm_password']);
    
    // Array untuk menyimpan error
    $errors = [];
    
    // Validasi input tidak kosong
    if (empty($fullname)) $errors[] = "Nama lengkap harus diisi!";
    if (empty($email)) $errors[] = "Email harus diisi!";
    if (empty($username)) $errors[] = "Username harus diisi!";
    if (empty($password)) $errors[] = "Password harus diisi!";
    
    // Validasi email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }
    
    // Validasi password match
    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak cocok!";
    }
    
    // Validasi panjang password
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter!";
    }
    
    // Validasi panjang username
    if (strlen($username) < 4) {
        $errors[] = "Username minimal 4 karakter!";
    }
    
    // Cek username sudah ada atau belum
    if (empty($errors)) {
        $check_username = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($check_username, "s", $username);
        mysqli_stmt_execute($check_username);
        mysqli_stmt_store_result($check_username);
        
        if (mysqli_stmt_num_rows($check_username) > 0) {
            $errors[] = "Username sudah digunakan!";
        }
        mysqli_stmt_close($check_username);
    }
    
    // Cek email sudah ada atau belum
    if (empty($errors)) {
        $check_email = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check_email, "s", $email);
        mysqli_stmt_execute($check_email);
        mysqli_stmt_store_result($check_email);
        
        if (mysqli_stmt_num_rows($check_email) > 0) {
            $errors[] = "Email sudah terdaftar!";
        }
        mysqli_stmt_close($check_email);
    }
    
    // Jika tidak ada error, insert ke database
    if (empty($errors)) {
        $password_hash = md5($password);
        $role = 'member'; // Default role
        
        $insert = mysqli_prepare($conn, "INSERT INTO users (fullname, email, username, password, role) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert, "sssss", $fullname, $email, $username, $password_hash, $role);
        
        if (mysqli_stmt_execute($insert)) {
            $_SESSION['success'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
            mysqli_stmt_close($insert);
            redirect('../index.php');
        } else {
            $errors[] = "Terjadi kesalahan saat registrasi. Silakan coba lagi.";
        }
    }
    
    // Jika ada error, simpan ke session dan redirect
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_fullname'] = $fullname;
        $_SESSION['old_email'] = $email;
        $_SESSION['old_username'] = $username;
        redirect('../index.php');
    }
    
} else {
    // Akses langsung tanpa POST
    redirect('../index.php');
}
?>