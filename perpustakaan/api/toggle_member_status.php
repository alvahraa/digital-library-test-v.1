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
$newStatus = isset($input['status']) ? $input['status'] : '';

// Validasi
if ($memberId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
    exit;
}

if (!in_array($newStatus, ['active', 'inactive', 'suspended'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Get member info
    $sql = "SELECT first_name, last_name, status FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Anggota tidak ditemukan');
    }
    
    $member = $result->fetch_assoc();
    
    // Update status
    $updateSql = "UPDATE members SET status = ? WHERE member_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $memberId);
    
    if ($updateStmt->execute()) {
        $conn->commit();
        
        $statusLabels = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'suspended' => 'Ditangguhkan'
        ];
        
        echo json_encode([
            'success' => true,
            'message' => "Status {$member['first_name']} {$member['last_name']} berhasil diubah menjadi {$statusLabels[$newStatus]}",
            'new_status' => $newStatus
        ]);
    } else {
        throw new Exception('Gagal mengubah status');
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