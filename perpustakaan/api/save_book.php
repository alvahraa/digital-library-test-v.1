<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek login dan role admin
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publishYear = !empty($_POST['publish_year']) ? intval($_POST['publish_year']) : null;
    $publishPlace = trim($_POST['publish_place'] ?? '');
    $pages = !empty($_POST['pages']) ? intval($_POST['pages']) : null;
    $language = trim($_POST['language'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $callNumber = trim($_POST['call_number'] ?? '');
    $totalCopies = intval($_POST['total_copies'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    
    // Validasi
    if (empty($title) || empty($author) || empty($isbn)) {
        throw new Exception('Judul, Pengarang, dan ISBN wajib diisi');
    }
    
    if ($totalCopies < 1) {
        throw new Exception('Jumlah eksemplar minimal 1');
    }
    
    // Handle upload cover
    $coverImage = null;
    $uploadDir = __DIR__ . '/../uploads/covers/';
    
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        
        // Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Gagal membuat folder upload');
            }
        }
        
        // Cek apakah folder writable
        if (!is_writable($uploadDir)) {
            throw new Exception('Folder upload tidak dapat ditulis. Setting permission dulu ya!');
        }
        
        // Validasi file
        $fileSize = $_FILES['cover_image']['size'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if ($fileSize > $maxSize) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 2MB');
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Format file tidak didukung. Gunakan: jpg, jpeg, png, gif');
        }
        
        // Generate unique filename
        $fileName = 'cover_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetPath)) {
            $coverImage = $fileName;
        } else {
            throw new Exception('Gagal upload cover image');
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    if ($bookId > 0) {
        // ========== UPDATE BOOK ==========
        
        // Hitung available_copies yang benar
        $availableSql = "SELECT 
                            (? - COALESCE((SELECT COUNT(*) FROM transactions t 
                                WHERE t.book_id = ? AND t.status = 'borrowed'), 0)) as new_available";
        $availableStmt = $conn->prepare($availableSql);
        $availableStmt->bind_param("ii", $totalCopies, $bookId);
        $availableStmt->execute();
        $newAvailable = $availableStmt->get_result()->fetch_assoc()['new_available'];
        
        if ($newAvailable < 0) {
            throw new Exception('Total eksemplar tidak boleh lebih kecil dari jumlah yang sedang dipinjam');
        }
        
        $sql = "UPDATE books SET 
                title = ?, author = ?, isbn = ?, publisher = ?, 
                publish_year = ?, publish_place = ?, pages = ?, language = ?, 
                category = ?, call_number = ?, total_copies = ?, description = ?";
        
        $params = [$title, $author, $isbn, $publisher, $publishYear, $publishPlace, 
                   $pages, $language, $category, $callNumber, $totalCopies, $description];
        $types = "ssssisssssis";
        
        // Jika ada cover baru, update dan hapus yang lama
        if ($coverImage) {
            // Ambil cover lama
            $oldCoverSql = "SELECT cover_image FROM books WHERE id = ?";
            $oldStmt = $conn->prepare($oldCoverSql);
            $oldStmt->bind_param("i", $bookId);
            $oldStmt->execute();
            $oldResult = $oldStmt->get_result();
            
            if ($oldResult->num_rows > 0) {
                $oldCover = $oldResult->fetch_assoc()['cover_image'];
                if ($oldCover) {
                    $oldPath = $uploadDir . $oldCover;
                    if (file_exists($oldPath)) {
                        unlink($oldPath); // Hapus file lama
                    }
                }
            }
            
            $sql .= ", cover_image = ?";
            $params[] = $coverImage;
            $types .= "s";
            
            $oldStmt->close();
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $bookId;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Buku berhasil diupdate',
                'book_id' => $bookId
            ]);
        } else {
            throw new Exception('Gagal update buku');
        }
        
        $availableStmt->close();
        
    } else {
        // ========== INSERT NEW BOOK ==========
        
        $sql = "INSERT INTO books 
                (title, author, isbn, publisher, publish_year, publish_place, 
                 pages, language, category, call_number, total_copies, available_copies, 
                 cover_image, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssissssiisss", 
            $title, $author, $isbn, $publisher, $publishYear, $publishPlace,
            $pages, $language, $category, $callNumber, $totalCopies, $totalCopies,
            $coverImage, $description
        );
        
        if ($stmt->execute()) {
            $newBookId = $conn->insert_id;
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Buku berhasil ditambahkan',
                'book_id' => $newBookId
            ]);
        } else {
            throw new Exception('Gagal menyimpan buku');
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $conn->rollback();
    
    // Hapus file upload jika ada error
    if (isset($coverImage) && $coverImage && isset($uploadDir)) {
        $errorPath = $uploadDir . $coverImage;
        if (file_exists($errorPath)) {
            @unlink($errorPath);
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>