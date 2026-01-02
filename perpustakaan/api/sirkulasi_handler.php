<?php
require_once __DIR__ . '/../includes/config.php';

// Set header JSON
header('Content-Type: application/json');

// Cek login
if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu!'
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

// ========================================
// 1. GET MEMBER DATA
// ========================================
if ($action === 'get_member') {
    $member_code = sanitize($_POST['member_code']);
    
    $query = "SELECT * FROM members WHERE member_code = ? AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $member_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
        
        // Cek jumlah buku yang sedang dipinjam
        $check_borrowed = "SELECT COUNT(*) as total FROM transactions 
                          WHERE member_id = ? AND status = 'borrowed'";
        $stmt2 = $conn->prepare($check_borrowed);
        $stmt2->bind_param("i", $member['member_id']);
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
        
        echo json_encode([
            'success' => true,
            'message' => 'Anggota ditemukan',
            'data' => $member
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Anggota tidak ditemukan atau tidak aktif'
        ]);
    }
}

// ========================================
// 2. GET BOOK DATA
// ========================================
elseif ($action === 'get_book') {
    $book_code = sanitize($_POST['book_code']);
    
    // Cari berdasarkan call_number atau ISBN
    $query = "SELECT b.*, 
              (SELECT COUNT(*) FROM book_copies bc 
               WHERE bc.book_id = b.id AND bc.status = 'available') as available_copies_count
              FROM books b
              WHERE (b.call_number = ? OR b.isbn = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $book_code, $book_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        
        // Check if book uses copy tracking
        $has_copies = false;
        $copy_query = "SELECT COUNT(*) as total FROM book_copies WHERE book_id = ?";
        $copy_stmt = $conn->prepare($copy_query);
        $copy_stmt->bind_param("i", $book['id']);
        $copy_stmt->execute();
        $copy_result = $copy_stmt->get_result();
        $copy_data = $copy_result->fetch_assoc();
        
        if ($copy_data['total'] > 0) {
            $has_copies = true;
            // Get available copy info
            $avail_copy_query = "SELECT copy_id, copy_number, barcode 
                                FROM book_copies 
                                WHERE book_id = ? AND status = 'available' 
                                LIMIT 1";
            $avail_stmt = $conn->prepare($avail_copy_query);
            $avail_stmt->bind_param("i", $book['id']);
            $avail_stmt->execute();
            $avail_result = $avail_stmt->get_result();
            
            if ($avail_result->num_rows > 0) {
                $book['available_copy'] = $avail_result->fetch_assoc();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tidak ada eksemplar yang tersedia untuk buku ini'
                ]);
                exit;
            }
        } else {
            // Fallback to old system
            if ($book['available_copies'] <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Buku tidak ditemukan atau stok habis'
                ]);
                exit;
            }
        }
        
        $book['has_copies'] = $has_copies;
        $book['available_copies'] = $has_copies ? $book['available_copies_count'] : $book['available_copies'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Buku ditemukan',
            'data' => $book
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Buku tidak ditemukan atau stok habis'
        ]);
    }
}

// ========================================
// 3. CREATE BORROW (Peminjaman)
// ========================================
elseif ($action === 'create_borrow') {
    $member_code = sanitize($_POST['member_code']);
    $book_code = sanitize($_POST['book_code']);
    $borrow_date = sanitize($_POST['borrow_date']);
    $due_date = sanitize($_POST['due_date']);
    
    // Get member_id
    $member_query = "SELECT member_id FROM members WHERE member_code = ? AND status = 'active'";
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
            'message' => 'Anggota sudah meminjam 3 buku (maksimal)'
        ]);
        exit;
    }
    
    // Get book_id
    $book_query = "SELECT id, available_copies FROM books 
                   WHERE (call_number = ? OR isbn = ?) 
                   AND available_copies > 0";
    $stmt3 = $conn->prepare($book_query);
    $stmt3->bind_param("ss", $book_code, $book_code);
    $stmt3->execute();
    $book_result = $stmt3->get_result();
    
    if ($book_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Buku tidak ditemukan atau stok habis'
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
            $stmt4->bind_param("siiissi", $transaction_code, $member_id, $book_id, $copy_id, $borrow_date, $due_date, $_SESSION['user_id']);
        } else {
            $insert_query = "INSERT INTO transactions 
                            (transaction_code, member_id, book_id, borrow_date, due_date, status, created_by) 
                            VALUES (?, ?, ?, ?, ?, 'borrowed', ?)";
            $stmt4 = $conn->prepare($insert_query);
            $stmt4->bind_param("siissi", $transaction_code, $member_id, $book_id, $borrow_date, $due_date, $_SESSION['user_id']);
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
}

// ========================================
// 4. GET TRANSACTION DATA
// ========================================
elseif ($action === 'get_transaction') {
    $transaction_code = sanitize($_POST['transaction_code']);
    
    $query = "SELECT t.*, m.full_name, b.title, b.author,
              bc.copy_number, bc.barcode
              FROM transactions t
              JOIN members m ON t.member_id = m.member_id
              JOIN books b ON t.book_id = b.id
              LEFT JOIN book_copies bc ON t.copy_id = bc.copy_id
              WHERE t.transaction_code = ?
              AND t.status = 'borrowed'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $transaction_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();
        
        // Hitung keterlambatan
        $due_date = new DateTime($transaction['due_date']);
        $today = new DateTime();
        $diff = $today->diff($due_date);
        
        $days_overdue = 0;
        $fine_amount = 0;
        
        if ($today > $due_date) {
            $days_overdue = $diff->days;
            $fine_amount = $days_overdue * 1000; // Rp 1.000/hari
        }
        
        $transaction['days_overdue'] = $days_overdue;
        $transaction['fine_amount'] = $fine_amount;
        
        echo json_encode([
            'success' => true,
            'message' => 'Transaksi ditemukan',
            'data' => $transaction
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Transaksi tidak ditemukan atau sudah dikembalikan'
        ]);
    }
}

// ========================================
// 5. PROCESS RETURN (Pengembalian)
// ========================================
elseif ($action === 'process_return') {
    $transaction_code = sanitize($_POST['transaction_code']);
    $return_date = sanitize($_POST['return_date']);
    $book_condition = sanitize($_POST['book_condition']);
    
    // Get transaction data
    $query = "SELECT * FROM transactions 
              WHERE transaction_code = ? 
              AND status = 'borrowed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $transaction_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Transaksi tidak ditemukan atau sudah dikembalikan'
        ]);
        exit;
    }
    
    $transaction = $result->fetch_assoc();
    
    // Hitung denda keterlambatan
    $due_date = new DateTime($transaction['due_date']);
    $return_date_obj = new DateTime($return_date);
    $diff = $return_date_obj->diff($due_date);
    
    $days_overdue = 0;
    $late_fine = 0;
    $status = 'returned';
    
    if ($return_date_obj > $due_date) {
        $days_overdue = $diff->days;
        $late_fine = $days_overdue * 1000; // Rp 1.000/hari
    }
    
    // Hitung denda kerusakan
    $damage_fine = 0;
    switch ($book_condition) {
        case 'light_damage':
            $damage_fine = 10000;
            break;
        case 'heavy_damage':
            $damage_fine = 50000;
            break;
        case 'lost':
            $damage_fine = 150000;
            break;
    }
    
    $total_fine = $late_fine + $damage_fine;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update transaction
        $update_query = "UPDATE transactions SET 
                        return_date = ?,
                        status = ?,
                        fine_amount = ?,
                        book_condition = ?
                        WHERE transaction_id = ?";
        
        $stmt2 = $conn->prepare($update_query);
        $stmt2->bind_param("ssdsi", $return_date, $status, $total_fine, $book_condition, $transaction['transaction_id']);
        
        if (!$stmt2->execute()) {
            throw new Exception('Gagal update transaksi');
        }
        
        // Update copy status or available_copies
        if (!empty($transaction['copy_id'])) {
            // Use copy tracking system
            $new_copy_status = 'available';
            $new_copy_condition = 'good';
            
            // Determine copy status based on book condition
            switch ($book_condition) {
                case 'light_damage':
                    $new_copy_status = 'damaged';
                    $new_copy_condition = 'fair';
                    break;
                case 'heavy_damage':
                    $new_copy_status = 'damaged';
                    $new_copy_condition = 'poor';
                    break;
                case 'lost':
                    $new_copy_status = 'lost';
                    break;
                default:
                    $new_copy_status = 'available';
                    $new_copy_condition = 'good';
            }
            
            // Update copy status and condition
            $update_copy = "UPDATE book_copies SET 
                           status = ?,
                           `condition` = ?
                           WHERE copy_id = ?";
            $stmt3 = $conn->prepare($update_copy);
            $stmt3->bind_param("ssi", $new_copy_status, $new_copy_condition, $transaction['copy_id']);
            
            if (!$stmt3->execute()) {
                throw new Exception('Gagal update status eksemplar');
            }
        } else {
            // Fallback to old system - update available_copies
            if ($book_condition !== 'lost') {
                $update_book = "UPDATE books SET available_copies = available_copies + 1 
                               WHERE id = ?";
                $stmt3 = $conn->prepare($update_book);
                $stmt3->bind_param("i", $transaction['book_id']);
                
                if (!$stmt3->execute()) {
                    throw new Exception('Gagal update stok buku');
                }
            } else {
                // Jika hilang, kurangi total_copies juga
                $update_book = "UPDATE books SET 
                               total_copies = total_copies - 1,
                               available_copies = available_copies - 1
                               WHERE id = ?";
                $stmt3 = $conn->prepare($update_book);
                $stmt3->bind_param("i", $transaction['book_id']);
                
                if (!$stmt3->execute()) {
                    throw new Exception('Gagal update stok buku');
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pengembalian berhasil diproses',
            'days_overdue' => $days_overdue,
            'late_fine' => $late_fine,
            'damage_fine' => $damage_fine,
            'fine_amount' => $total_fine
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// ========================================
// Invalid Action
// ========================================
else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}
?>