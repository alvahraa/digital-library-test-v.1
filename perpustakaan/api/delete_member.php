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

if ($memberId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // ========================================
    // CEK TRANSAKSI AKTIF (BORROWED)
    // ========================================
    $checkSql = "SELECT COUNT(*) as total FROM transactions 
                 WHERE member_id = ? AND status = 'borrowed'";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $memberId);
    $checkStmt->execute();
    $activeBorrows = $checkStmt->get_result()->fetch_assoc()['total'];
    
    if ($activeBorrows > 0) {
        throw new Exception('Tidak dapat menghapus anggota yang masih memiliki peminjaman aktif. Silakan kembalikan buku terlebih dahulu atau ubah status menjadi "Tidak Aktif".');
    }
    
    // ========================================
    // GET PHOTO UNTUK DIHAPUS
    // ========================================
    $photoSql = "SELECT photo FROM members WHERE member_id = ?";
    $photoStmt = $conn->prepare($photoSql);
    $photoStmt->bind_param("i", $memberId);
    $photoStmt->execute();
    $photoResult = $photoStmt->get_result();
    
    if ($photoResult->num_rows > 0) {
        $photo = $photoResult->fetch_assoc()['photo'];
        if ($photo) {
            $photoPath = __DIR__ . '/../uploads/members/' . $photo;
            if (file_exists($photoPath)) {
                @unlink($photoPath);
            }
        }
    }
    
    // ========================================
    // HAPUS USER ACCOUNT TERLEBIH DAHULU
    // ========================================
    $deleteUserSql = "DELETE FROM users WHERE member_id = ?";
    $deleteUserStmt = $conn->prepare($deleteUserSql);
    $deleteUserStmt->bind_param("i", $memberId);
    $deleteUserStmt->execute();
    
    // ========================================
    // UPDATE TRANSACTIONS: SET member_id = NULL
    // Jangan hapus transaksi, tapi putuskan relasi
    // ========================================
    $updateTrxSql = "UPDATE transactions SET member_id = NULL WHERE member_id = ?";
    $updateTrxStmt = $conn->prepare($updateTrxSql);
    $updateTrxStmt->bind_param("i", $memberId);
    $updateTrxStmt->execute();
    
    // ========================================
    // HAPUS MEMBER
    // ========================================
    $deleteSql = "DELETE FROM members WHERE member_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $memberId);
    
    if ($deleteStmt->execute()) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Anggota berhasil dihapus. Riwayat transaksi lama tetap tersimpan.'
        ]);
    } else {
        throw new Exception('Gagal menghapus anggota');
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>