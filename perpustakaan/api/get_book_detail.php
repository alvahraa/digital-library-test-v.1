<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($bookId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
    exit;
}

try {
    $sql = "SELECT b.*, 
            (b.total_copies - COALESCE((SELECT COUNT(*) FROM transactions t 
                WHERE t.book_id = b.id AND t.status = 'borrowed'), 0)) as available_copies
            FROM books b 
            WHERE b.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Buku tidak ditemukan'
        ]);
        exit;
    }
    
    $book = $result->fetch_assoc();
    
    // Determine status
    $book['status'] = $book['available_copies'] > 0 ? 'available' : 'borrowed';
    
    echo json_encode([
        'success' => true,
        'book' => $book
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>