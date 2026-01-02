<?php
require_once __DIR__ . '/../includes/config.php';

// Check if admin session exists for "Back to Admin" link
$is_admin = is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Library - I Love Swiss Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoE1zsBHyh3Z4k9h6YewsG7C8h+7x1YkP7Qd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom SweetAlert2 Anime Style */
        .anime-swal-popup {
            border-radius: 20px !important;
            border: 3px solid #F875AA !important;
            box-shadow: 0 12px 48px rgba(248, 117, 170, 0.3) !important;
        }
        .anime-swal-title {
            font-family: 'Quicksand', sans-serif !important;
            color: #F875AA !important;
        }
        .anime-swal-button {
            background: linear-gradient(135deg, #F875AA 0%, #ff4d9f 100%) !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            padding: 12px 32px !important;
        }
    </style>
</head>
<body>
    <!-- Sakura Petals Background Animation -->
    <div class="sakura-container">
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
        <div class="sakura-petal"></div>
    </div>

    <!-- Navigation -->
    <nav class="library-nav">
        <div class="nav-content">
            <div class="logo-section">
                <div class="logo-circle">
                    <img src="../assets/img/logo.jpg" alt="I Love Swiss Logo" class="logo-img">
                </div>
                <div class="library-title">
                    <span class="library-name">I Love Swiss</span>
                    <span class="library-subtitle">Digital Library</span>
                </div>
            </div>
            <div class="nav-links">
                <?php if ($is_admin): ?>
                    <a href="../pages/dashboard.php" class="nav-link admin-link">
                        <span>👤</span> Back to Admin
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section hero-with-wallpaper">
        <div class="hero-content glassy-hero">
            <h1 class="hero-title">
                <span class="title-line">Cari Petualanganmu</span>
                <span class="title-line highlight">Berikutnya...</span>
            </h1>
            <p class="hero-subtitle">Jelajahi koleksi buku digital kami</p>
            
            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input 
                        type="text" 
                        id="bookSearch" 
                        class="search-input" 
                        placeholder="Cari berdasarkan judul, pengarang, atau kategori..."
                        autocomplete="off"
                    >
                    <button class="search-btn" id="searchBtn">
                        <span>検索</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Book Grid Section -->
    <section class="books-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Koleksi Buku</h2>
                <div class="filter-tabs" id="categoryFilter">
                    <button class="filter-tab active" data-category="all">すべて (Semua)</button>
                    <button class="filter-tab" data-category="Fiksi">Fiksi</button>
                    <button class="filter-tab" data-category="Teknologi">Teknologi</button>
                    <button class="filter-tab" data-category="Sains">Sains</button>
                    <button class="filter-tab" data-category="Sejarah">Sejarah</button>
                    <button class="filter-tab" data-category="Pendidikan">Pendidikan</button>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="loading-spinner"></div>
                <p>読み込み中...</p>
            </div>

            <!-- Books Grid -->
            <div id="booksGrid" class="books-grid">
                <!-- Books will be loaded here via JavaScript -->
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <div class="empty-icon">📖</div>
                <h3>本が見つかりませんでした</h3>
                <p>Tidak ada buku yang ditemukan. Coba kata kunci lain.</p>
            </div>
        </div>
    </section>

    <!-- Sakura Wallpaper Footer -->
    <footer class="sakura-footer">
        <div class="sakura-footer-inner">
            <div class="sakura-footer-text">
                <div class="footer-title">I Love Swiss Digital Library</div>
                <div class="footer-subtitle">Tempat cerita baru bermula, ditemani sakura yang selalu jatuh dengan lembut.</div>
            </div>
            <div class="sakura-footer-graphic">
                <div class="sakura-mount"></div>
                <div class="sakura-sun"></div>
                <div class="sakura-cloud cloud-1"></div>
                <div class="sakura-cloud cloud-2"></div>
            </div>
        </div>
    </footer>

    <!-- Borrow Modal -->
    <div id="borrowModal" class="modal-overlay">
        <div class="modal-content anime-modal">
            <button class="modal-close" id="closeModal">&times;</button>
            <div class="modal-header">
                <h2 class="modal-title">📚 Pinjam Buku</h2>
            </div>
            <div class="modal-body">
                <div class="book-preview" id="borrowBookPreview">
                    <!-- Book info will be inserted here -->
                </div>
                <form id="borrowForm">
                    <div class="form-group">
                        <label class="form-label">
                            <span>🆔</span> Member ID
                        </label>
                        <input 
                            type="text" 
                            id="memberIdInput" 
                            class="form-input" 
                            placeholder="Masukkan Member ID (contoh: MBR001)"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelBorrow">キャンセル</button>
                        <button type="submit" class="btn-borrow">借りる</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Book Detail Modal -->
    <div id="detailModal" class="modal-overlay">
        <div class="modal-content detail-modal">
            <button class="modal-close" id="closeDetail">&times;</button>
            <div class="detail-layout">
                <div class="detail-cover" id="detailCover">
                    <div class="cover-placeholder">📚</div>
                </div>
                <div class="detail-info">
                    <div class="detail-header">
                        <p class="detail-badge">I Love Swiss Digital Library</p>
                        <h2 class="detail-title" id="detailTitle">Judul Buku</h2>
                        <p class="detail-author" id="detailAuthor">Nama Pengarang</p>
                    </div>
                    <div class="detail-meta">
                        <div class="meta-item">
                            <i class="fa-solid fa-file-lines"></i>
                            <span id="detailPages">0 halaman</span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-regular fa-calendar"></i>
                            <span id="detailDate">-</span>
                        </div>
                    </div>
                    <div class="detail-synopsis" id="detailSynopsis">
                        Sinopsis akan tampil di sini.
                    </div>
                    <div class="detail-actions">
                        <button class="btn-borrow" id="detailBorrowBtn">Pinjam Buku</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

