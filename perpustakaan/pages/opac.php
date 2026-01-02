<?php
require_once __DIR__ . '/../includes/config.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    redirect('../index.php');
}

// Ambil data user dari session
$fullname = $_SESSION['fullname'];
$username = $_SESSION['username'];
$role = ucfirst($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPAC Search - I Love Swiss Library</title>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- CSS OPAC -->
    <link rel="stylesheet" href="../assets/css/opac.css">
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
            <h1 class="page-title">OPAC Search</h1>
            <p class="page-subtitle">Cari dan temukan buku yang Anda inginkan</p>
        </div>

        <div class="search-section">
            <form class="search-form" id="searchForm">
                <div class="search-input-group">
                    <input type="text" class="search-input" id="searchInput" placeholder="Cari judul buku, pengarang, atau ISBN...">
                    <select class="search-select" id="searchCategory">
                        <option value="all">Semua Kategori</option>
                        <option value="title">Judul</option>
                        <option value="author">Pengarang</option>
                        <option value="isbn">ISBN</option>
                        <option value="publisher">Penerbit</option>
                    </select>
                </div>
                <button type="submit" class="btn-search">Cari</button>
            </form>

            <div class="filter-tags">
                <div class="filter-tag active" data-filter="all">Semua</div>
                <div class="filter-tag" data-filter="Fiksi">Fiksi</div>
                <div class="filter-tag" data-filter="Sains">Sains</div>
                <div class="filter-tag" data-filter="Teknologi">Teknologi</div>
                <div class="filter-tag" data-filter="Sejarah">Sejarah</div>
                <div class="filter-tag" data-filter="Seni">Seni</div>
                <div class="filter-tag" data-filter="Pendidikan">Pendidikan</div>
                <div class="filter-tag" data-filter="Ekonomi">Ekonomi</div>
                <div class="filter-tag" data-filter="Psikologi">Psikologi</div>
                <div class="filter-tag" data-filter="Agama">Agama</div>
            </div>
        </div>

        <div class="results-header">
            <div class="results-count">Menampilkan <strong id="resultCount">0</strong> hasil</div>
            <select class="sort-select" id="sortBy">
                <option value="relevance">Paling Relevan</option>
                <option value="title">Judul A-Z</option>
                <option value="author">Pengarang A-Z</option>
                <option value="year">Tahun Terbaru</option>
            </select>
        </div>

        <div class="books-grid" id="booksGrid">
            <!-- Books will be loaded here by JavaScript -->
        </div>
    </div>

    <!-- MODAL DETAIL BUKU -->
    <div class="modal-overlay" id="bookDetailModal">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            
            <div class="modal-body">
                <!-- Cover Buku -->
                <div>
                    <div class="modal-book-cover" id="modalBookCover">
                        <span>No Cover</span>
                    </div>
                </div>

                <!-- Info Buku -->
                <div class="modal-book-info">
                    <!-- Header -->
                    <div class="modal-book-header">
                        <h2 class="modal-book-title" id="modalBookTitle">Judul Buku</h2>
                        <p class="modal-book-author" id="modalBookAuthor">Nama Pengarang</p>
                        <span class="modal-book-status status-available" id="modalBookStatus">Tersedia</span>
                    </div>

                    <!-- Metadata Bibliografi -->
                    <div class="metadata-section">
                        <div class="metadata-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            Informasi Bibliografi
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">ISBN</div>
                            <div class="metadata-value" id="modalISBN">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Penerbit</div>
                            <div class="metadata-value" id="modalPublisher">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Tahun Terbit</div>
                            <div class="metadata-value" id="modalPublishYear">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Tempat Terbit</div>
                            <div class="metadata-value" id="modalPublishPlace">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Jumlah Halaman</div>
                            <div class="metadata-value" id="modalPages">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Bahasa</div>
                            <div class="metadata-value" id="modalLanguage">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Kategori</div>
                            <div class="metadata-value" id="modalCategory">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Nomor Panggil</div>
                            <div class="metadata-value" id="modalCallNumber">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Total Eksemplar</div>
                            <div class="metadata-value" id="modalCopies">-</div>
                        </div>
                        
                        <div class="metadata-item">
                            <div class="metadata-label">Ketersediaan</div>
                            <div class="metadata-value" id="modalAvailable">-</div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="description-section">
                        <div class="metadata-title" style="margin-bottom: 12px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            Deskripsi
                        </div>
                        <p class="description-text" id="modalDescription">
                            Deskripsi buku akan ditampilkan di sini...
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="modal-actions">
                        <button class="btn-modal btn-primary-modal" id="btnBorrow">Pinjam Buku</button>
                        <button class="btn-modal btn-secondary-modal" onclick="window.print()">Cetak Detail</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container (akan di-generate oleh JavaScript) -->
    <div id="toast-container"></div>

    <footer>
        <p>&copy; 2025 I Love Swiss Library. Tugas Akhir PATI</p>
    </footer>

    <!-- JS OPAC -->
    <script src="../assets/js/opac.js"></script>
</body>
</html>