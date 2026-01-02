<?php
require_once __DIR__ . '/../includes/config.php';

// Set JSON header
header('Content-Type: application/json');

// Cek login dan role admin
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus buku']);
    exit;
}

// Ambil input
$input = json_decode(file_get_contents('php://input'), true);
$bookId = isset($input['book_id']) ? intval($input['book_id']) : 0;

if ($bookId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // 1. Cek apakah buku sedang dipinjam
    $checkSql = "SELECT COUNT(*) as total FROM transactions 
                 WHERE book_id = ? AND status = 'borrowed'";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $bookId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $borrowed = $checkResult->fetch_assoc()['total'];
    
    if ($borrowed > 0) {
        throw new Exception('Tidak dapat menghapus buku yang sedang dipinjam. Tunggu hingga semua peminjaman selesai.');
    }
    
    // 2. Ambil info buku untuk mendapatkan cover image
    $bookSql = "SELECT cover_image FROM books WHERE id = ?";
    $bookStmt = $conn->prepare($bookSql);
    $bookStmt->bind_param("i", $bookId);
    $bookStmt->execute();
    $bookResult = $bookStmt->get_result();
    
    if ($bookResult->num_rows === 0) {
        throw new Exception('Buku tidak ditemukan');
    }
    
    $book = $bookResult->fetch_assoc();
    $coverImage = $book['cover_image'];
    
    // 3. Hapus riwayat transaksi yang sudah selesai (opsional, untuk history)
    // Kita tidak hapus transaksi untuk menjaga history
    // Hanya hapus jika benar-benar tidak ada transaksi sama sekali
    $allTransactionsSql = "SELECT COUNT(*) as total FROM transactions WHERE book_id = ?";
    $allTransStmt = $conn->prepare($allTransactionsSql);
    $allTransStmt->bind_param("i", $bookId);
    $allTransStmt->execute();
    $allTransResult = $allTransStmt->get_result();
    $totalTrans = $allTransResult->fetch_assoc()['total'];
    
    if ($totalTrans > 0) {
        // Jika ada riwayat transaksi, kita tidak hapus buku tapi kasih peringatan
        throw new Exception("Buku memiliki riwayat transaksi ($totalTrans transaksi). Untuk menjaga integritas data, buku tidak dapat dihapus.");
    }
    
    // 4. Hapus buku dari database
    $deleteSql = "DELETE FROM books WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $bookId);
    
    if (!$deleteStmt->execute()) {
        throw new Exception('Gagal menghapus buku dari database');
    }
    
    // 5. Hapus file cover jika ada
    if ($coverImage) {
        $coverPath = __DIR__ . '/../uploads/covers/' . $coverImage;
        if (file_exists($coverPath)) {
            @unlink($coverPath); // @ untuk suppress error jika gagal hapus file
        }
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil dihapus'
    ]);
    
    // Close statements
    $checkStmt->close();
    $bookStmt->close();
    $allTransStmt->close();
    $deleteStmt->close();
    
} catch (Exception $e) {
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>