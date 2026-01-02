<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek login
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

$bookId = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

if ($bookId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // 1. Ambil info user dan member
    $userSql = "SELECT u.id, u.member_id, u.email,
                m.member_code, m.full_name, m.phone, m.member_type, m.status, m.expired_date
                FROM users u
                LEFT JOIN members m ON u.member_id = m.member_id
                WHERE u.id = ?";
    
    $userStmt = $conn->prepare($userSql);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        throw new Exception('User tidak ditemukan');
    }
    
    $user = $userResult->fetch_assoc();
    
    // Validasi member
    if (empty($user['member_id'])) {
        throw new Exception('Anda belum terdaftar sebagai anggota perpustakaan');
    }
    
    if ($user['status'] !== 'active') {
        throw new Exception('Status keanggotaan Anda tidak aktif');
    }
    
    $memberId = $user['member_id'];
    
    // 2. Ambil info buku
    $bookSql = "SELECT b.*, 
                (b.total_copies - COALESCE((SELECT COUNT(*) FROM transactions t 
                    WHERE t.book_id = b.id AND t.status = 'borrowed'), 0)) as current_available
                FROM books b 
                WHERE b.id = ?";
    
    $bookStmt = $conn->prepare($bookSql);
    $bookStmt->bind_param("i", $bookId);
    $bookStmt->execute();
    $bookResult = $bookStmt->get_result();
    
    if ($bookResult->num_rows === 0) {
        throw new Exception('Buku tidak ditemukan');
    }
    
    $book = $bookResult->fetch_assoc();
    
    // 3. Hitung pinjaman aktif
    $activeLoansSql = "SELECT COUNT(*) as total FROM transactions 
                       WHERE member_id = ? AND status = 'borrowed'";
    $activeStmt = $conn->prepare($activeLoansSql);
    $activeStmt->bind_param("i", $memberId);
    $activeStmt->execute();
    $activeResult = $activeStmt->get_result();
    $activeData = $activeResult->fetch_assoc();
    $activeLoans = $activeData['total'];
    
    // 4. Cek keterlambatan dan denda
    $overdueSql = "SELECT SUM(fine_amount) as total_fine
                   FROM transactions
                   WHERE member_id = ? 
                   AND status = 'borrowed' 
                   AND due_date < CURDATE()
                   AND fine_amount > 0";
    
    $overdueStmt = $conn->prepare($overdueSql);
    $overdueStmt->bind_param("i", $memberId);
    $overdueStmt->execute();
    $overdueResult = $overdueStmt->get_result();
    $overdueData = $overdueResult->fetch_assoc();
    $totalFine = $overdueData['total_fine'] ?? 0;
    
    // 5. Hitung tanggal peminjaman
    $borrowDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime('+7 days'));
    
    // Format tanggal Indonesia
    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    $borrowDateParts = explode('-', $borrowDate);
    $dueDateParts = explode('-', $dueDate);
    
    $borrowDateFormatted = $borrowDateParts[2] . ' ' . $months[(int)$borrowDateParts[1]] . ' ' . $borrowDateParts[0];
    $dueDateFormatted = $dueDateParts[2] . ' ' . $months[(int)$dueDateParts[1]] . ' ' . $dueDateParts[0];
    
    // Member type labels
    $memberTypeLabels = [
        'student' => 'Pelajar/Mahasiswa',
        'teacher' => 'Guru/Dosen',
        'public' => 'Umum'
    ];
    
    // Response
    echo json_encode([
        'success' => true,
        'member' => [
            'member_code' => $user['member_code'],
            'full_name' => $user['full_name'],
            'phone' => $user['phone'] ?? '-',
            'member_type' => $user['member_type'],
            'member_type_label' => $memberTypeLabels[$user['member_type']] ?? $user['member_type'],
            'status' => $user['status'],
            'status_label' => $user['status'] === 'active' ? 'Aktif' : 'Tidak Aktif',
            'expired_date' => $user['expired_date']
        ],
        'book' => [
            'id' => $book['id'],
            'title' => $book['title'],
            'author' => $book['author'],
            'isbn' => $book['isbn'],
            'cover_image' => $book['cover_image'],
            'available_copies' => $book['current_available']
        ],
        'loan' => [
            'borrow_date' => $borrowDateFormatted,
            'due_date' => $dueDateFormatted,
            'loan_days' => 7
        ],
        'active_loans' => $activeLoans,
        'has_overdue' => $totalFine > 0,
        'total_fine' => (int)$totalFine
    ]);
    
    $userStmt->close();
    $bookStmt->close();
    $activeStmt->close();
    $overdueStmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>