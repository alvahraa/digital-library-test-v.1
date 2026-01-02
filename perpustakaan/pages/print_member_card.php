<?php
require_once __DIR__ . '/../includes/config.php';

if (!is_logged_in()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    redirect('../index.php');
}

$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($member_id === 0) {
    $_SESSION['error'] = "ID anggota tidak valid!";
    redirect('keanggotaan.php');
}

// Get member data
$query = "SELECT m.*, mr.display_name as role_display 
          FROM members m
          LEFT JOIN member_roles mr ON m.member_role = mr.role_code
          WHERE m.member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Data anggota tidak ditemukan!";
    redirect('keanggotaan.php');
}

$member = $result->fetch_assoc();
$photo_url = $member['photo'] ? '../uploads/members/' . $member['photo'] : '../assets/img/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Anggota - <?php echo htmlspecialchars($member['full_name']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .print-actions {
            text-align: center;
            margin-bottom: 30px;
        }

        .print-notice {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
            font-size: 13px;
            line-height: 1.6;
        }

        .print-notice strong {
            color: #856404;
        }

        .btn {
            padding: 12px 30px;
            margin: 0 5px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #F875AA;
            color: white;
        }

        .btn-primary:hover {
            background: #e66599;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* KARTU ANGGOTA DESIGN */
        .member-card {
            width: 85.6mm;
            height: 54mm;
            background: linear-gradient(135deg, #F875AA 0%, #FFDFDF 100%);
            border-radius: 12px;
            padding: 15px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(248, 117, 170, 0.3);
            margin: 0 auto;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .member-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .library-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .library-logo img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: white;
            padding: 3px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .library-name {
            color: white;
            font-size: 11px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .library-title {
            font-size: 9px;
            opacity: 0.9;
        }

        .card-type {
            background: rgba(255, 255, 255, 0.95);
            color: #F875AA;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .card-body {
            display: flex;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .member-photo-card {
            width: 70px;
            height: 90px;
            border-radius: 8px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            background: white;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .member-info {
            flex: 1;
            color: white;
        }

        .member-name {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 4px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-row {
            display: flex;
            margin-bottom: 3px;
            font-size: 9px;
        }

        .info-label {
            width: 55px;
            opacity: 0.9;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
        }

        .card-footer {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 8px;
            color: white;
            position: relative;
            z-index: 1;
        }

        #barcode<?php echo $member['member_id']; ?> {
            background: white;
            padding: 5px 10px;
            border-radius: 4px;
        }



        /* BACK SIDE */
        .card-back {
            width: 85.6mm;
            height: 54mm;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border-radius: 12px;
            padding: 15px;
            margin: 20px auto 0;
            color: white;
            position: relative;
            overflow: hidden;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .card-back::before {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .back-title {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .rules {
            font-size: 8px;
            line-height: 1.6;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            list-style-position: inside;
        }

        .rules li {
            margin-bottom: 4px;
        }

        .back-footer {
            position: absolute;
            bottom: 10px;
            left: 15px;
            right: 15px;
            text-align: center;
            font-size: 7px;
            opacity: 0.7;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 6px;
            z-index: 1;
        }

        /* PRINT STYLES */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                background: white !important;
                padding: 0;
                margin: 0;
            }

            .card-container {
                box-shadow: none;
                padding: 10mm;
                background: white !important;
            }

            .print-actions,
            .print-notice {
                display: none !important;
            }

            .member-card,
            .card-back {
                box-shadow: none !important;
                page-break-after: always;
                page-break-inside: avoid;
                margin: 0 auto 10mm;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .member-card {
                background: linear-gradient(135deg, #F875AA 0%, #FFDFDF 100%) !important;
            }

            .card-back {
                background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
            }

            /* Force all colors to print */
            .library-logo img,
            .card-type,
            .member-photo-card {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Remove shadows for cleaner print */
            .member-card,
            .card-back,
            .member-photo-card {
                box-shadow: none !important;
            }
        }

        /* Ensure colors print on all browsers */
        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="print-actions">
            <div class="print-notice">
                <strong>⚠️ PENTING - Agar Warna Background Tercetak:</strong><br>
                📌 <strong>Chrome/Edge:</strong> Di Print settings → Centang <strong>"Background graphics"</strong><br>
                📌 <strong>Firefox:</strong> Di Page Setup → Centang <strong>"Print Background (colors & images)"</strong><br>
                💡 Tanpa ini, kartu akan tercetak putih tanpa warna!
            </div>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Kartu</button>
            <button onclick="window.close()" class="btn btn-secondary">✖ Tutup</button>
        </div>

        <!-- FRONT SIDE -->
        <div class="member-card">
            <div class="card-header">
                <div class="library-logo">
                    <img src="../assets/img/logo.jpg" alt="Logo">
                    <div>
                        <div class="library-name">I LOVE SWISS</div>
                        <div class="library-title">LIBRARY</div>
                    </div>
                </div>
                <div class="card-type"><?php echo htmlspecialchars($member['role_display'] ?: 'Member'); ?></div>
            </div>

            <div class="card-body">
                <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Photo" class="member-photo-card">
                
                <div class="member-info">
                    <div class="member-name"><?php echo htmlspecialchars($member['full_name']); ?></div>
                    
                    <div class="info-row">
                        <span class="info-label">ID Anggota</span>
                        <span class="info-value">: <?php echo htmlspecialchars($member['member_code']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Jenis Kelamin</span>
                        <span class="info-value">: <?php echo $member['gender'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Tipe</span>
                        <span class="info-value">: <?php 
                            $types = ['student' => 'Pelajar/Mahasiswa', 'teacher' => 'Guru/Dosen', 'public' => 'Umum'];
                            echo $types[$member['member_type']] ?? $member['member_type']; 
                        ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Institusi</span>
                        <span class="info-value">: <?php echo htmlspecialchars($member['institution'] ?: '-'); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Telepon</span>
                        <span class="info-value">: <?php echo htmlspecialchars($member['phone']); ?></span>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <svg id="barcode<?php echo $member['member_id']; ?>"></svg>
            </div>
        </div>

        <!-- BACK SIDE -->
        <div class="card-back">
            <div class="back-title">PERATURAN PERPUSTAKAAN</div>
            <ul class="rules">
                <li>✓ Kartu ini adalah identitas resmi anggota perpustakaan</li>
                <li>✓ Kartu tidak dapat dipindahtangankan</li>
                <li>✓ Maksimal peminjaman 3 buku dengan durasi 7 hari</li>
                <li>✓ Denda keterlambatan Rp 1.000/hari per buku</li>
                <li>✓ Jaga kebersihan dan keutuhan buku yang dipinjam</li>
                <li>✓ Harap lapor jika kartu hilang atau rusak</li>
            </ul>
            <div class="back-footer">
                © 2025 I Love Swiss Library • Semarang, Indonesia<br>
                www.iloveswisslibrary.com • +62 812-3456-7890
            </div>
        </div>
    </div>

    <!-- Include JsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    
    <script>
        // Generate barcode
        JsBarcode("#barcode<?php echo $member['member_id']; ?>", "<?php echo $member['member_code']; ?>", {
            format: "CODE128",
            width: 1.5,
            height: 35,
            displayValue: true,
            fontSize: 12,
            background: "transparent",
            lineColor: "#000000",
            margin: 0
        });

        // Auto print dialog saat halaman load (opsional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>