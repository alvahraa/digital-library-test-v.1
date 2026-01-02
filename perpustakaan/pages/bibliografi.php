<?php
require_once __DIR__ . '/../includes/config.php';

if (!is_logged_in()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    redirect('../index.php');
}

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini!";
    redirect('dashboard.php');
}

$fullname = $_SESSION['fullname'];
$username = $_SESSION['username'];
$role = ucfirst($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliografi - I Love Swiss Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bibliografi.css">
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
            <h1 class="page-title">Manajemen Bibliografi</h1>
            <button class="btn-add" onclick="openAddModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Buku Baru
            </button>
        </div>

        <div class="content-wrapper">
            <div class="search-bar">
                <input type="text" class="search-input" id="searchInput" placeholder="Cari judul, pengarang, ISBN...">
                <select class="form-select" style="width: 200px;" id="categoryFilter">
                    <option value="">Semua Kategori</option>
                    <option value="Fiksi">Fiksi</option>
                    <option value="Non-Fiksi">Non-Fiksi</option>
                    <option value="Sains">Sains</option>
                    <option value="Teknologi">Teknologi</option>
                    <option value="Sejarah">Sejarah</option>
                    <option value="Seni">Seni</option>
                    <option value="Pendidikan">Pendidikan</option>
                    <option value="Ekonomi">Ekonomi</option>
                    <option value="Psikologi">Psikologi</option>
                    <option value="Agama">Agama</option>
                </select>
            </div>

            <div class="table-container">
                <table id="booksTable">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Cover</th>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>ISBN</th>
                            <th style="width: 80px;">Tahun</th>
                            <th style="width: 80px;">Stok</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination"></div>
        </div>
    </div>

    <!-- Modal Add/Edit Book -->
    <div class="modal-overlay" id="bookModal">
        <div class="modal-content" style="max-width: 800px;">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h2 class="modal-title" id="modalTitle">Tambah Buku Baru</h2>
            
            <form id="bookForm" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Judul Buku *</label>
                        <input type="text" class="form-input" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Pengarang *</label>
                        <input type="text" class="form-input" name="author" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ISBN *</label>
                        <input type="text" class="form-input" name="isbn" required placeholder="978-xxx-xxx-xxx-x">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Penerbit</label>
                        <input type="text" class="form-input" name="publisher">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" class="form-input" name="publish_year" min="1900" max="2099">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tempat Terbit</label>
                        <input type="text" class="form-input" name="publish_place" placeholder="Jakarta">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jumlah Halaman</label>
                        <input type="number" class="form-input" name="pages" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bahasa</label>
                        <input type="text" class="form-input" name="language" value="Indonesia">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category">
                            <option value="">Pilih Kategori</option>
                            <option value="Fiksi">Fiksi</option>
                            <option value="Non-Fiksi">Non-Fiksi</option>
                            <option value="Sains">Sains</option>
                            <option value="Teknologi">Teknologi</option>
                            <option value="Sejarah">Sejarah</option>
                            <option value="Seni">Seni</option>
                            <option value="Pendidikan">Pendidikan</option>
                            <option value="Ekonomi">Ekonomi</option>
                            <option value="Psikologi">Psikologi</option>
                            <option value="Agama">Agama</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nomor Panggil</label>
                        <input type="text" class="form-input" name="call_number" placeholder="TECH-001">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Jumlah Eksemplar *</label>
                        <input type="number" class="form-input" name="total_copies" required min="1" value="1">
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Cover Buku</label>
                        <input type="file" class="form-input" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/gif">
                        <small style="color: #999; font-size: 12px;">Format: JPG, PNG, GIF (Max 2MB)</small>
                        <div id="coverPreview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-textarea" name="description" rows="4" placeholder="Deskripsi singkat tentang buku..."></textarea>
                    </div>
                </div>
                
                <button type="submit" class="form-submit">Simpan Buku</button>
            </form>
        </div>
    </div>

    <!-- Modal Kelola Eksemplar -->
    <div class="modal-overlay" id="copiesModal">
        <div class="modal-content" style="max-width: 1100px;">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h2 class="modal-title">Kelola Eksemplar: <span id="copiesBookTitle"></span></h2>
            
            <!-- Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">
                <div style="padding: 15px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 10px; color: white;">
                    <div style="font-size: 12px; opacity: 0.9;">Tersedia</div>
                    <div style="font-size: 28px; font-weight: 600;" id="summaryAvailable">0</div>
                </div>
                <div style="padding: 15px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 10px; color: white;">
                    <div style="font-size: 12px; opacity: 0.9;">Dipinjam</div>
                    <div style="font-size: 28px; font-weight: 600;" id="summaryBorrowed">0</div>
                </div>
                <div style="padding: 15px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 10px; color: white;">
                    <div style="font-size: 12px; opacity: 0.9;">Maintenance</div>
                    <div style="font-size: 28px; font-weight: 600;" id="summaryMaintenance">0</div>
                </div>
                <div style="padding: 15px; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 10px; color: white;">
                    <div style="font-size: 12px; opacity: 0.9;">Rusak/Hilang</div>
                    <div style="font-size: 28px; font-weight: 600;" id="summaryDamaged">0</div>
                </div>
            </div>
            
            <button class="btn-add" onclick="openAddCopyModal()" style="margin-bottom: 20px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Eksemplar
            </button>
            
            <div class="table-container">
                <table id="copiesTable">
                    <thead>
                        <tr>
                            <th style="width: 120px;">No. Eksemplar</th>
                            <th style="width: 150px;">Barcode</th>
                            <th style="width: 160px;">Status</th>
                            <th style="width: 100px;">Kondisi</th>
                            <th>Lokasi</th>
                            <th>Catatan</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                                Memuat data eksemplar...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form Eksemplar -->
    <div class="modal-overlay" id="copyFormModal">
        <div class="modal-content" style="max-width: 600px;">
            <button class="modal-close" onclick="closeCopyFormModal()">&times;</button>
            <h2 class="modal-title" id="copyModalTitle">Tambah Eksemplar Baru</h2>
            
            <form id="copyForm" onsubmit="handleCopySubmit(event)">
                <input type="hidden" name="copy_id">
                
                <div class="form-group">
                    <label class="form-label">Nomor Eksemplar *</label>
                    <input type="text" class="form-input" name="copy_number" required placeholder="EX-001">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Barcode</label>
                    <input type="text" class="form-input" name="barcode" placeholder="123456789">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        <option value="available">Tersedia</option>
                        <option value="borrowed">Dipinjam</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="lost">Hilang</option>
                        <option value="damaged">Rusak</option>
                        <option value="reserved">Reserved</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Kondisi *</label>
                    <select class="form-select" name="condition" required>
                        <option value="good">Baik</option>
                        <option value="fair">Cukup</option>
                        <option value="poor">Buruk</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Lokasi *</label>
                    <input type="text" class="form-input" name="location" required value="Perpustakaan">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea class="form-textarea" name="notes" rows="3" placeholder="Catatan tambahan..."></textarea>
                </div>
                
                <button type="submit" class="form-submit">Simpan Eksemplar</button>
            </form>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <footer>
        <p>&copy; 2025 I Love Swiss Library. Tugas Akhir PATI</p>
    </footer>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/bibliografi.js"></script>
</body>
</html>