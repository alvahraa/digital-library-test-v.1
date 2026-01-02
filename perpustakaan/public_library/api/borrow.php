<?php
/**
 * Public Self-Service Borrowing API
 * No authentication required - public library kiosk
 */
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_POST['action'] ?? '';

// ========================================
// CREATE BORROW (Self-Service)
// ========================================
if ($action === 'create_borrow') {
    $member_code = sanitize($_POST['member_code'] ?? '');
    $book_code = sanitize($_POST['book_code'] ?? '');
    $borrow_date = sanitize($_POST['borrow_date'] ?? date('Y-m-d'));
    $due_date = sanitize($_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days')));
    
    // Validation
    if (empty($member_code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Member ID tidak boleh kosong'
        ]);
        exit;
    }
    
    if (empty($book_code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Book code tidak boleh kosong'
        ]);
        exit;
    }
    
    // Get member_id
    $member_query = "SELECT member_id, status FROM members WHERE member_code = ? AND status = 'active'";
    $stmt = $conn->prepare($member_query);
    $stmt->bind_param("s", $member_code);
    $stmt->execute();
    $member_result = $stmt->get_result();
    
    if ($member_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Anggota tidak ditemukan atau tidak aktif'
        ]);
        exit;
    }
    
    $member = $member_result->fetch_assoc();
    $member_id = $member['member_id'];
    
    // Cek jumlah buku yang sedang dipinjam
    $check_borrowed = "SELECT COUNT(*) as total FROM transactions 
                      WHERE member_id = ? AND status = 'borrowed'";
    $stmt2 = $conn->prepare($check_borrowed);
    $stmt2->bind_param("i", $member_id);
    $stmt2->execute();
    $borrowed_result = $stmt2->get_result();
    $borrowed_data = $borrowed_result->fetch_assoc();
    
    if ($borrowed_data['total'] >= 3) {
        echo json_encode([
            'success' => false,
            'message' => 'Anggota sudah meminjam 3 buku (maksimal). Tidak dapat meminjam lagi.'
        ]);
        exit;
    }
    
    // Get book_id
    $book_query = "SELECT id, available_copies FROM books 
                   WHERE (call_number = ? OR isbn = ?)";
    $stmt3 = $conn->prepare($book_query);
    $stmt3->bind_param("ss", $book_code, $book_code);
    $stmt3->execute();
    $book_result = $stmt3->get_result();
    
    if ($book_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Buku tidak ditemukan'
        ]);
        exit;
    }
    
    $book = $book_result->fetch_assoc();
    $book_id = $book['id'];
    
    // Check if book uses copy tracking
    $copy_query = "SELECT copy_id, copy_number, barcode 
                   FROM book_copies 
                   WHERE book_id = ? AND status = 'available' 
                   ORDER BY copy_id ASC 
                   LIMIT 1";
    $copy_stmt = $conn->prepare($copy_query);
    $copy_stmt->bind_param("i", $book_id);
    $copy_stmt->execute();
    $copy_result = $copy_stmt->get_result();
    
    $copy_id = null;
    $use_copy_tracking = false;
    
    if ($copy_result->num_rows > 0) {
        // Use copy tracking system
        $copy_data = $copy_result->fetch_assoc();
        $copy_id = $copy_data['copy_id'];
        $use_copy_tracking = true;
    } else {
        // Fallback to old system - check available_copies
        if ($book['available_copies'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Tidak ada eksemplar yang tersedia untuk buku ini'
            ]);
            exit;
        }
    }
    
    // Generate transaction code
    $transaction_code = 'TRX' . date('Ymd') . rand(1000, 9999);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert transaction with copy_id if available
        if ($use_copy_tracking && $copy_id) {
            $insert_query = "INSERT INTO transactions 
                            (transaction_code, member_id, book_id, copy_id, borrow_date, due_date, status, created_by) 
                            VALUES (?, ?, ?, ?, ?, ?, 'borrowed', ?)";
            $stmt4 = $conn->prepare($insert_query);
            $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $member_id;
            $stmt4->bind_param("siiissi", $transaction_code, $member_id, $book_id, $copy_id, $borrow_date, $due_date, $created_by);
        } else {
            $insert_query = "INSERT INTO transactions 
                            (transaction_code, member_id, book_id, borrow_date, due_date, status, created_by) 
                            VALUES (?, ?, ?, ?, ?, 'borrowed', ?)";
            $stmt4 = $conn->prepare($insert_query);
            $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $member_id;
            $stmt4->bind_param("siissi", $transaction_code, $member_id, $book_id, $borrow_date, $due_date, $created_by);
        }
        
        if (!$stmt4->execute()) {
            throw new Exception('Gagal menyimpan transaksi');
        }
        
        // Update copy status or available_copies
        if ($use_copy_tracking && $copy_id) {
            // Update copy status to borrowed
            $update_copy = "UPDATE book_copies SET status = 'borrowed' WHERE copy_id = ?";
            $stmt5 = $conn->prepare($update_copy);
            $stmt5->bind_param("i", $copy_id);
            
            if (!$stmt5->execute()) {
                throw new Exception('Gagal update status eksemplar');
            }
        } else {
            // Update available_copies (old system)
            $update_book = "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?";
            $stmt5 = $conn->prepare($update_book);
            $stmt5->bind_param("i", $book_id);
            
            if (!$stmt5->execute()) {
                throw new Exception('Gagal update stok buku');
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Peminjaman berhasil diproses',
            'transaction_code' => $transaction_code
        ];
        
        if ($use_copy_tracking && $copy_id) {
            $response['copy_number'] = $copy_data['copy_number'];
            $response['barcode'] = $copy_data['barcode'];
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}
?>

