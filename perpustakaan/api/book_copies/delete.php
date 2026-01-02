<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$copy_id = isset($input['copy_id']) ? intval($input['copy_id']) : 0;

if ($copy_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Copy ID required']);
    exit;
}

try {
    $checkQuery = "SELECT status FROM book_copies WHERE copy_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $copy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Eksemplar tidak ditemukan']);
        exit;
    }
    
    $copy = $result->fetch_assoc();
    
    if ($copy['status'] === 'borrowed') {
        echo json_encode(['success' => false, 'message' => 'Eksemplar sedang dipinjam, tidak bisa dihapus']);
        exit;
    }
    
    $stmt->close();
    
    $deleteQuery = "DELETE FROM book_copies WHERE copy_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $copy_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Eksemplar berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus eksemplar']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>