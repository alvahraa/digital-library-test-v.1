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
$months = isset($input['months']) ? intval($input['months']) : 12;

if ($memberId <= 0 || !in_array($months, [3, 6, 12, 24])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Get current expire date
    $sql = "SELECT expired_date, first_name, last_name FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Member not found');
    }
    
    $member = $result->fetch_assoc();
    $currentExpire = new DateTime($member['expired_date']);
    $now = new DateTime();
    
    // Jika sudah expired, hitung dari sekarang. Jika belum, tambahkan dari tanggal expire
    $startDate = ($currentExpire < $now) ? $now : $currentExpire;
    $newExpire = clone $startDate;
    $newExpire->modify("+{$months} months");
    $newExpireFormatted = $newExpire->format('Y-m-d');
    
    // Update expire date dan status
    $updateSql = "UPDATE members SET expired_date = ?, status = 'active' WHERE member_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $newExpireFormatted, $memberId);
    
    if ($updateStmt->execute()) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => "Keanggotaan {$member['first_name']} {$member['last_name']} berhasil diperpanjang {$months} bulan",
            'new_expire_date' => $newExpireFormatted
        ]);
    } else {
        throw new Exception('Failed to renew membership');
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