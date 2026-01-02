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
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

try {
    $sql = "SELECT 
            member_id,
            member_code,
            username,
            first_name,
            last_name,
            CONCAT(first_name, ' ', last_name) as full_name,
            email,
            phone,
            address,
            birth_date,
            gender,
            member_type,
            institution,
            status,
            join_date,
            expired_date,
            created_at
            FROM members 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Search filter
    if (!empty($search)) {
        $sql .= " AND (
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            CONCAT(first_name, ' ', last_name) LIKE ? OR
            email LIKE ? OR 
            phone LIKE ? OR
            member_code LIKE ? OR
            username LIKE ?
        )";
        $searchParam = "%{$search}%";
        $params = array_fill(0, 7, $searchParam);
        $types = str_repeat("s", 7);
    }
    
    // Status filter
    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            'id' => (int)$row['member_id'],
            'memberNumber' => $row['member_code'],
            'username' => $row['username'],
            'firstName' => $row['first_name'],
            'lastName' => $row['last_name'],
            'fullName' => $row['full_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'address' => $row['address'],
            'birthDate' => $row['birth_date'],
            'gender' => $row['gender'],
            'memberType' => $row['member_type'],
            'institution' => $row['institution'],
            'status' => $row['status'],
            'joinDate' => $row['join_date'],
            'expireDate' => $row['expired_date'],
            'createdAt' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $members,
        'count' => count($members),
        'search' => $search,
        'status' => $status
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