<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek apakah user sudah login
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil parameter dari request
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$filterCategory = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'relevance';

try {
    // Build query
    $sql = "SELECT b.*, 
            (b.total_copies - COALESCE((SELECT COUNT(*) FROM transactions t 
                WHERE t.book_id = b.id AND t.status = 'borrowed'), 0)) as current_available
            FROM books b 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Search filter
    if (!empty($searchTerm)) {
        switch($category) {
            case 'title':
                $sql .= " AND b.title LIKE ?";
                $params[] = "%{$searchTerm}%";
                $types .= "s";
                break;
            case 'author':
                $sql .= " AND b.author LIKE ?";
                $params[] = "%{$searchTerm}%";
                $types .= "s";
                break;
            case 'isbn':
                $sql .= " AND b.isbn LIKE ?";
                $params[] = "%{$searchTerm}%";
                $types .= "s";
                break;
            case 'publisher':
                $sql .= " AND b.publisher LIKE ?";
                $params[] = "%{$searchTerm}%";
                $types .= "s";
                break;
            default: // all
                $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.publisher LIKE ?)";
                $params[] = "%{$searchTerm}%";
                $params[] = "%{$searchTerm}%";
                $params[] = "%{$searchTerm}%";
                $params[] = "%{$searchTerm}%";
                $types .= "ssss";
        }
    }
    
    // Category filter - PERBAIKAN: langsung gunakan nama kategori
    if ($filterCategory !== 'all') {
        $sql .= " AND b.category = ?";
        $params[] = $filterCategory;
        $types .= "s";
    }
    
    // Sorting
    switch($sortBy) {
        case 'title':
            $sql .= " ORDER BY b.title ASC";
            break;
        case 'author':
            $sql .= " ORDER BY b.author ASC";
            break;
        case 'year':
            $sql .= " ORDER BY b.publish_year DESC";
            break;
        default: // relevance
            if (!empty($searchTerm)) {
                $sql .= " ORDER BY 
                    CASE 
                        WHEN b.title LIKE ? THEN 1
                        WHEN b.author LIKE ? THEN 2
                        ELSE 3
                    END, b.title ASC";
                $params[] = "%{$searchTerm}%";
                $params[] = "%{$searchTerm}%";
                $types .= "ss";
            } else {
                $sql .= " ORDER BY b.created_at DESC";
            }
    }
    
    // Prepare and execute
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'author' => $row['author'],
            'isbn' => $row['isbn'],
            'publisher' => $row['publisher'],
            'publish_year' => $row['publish_year'],
            'publish_place' => $row['publish_place'],
            'pages' => $row['pages'],
            'language' => $row['language'],
            'category' => $row['category'],
            'call_number' => $row['call_number'],
            'total_copies' => $row['total_copies'],
            'available_copies' => $row['current_available'],
            'cover_image' => $row['cover_image'],
            'description' => $row['description'],
            'status' => $row['current_available'] > 0 ? 'available' : 'borrowed'
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