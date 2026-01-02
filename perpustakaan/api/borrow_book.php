<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek login
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);
$bookId = isset($input['book_id']) ? intval($input['book_id']) : 0;

if ($bookId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Ambil member_id dari user yang login
    $userId = $_SESSION['user_id'];
    $userSql = "SELECT u.member_id, m.member_code, m.status, m.expired_date 
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
    
    // Cek apakah sudah pinjam buku yang sama
    $checkDuplicateSql = "SELECT transaction_id FROM transactions 
                          WHERE member_id = ? AND book_id = ? AND status = 'borrowed'";
    $checkStmt = $conn->prepare($checkDuplicateSql);
    $checkStmt->bind_param("ii", $memberId, $bookId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        throw new Exception('Anda sudah meminjam buku ini');
    }
    
    // Cek jumlah pinjaman aktif (max 3)
    $activeBorrowSql = "SELECT COUNT(*) as total FROM transactions 
                        WHERE member_id = ? AND status = 'borrowed'";
    $borrowStmt = $conn->prepare($activeBorrowSql);
    $borrowStmt->bind_param("i", $memberId);
    $borrowStmt->execute();
    $borrowResult = $borrowStmt->get_result();
    $borrowData = $borrowResult->fetch_assoc();
    
    if ($borrowData['total'] >= 3) {
        throw new Exception('Maksimal meminjam 3 buku. Kembalikan buku terlebih dahulu');
    }
    
    // Cek ketersediaan buku
    $sql = "SELECT b.*, 
            (b.total_copies - COALESCE((SELECT COUNT(*) FROM transactions t 
                WHERE t.book_id = b.id AND t.status = 'borrowed'), 0)) as current_available
            FROM books b 
            WHERE b.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Buku tidak ditemukan');
    }
    
    $book = $result->fetch_assoc();
    
    if ($book['current_available'] <= 0) {
        throw new Exception('Buku sedang tidak tersedia');
    }
    
    // Generate transaction code
    $transactionCode = 'TRX' . date('Ymd') . sprintf('%04d', rand(1000, 9999));
    
    // Cek duplikasi code
    $checkCodeSql = "SELECT transaction_id FROM transactions WHERE transaction_code = ?";
    $codeStmt = $conn->prepare($checkCodeSql);
    $codeStmt->bind_param("s", $transactionCode);
    $codeStmt->execute();
    $codeResult = $codeStmt->get_result();
    
    // Jika duplicate, generate lagi
    while ($codeResult->num_rows > 0) {
        $transactionCode = 'TRX' . date('Ymd') . sprintf('%04d', rand(1000, 9999));
        $codeStmt->bind_param("s", $transactionCode);
        $codeStmt->execute();
        $codeResult = $codeStmt->get_result();
    }
    
    // Tanggal peminjaman dan jatuh tempo (7 hari)
    $borrowDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime('+7 days'));
    
    // Insert transaksi
    $insertSql = "INSERT INTO transactions 
                  (transaction_code, member_id, book_id, borrow_date, due_date, status, created_by) 
                  VALUES (?, ?, ?, ?, ?, 'borrowed', ?)";
    
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("siissi", $transactionCode, $memberId, $bookId, $borrowDate, $dueDate, $userId);
    
    if (!$insertStmt->execute()) {
        throw new Exception('Gagal menyimpan transaksi');
    }
    
    // Commit transaction
    $conn->commit();
    
    // Response sukses
    echo json_encode([
        'success' => true,
        'message' => 'Peminjaman berhasil!',
        'data' => [
            'transaction_code' => $transactionCode,
            'book_title' => $book['title'],
            'borrow_date' => date('d/m/Y', strtotime($borrowDate)),
            'due_date' => date('d/m/Y', strtotime($dueDate)),
            'days' => 7
        ]
    ]);
    
    // Close statements
    $userStmt->close();
    $checkStmt->close();
    $borrowStmt->close();
    $stmt->close();
    $codeStmt->close();
    $insertStmt->close();
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>