<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek login dan role admin
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // ========================================
    // QUERY TANPA username (sudah dihapus dari members)
    // Ambil username dari tabel USERS
    // ========================================
    $sql = "SELECT 
            m.member_id,
            m.member_code,
            u.username,
            m.first_name,
            m.last_name,
            CONCAT(m.first_name, ' ', m.last_name) as full_name,
            m.email,
            m.phone,
            m.address,
            m.birth_date,
            m.gender,
            m.member_type,
            m.member_role,
            m.permissions,
            m.institution,
            m.photo,
            m.status,
            m.join_date,
            m.expired_date,
            m.created_at
            FROM members m
            LEFT JOIN users u ON m.member_id = u.member_id
            ORDER BY m.created_at DESC";
    
    $result = $conn->query($sql);
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            'id' => (int)$row['member_id'],
            'memberNumber' => $row['member_code'],
            'username' => $row['username'], // Dari tabel users
            'firstName' => $row['first_name'],
            'lastName' => $row['last_name'],
            'fullName' => $row['full_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'address' => $row['address'],
            'birthDate' => $row['birth_date'],
            'gender' => $row['gender'],
            'memberType' => $row['member_type'],
            'memberRole' => $row['member_role'] ?? 'library_member',
            'permissions' => $row['permissions'],
            'institution' => $row['institution'],
            'photo' => $row['photo'],
            'status' => $row['status'],
            'joinDate' => $row['join_date'],
            'expireDate' => $row['expired_date'],
            'createdAt' => $row['created_at']
        ];
    }
    
    // ========================================
    // HITUNG STATISTIK
    // ========================================
    $stats = [
        'total' => count($members),
        'active' => count(array_filter($members, fn($m) => $m['status'] === 'active')),
        'inactive' => count(array_filter($members, fn($m) => $m['status'] === 'inactive')),
        'suspended' => count(array_filter($members, fn($m) => $m['status'] === 'suspended')),
        'newThisMonth' => count(array_filter($members, function($m) {
            $joinDate = new DateTime($m['joinDate']);
            $now = new DateTime();
            return $joinDate->format('Y-m') === $now->format('Y-m');
        })),
        'expiringThisMonth' => count(array_filter($members, function($m) {
            $expireDate = new DateTime($m['expireDate']);
            $now = new DateTime();
            return $expireDate->format('Y-m') === $now->format('Y-m');
        }))
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $members,
        'stats' => $stats
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>