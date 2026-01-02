<?php
/**
 * Public Book Search API
 * No authentication required for public library access
 */
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Get parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';

try {
    // Build query - check both old system and copy tracking
    $sql = "SELECT b.*, 
            COALESCE(
                (SELECT COUNT(*) FROM book_copies bc 
                 WHERE bc.book_id = b.id AND bc.status = 'available'),
                b.available_copies
            ) as current_available
            FROM books b 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Advanced Search filter - Title, Author, Category, ISBN, Publisher
    if (!empty($searchTerm)) {
        $sql .= " AND (
            b.title LIKE ? OR 
            b.author LIKE ? OR 
            b.category LIKE ? OR 
            b.isbn LIKE ? OR 
            b.publisher LIKE ? OR
            b.call_number LIKE ?
        )";
        $searchPattern = "%{$searchTerm}%";
        $params[] = $searchPattern; // title
        $params[] = $searchPattern; // author
        $params[] = $searchPattern; // category
        $params[] = $searchPattern; // isbn
        $params[] = $searchPattern; // publisher
        $params[] = $searchPattern; // call_number
        $types .= "ssssss";
    }
    
    // Category filter
    if ($category !== 'all' && !empty($category)) {
        $sql .= " AND b.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    // Only show books with available copies
    $sql .= " HAVING current_available > 0";
    
    // Sort by title
    $sql .= " ORDER BY b.title ASC";
    
    // Limit results
    $sql .= " LIMIT 100";
    
    // Prepare and execute
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $coverPath = '';
        if (!empty($row['cover_image'])) {
            // Path relative to public_library folder
            $coverPath = '../../uploads/covers/' . $row['cover_image'];
        }
        
        $books[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'author' => $row['author'],
            'isbn' => $row['isbn'],
            'publisher' => $row['publisher'],
            'publish_year' => $row['publish_year'],
            'pages' => isset($row['pages']) ? (int)$row['pages'] : null,
            'category' => $row['category'],
            'call_number' => $row['call_number'],
            'available_copies' => (int)$row['current_available'],
            'cover_image' => $coverPath,
            'description' => $row['description'],
            'status' => $row['current_available'] > 0 ? 'available' : 'unavailable'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($books),
        'books' => $books
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

