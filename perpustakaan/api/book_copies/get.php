<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

if ($book_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Book ID required']);
    exit;
}

try {
    $copiesQuery = "SELECT * FROM book_copies WHERE book_id = ? ORDER BY copy_number ASC";
    $stmt = $conn->prepare($copiesQuery);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $copiesResult = $stmt->get_result();
    
    $copies = [];
    while ($row = $copiesResult->fetch_assoc()) {
        $copies[] = [
            'copy_id' => (int)$row['copy_id'],
            'book_id' => (int)$row['book_id'],
            'copy_number' => $row['copy_number'],
            'barcode' => $row['barcode'],
            'status' => $row['status'],
            'condition' => $row['condition'],
            'location' => $row['location'],
            'notes' => $row['notes']
        ];
    }
    
    $statusCount = [
        'available' => 0,
        'borrowed' => 0,
        'maintenance' => 0,
        'lost' => 0,
        'damaged' => 0
    ];
    
    foreach ($copies as $copy) {
        if (isset($statusCount[$copy['status']])) {
            $statusCount[$copy['status']]++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'copies' => $copies,
        'summary' => $statusCount
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