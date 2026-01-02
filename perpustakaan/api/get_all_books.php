<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek login dan role admin
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Build query - OPTIMIZED dengan LEFT JOIN
    $sql = "SELECT b.*,
            b.available_copies as current_available
            FROM books b 
            WHERE 1=1";
    
    $countSql = "SELECT COUNT(*) as total FROM books b WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Search filter
    if (!empty($search)) {
        $searchCondition = " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
        $sql .= $searchCondition;
        $countSql .= $searchCondition;
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $types .= "sss";
    }
    
    // Category filter
    if (!empty($category)) {
        $categoryCondition = " AND b.category = ?";
        $sql .= $categoryCondition;
        $countSql .= $categoryCondition;
        $params[] = $category;
        $types .= "s";
    }
    
    // Count total - SIMPLE & FAST
    if (!empty($params)) {
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
        $countStmt->close();
    } else {
        $result = $conn->query($countSql);
        $totalRecords = $result->fetch_assoc()['total'];
    }
    
    $totalPages = max(1, ceil($totalRecords / $limit));
    
    // Get data with pagination
    $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = [
            'id' => (int)$row['id'],
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
            'total_copies' => (int)$row['total_copies'],
            'available_copies' => (int)$row['current_available'],
            'cover_image' => $row['cover_image'],
            'description' => $row['description'],
            'created_at' => $row['created_at']
        ];
    }
    
    // Output JSON
    echo json_encode([
        'success' => true,
        'data' => $books,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit
        ]
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>