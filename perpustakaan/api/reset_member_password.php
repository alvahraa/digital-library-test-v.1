<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek login dan role admin
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$memberId = isset($input['member_id']) ? intval($input['member_id']) : 0;
$newPassword = isset($input['new_password']) ? trim($input['new_password']) : '';

// Validasi
if ($memberId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
    exit;
}

if (empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Password baru tidak boleh kosong']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Get member info
    $sql = "SELECT m.member_id, m.first_name, m.last_name, u.id as user_id, u.username
            FROM members m
            LEFT JOIN users u ON m.member_id = u.member_id
            WHERE m.member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Anggota tidak ditemukan');
    }
    
    $member = $result->fetch_assoc();
    
    if (!$member['user_id']) {
        throw new Exception('Anggota ini belum memiliki akun login');
    }
    
    // Hash password dengan MD5
    $passwordHash = md5($newPassword);
    
    // Update password di tabel users
    $updateSql = "UPDATE users SET password = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $passwordHash, $member['user_id']);
    
    if ($updateStmt->execute()) {
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Password untuk {$member['first_name']} {$member['last_name']} berhasil direset",
            'username' => $member['username'],
            'new_password' => $newPassword
        ]);
    } else {
        throw new Exception('Gagal mereset password');
    }
    
    $stmt->close();
    $updateStmt->close();
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>