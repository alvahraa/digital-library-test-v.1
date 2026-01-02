<?php
require_once __DIR__ . '/../includes/config.php';

// Cek login dan role admin
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($bookId <= 0) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid book ID');
}

try {
    // Ambil data buku
    $sql = "SELECT id, title, author, isbn, call_number FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Buku tidak ditemukan');
    }
    
    $book = $result->fetch_assoc();
    
    // Generate barcode menggunakan barcode online API
    // Kita gunakan ISBN atau ID buku sebagai data barcode
    $barcodeData = $book['isbn'] ?: 'BOOK-' . str_pad($bookId, 8, '0', STR_PAD_LEFT);
    
    // Buat HTML untuk print barcode
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Barcode - <?php echo htmlspecialchars($book['title']); ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: #f5f5f5;
            }
            .barcode-container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 400px;
            }
            .barcode-title {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 10px;
                color: #333;
            }
            .barcode-author {
                font-size: 14px;
                color: #666;
                margin-bottom: 20px;
            }
            .barcode-image {
                margin: 20px 0;
                padding: 15px;
                background: white;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
            }
            .barcode-code {
                font-family: 'Courier New', monospace;
                font-size: 16px;
                font-weight: bold;
                color: #333;
                margin-top: 10px;
            }
            .barcode-info {
                font-size: 12px;
                color: #999;
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #e0e0e0;
            }
            .btn-print {
                margin-top: 20px;
                padding: 12px 30px;
                background: #F875AA;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            }
            .btn-print:hover {
                background: #e66599;
                transform: translateY(-1px);
            }
            @media print {
                body {
                    background: white;
                }
                .btn-print {
                    display: none;
                }
                .barcode-container {
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="barcode-container">
            <div class="barcode-title"><?php echo htmlspecialchars($book['title']); ?></div>
            <div class="barcode-author">oleh <?php echo htmlspecialchars($book['author']); ?></div>
            
            <div class="barcode-image">
                <!-- Barcode Image menggunakan API eksternal -->
                <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo urlencode($barcodeData); ?>&code=Code128&translate-esc=on" 
                     alt="Barcode" 
                     style="max-width: 100%; height: auto;">
            </div>
            
            <div class="barcode-code"><?php echo htmlspecialchars($barcodeData); ?></div>
            
            <div class="barcode-info">
                <?php if ($book['call_number']): ?>
                    Nomor Panggil: <?php echo htmlspecialchars($book['call_number']); ?><br>
                <?php endif; ?>
                ID Buku: <?php echo $bookId; ?>
            </div>
            
            <button class="btn-print" onclick="window.print()">
                🖨️ Cetak Barcode
            </button>
        </div>
    </body>
    </html>
    <?php
    
    $stmt->close();
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error: ' . $e->getMessage());
}

$conn->close();
?>