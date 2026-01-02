<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// LOGIN FUNCTION
// Pakai tabel USERS untuk autentikasi
// ==========================================
function login($username, $password) {
    global $conn;
    
    // Hash password dengan MD5
    $password_hash = md5($password);
    
    // Query ke tabel USERS
    $sql = "SELECT u.id, u.username, u.fullname, u.email, u.role, u.member_id,
                   m.member_code, m.first_name, m.last_name, m.member_role, 
                   m.permissions, m.status, m.photo
            FROM users u
            LEFT JOIN members m ON u.member_id = m.member_id
            WHERE u.username = ? AND u.password = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Jika role = member, cek status keanggotaan
        if ($user['role'] === 'member') {
            if ($user['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
                ];
            }
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['member_id'] = $user['member_id'];
        $_SESSION['member_code'] = $user['member_code'];
        $_SESSION['member_role'] = $user['member_role'];
        $_SESSION['permissions'] = $user['permissions'];
        $_SESSION['photo'] = $user['photo'];
        $_SESSION['logged_in'] = true;
        
        return [
            'success' => true,
            'role' => $user['role']
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Username atau password salah!'
    ];
}

// ==========================================
// CHECK IF USER IS LOGGED IN
// ==========================================
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// ==========================================
// CHECK USER PERMISSION
// ==========================================
function has_permission($permission) {
    if (!isset($_SESSION['permissions'])) {
        return false;
    }
    
    $permissions = json_decode($_SESSION['permissions'], true);
    return isset($permissions[$permission]) && $permissions[$permission] === true;
}

// ==========================================
// GET LOGGED IN USER INFO
// GANTI NAMA: get_current_user() -> get_logged_user()
// ==========================================
function get_logged_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'fullname' => $_SESSION['fullname'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'member_id' => $_SESSION['member_id'] ?? null,
        'member_code' => $_SESSION['member_code'] ?? null,
        'member_role' => $_SESSION['member_role'] ?? null,
        'photo' => $_SESSION['photo'] ?? null,
    ];
}

// ==========================================
// LOGOUT FUNCTION
// ==========================================
function logout() {
    session_destroy();
    session_start();
}

// ==========================================
// REDIRECT FUNCTION
// ==========================================
function redirect($url) {
    header("Location: $url");
    exit();
}

// ==========================================
// SANITIZE INPUT
// ==========================================
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}
?>