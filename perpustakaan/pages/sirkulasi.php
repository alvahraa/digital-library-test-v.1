<?php
require_once __DIR__ . '/../includes/config.php';

if (!is_logged_in()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    redirect('../index.php');
}

$fullname = $_SESSION['fullname'];
$username = $_SESSION['username'];
$role = ucfirst($_SESSION['role']);

// Get transactions for today
$today = date('Y-m-d');
$query = "SELECT t.*, m.full_name, m.member_code, b.title,
          bc.copy_number, bc.barcode
          FROM transactions t
          JOIN members m ON t.member_id = m.member_id
          JOIN books b ON t.book_id = b.id
          LEFT JOIN book_copies bc ON t.copy_id = bc.copy_id
          WHERE DATE(t.created_at) = '$today'
          ORDER BY t.created_at DESC";
$transactions = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sirkulasi - I Love Swiss Library</title>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- CSS Sirkulasi -->
    <link rel="stylesheet" href="../assets/css/sirkulasi.css">
</head>
<body>
    <div id="sakura-container-bg"></div>

    <nav>
        <div class="logo">
            <div class="logo-image">
                <img src="../assets/img/logo.jpg" alt="Logo">
            </div>
            <span class="logo-text">I love swiss</span>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($fullname); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($role); ?></div>
            </div>
            <a href="dashboard.php" class="btn-logout" style="background: transparent; border: 1px solid #F875AA; color: #F875AA; margin-right: 10px;">Dashboard</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Sirkulasi Perpustakaan</h1>
            <p class="page-subtitle">Kelola peminjaman dan pengembalian buku</p>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('peminjaman')">Peminjaman Buku</button>
            <button class="tab-btn" onclick="switchTab('pengembalian')">Pengembalian Buku</button>
            <button class="tab-btn" onclick="switchTab('riwayat')">Riwayat Transaksi</button>
        </div>

        <!-- Tab Peminjaman -->
        <div id="peminjaman" class="tab-content active">
            <div class="form-card">
                <h3 class="form-title">Form Peminjaman Buku</h3>
                
                <form id="formPeminjaman">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ID Anggota</label>
                            <input type="text" class="form-input" id="idAnggota" name="member_code" placeholder="Masukkan ID Anggota (contoh: MBR001)" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Anggota</label>
                            <input type="text" class="form-input" id="namaAnggota" placeholder="Otomatis terisi" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Kode Buku / ISBN</label>
                            <input type="text" class="form-input" id="kodeBuku" name="book_code" placeholder="Masukkan Kode Buku atau ISBN" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Judul Buku</label>
                            <input type="text" class="form-input" id="judulBuku" placeholder="Otomatis terisi" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tanggal Pinjam</label>
                            <input type="date" class="form-input" id="tglPinjam" name="borrow_date" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Kembali (Maks 7 hari)</label>
                            <input type="date" class="form-input" id="tglKembali" name="due_date" readonly>
                        </div>
                    </div>

                    <div class="info-box">
                        <h4>Informasi Peminjaman</h4>
                        <p>• Maksimal peminjaman: 7 hari</p>
                        <p>• Denda keterlambatan: Rp 1.000/hari</p>
                        <p>• Maksimal buku yang dipinjam: 3 buku</p>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary">Proses Peminjaman</button>
                        <button type="reset" class="btn-secondary">Reset Form</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab Pengembalian -->
        <div id="pengembalian" class="tab-content">
            <div class="form-card">
                <h3 class="form-title">Form Pengembalian Buku</h3>
                
                <form id="formPengembalian">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ID Transaksi</label>
                            <input type="text" class="form-input" id="idTransaksi" name="transaction_code" placeholder="Masukkan ID Transaksi" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Pengembalian</label>
                            <input type="date" class="form-input" id="tglPengembalian" name="return_date" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kondisi Buku</label>
                        <select class="form-select" id="kondisiBuku" name="book_condition" required>
                            <option value="">Pilih Kondisi</option>
                            <option value="good">Baik</option>
                            <option value="light_damage">Rusak Ringan</option>
                            <option value="heavy_damage">Rusak Berat</option>
                            <option value="lost">Hilang</option>
                        </select>
                    </div>

                    <div class="info-box" id="infoKeterlambatan" style="display: none;">
                        <h4>Perhitungan Denda</h4>
                        <p id="dendaInfo"></p>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary">Proses Pengembalian</button>
                        <button type="reset" class="btn-secondary">Reset Form</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab Riwayat -->
        <div id="riwayat" class="tab-content">
            <div class="transaction-list">
                <h3 class="list-title">Riwayat Transaksi Hari Ini</h3>
                
                <table id="tableRiwayat">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($transactions) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($transactions)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['transaction_code']) ?></td>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($row['title']) ?>
                                        <?php if (!empty($row['copy_number'])): ?>
                                            <br><small style="color: #999; font-size: 12px;">
                                                Eksemplar: <?= htmlspecialchars($row['copy_number']) ?>
                                                <?php if (!empty($row['barcode'])): ?>
                                                    (<?= htmlspecialchars($row['barcode']) ?>)
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($row['borrow_date'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['due_date'])) ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = 'badge-warning';
                                        $status_text = 'Dipinjam';
                                        if ($row['status'] == 'returned') {
                                            $badge_class = 'badge-success';
                                            $status_text = 'Dikembalikan';
                                        } elseif ($row['status'] == 'overdue') {
                                            $badge_class = 'badge-danger';
                                            $status_text = 'Terlambat';
                                        }
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $status_text ?></span>
                                    </td>
                                    <td><?= $row['fine_amount'] > 0 ? 'Rp ' . number_format($row['fine_amount'], 0, ',', '.') : '-' ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <p style="color: #999;">Belum ada transaksi hari ini</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 I Love Swiss Library. Tugas Akhir PATI</p>
    </footer>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/sirkulasi.js"></script>
</body>
</html>