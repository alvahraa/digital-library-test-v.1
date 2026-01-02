<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$copy_id = isset($input['copy_id']) ? intval($input['copy_id']) : 0;
$book_id = isset($input['book_id']) ? intval($input['book_id']) : 0;
$copy_number = isset($input['copy_number']) ? trim($input['copy_number']) : '';
$barcode = isset($input['barcode']) ? trim($input['barcode']) : '';
$status = isset($input['status']) ? trim($input['status']) : 'available';
$condition = isset($input['condition']) ? trim($input['condition']) : 'good';
$location = isset($input['location']) ? trim($input['location']) : 'Perpustakaan';
$notes = isset($input['notes']) ? trim($input['notes']) : '';

if ($book_id === 0 || empty($copy_number)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

try {
    if ($copy_id > 0) {
        $query = "UPDATE book_copies SET 
                  copy_number = ?, barcode = ?, status = ?, `condition` = ?, 
                  location = ?, notes = ?
                  WHERE copy_id = ? AND book_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssii", $copy_number, $barcode, $status, $condition, $location, $notes, $copy_id, $book_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Eksemplar berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal update eksemplar']);
        }
    } else {
        $query = "INSERT INTO book_copies (book_id, copy_number, barcode, status, `condition`, location, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issssss", $book_id, $copy_number, $barcode, $status, $condition, $location, $notes);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Eksemplar berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan eksemplar']);
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>