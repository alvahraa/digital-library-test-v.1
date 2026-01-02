<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$copy_id = isset($input['copy_id']) ? intval($input['copy_id']) : 0;
$new_status = isset($input['status']) ? trim($input['status']) : '';

$valid_statuses = ['available', 'borrowed', 'maintenance', 'lost', 'damaged', 'reserved'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

try {
    $query = "UPDATE book_copies SET status = ? WHERE copy_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_status, $copy_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil diubah']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengubah status']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>