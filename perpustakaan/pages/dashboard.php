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

// ==========================================
// STATISTIK REAL-TIME DARI DATABASE
// ==========================================

// 1. Total Peminjaman Hari Ini
$today = date('Y-m-d');
$query_borrowed = "SELECT COUNT(*) as total FROM transactions 
                   WHERE DATE(borrow_date) = '$today'";
$result_borrowed = mysqli_query($conn, $query_borrowed);
$total_borrowed = mysqli_fetch_assoc($result_borrowed)['total'];

// 2. Total Pengembalian Hari Ini
$query_returned = "SELECT COUNT(*) as total FROM transactions 
                   WHERE DATE(return_date) = '$today' AND status = 'returned'";
$result_returned = mysqli_query($conn, $query_returned);
$total_returned = mysqli_fetch_assoc($result_returned)['total'];

// 3. Total Buku Tersedia
$query_available = "SELECT SUM(available_copies) as total FROM books";
$result_available = mysqli_query($conn, $query_available);
$total_available = mysqli_fetch_assoc($result_available)['total'];

// 4. Total Anggota Aktif
$query_members = "SELECT COUNT(*) as total FROM members WHERE status = 'active'";
$result_members = mysqli_query($conn, $query_members);
$total_members = mysqli_fetch_assoc($result_members)['total'];

// 5. Total Buku Dipinjam (sedang dipinjam)
$query_on_loan = "SELECT COUNT(*) as total FROM transactions WHERE status = 'borrowed'";
$result_on_loan = mysqli_query($conn, $query_on_loan);
$total_on_loan = mysqli_fetch_assoc($result_on_loan)['total'];

// 6. Total Buku Terlambat
$query_overdue = "SELECT COUNT(*) as total FROM transactions 
                  WHERE status = 'borrowed' AND due_date < CURDATE()";
$result_overdue = mysqli_query($conn, $query_overdue);
$total_overdue = mysqli_fetch_assoc($result_overdue)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - I Love Swiss Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
    <div id="sakura-container-bg"></div>

    <nav>
        <div class="logo">
            <div class="logo-image">
                 <img src="../assets/img/logo.jpg">
            </div>
            <span class="logo-text">I love swiss</span>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($fullname); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($role); ?></div>
            </div>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <section class="hero-section">
        <div class="hero-wallpaper">
            <div class="wallpaper-slide active"> 
                  <img src="../assets/img/wallpaper1.jpg" alt="Wallpaper 1">
            </div>
            <div class="wallpaper-slide">
                 <img src="../assets/img/wallpaper2.jpg" alt="Wallpaper 2">
            </div>
            <div class="wallpaper-slide">
                 <img src="../assets/img/wallpaper3.jpg" alt="Wallpaper 3">
            </div>
        </div>
        <div class="wallpaper-overlay">
            <div class="hero-content">
                <h1 class="hero-title">Dashboard Perpustakaan</h1>
                <p class="hero-subtitle">Kelola sistem perpustakaan dengan mudah dan efisien</p>
            </div>
        </div>
        <div class="wallpaper-indicators">
            <div class="indicator active" data-slide="0"></div>
            <div class="indicator" data-slide="1"></div>
            <div class="indicator" data-slide="2"></div>
        </div>
    </section>

    <div class="dashboard-container">
        <div class="welcome-card">
            <h2 class="welcome-title">Selamat Datang Kembali, <?php echo htmlspecialchars($fullname); ?>! 👋</h2>
            <p class="welcome-text">Kelola sistem perpustakaan Anda dengan mudah melalui menu-menu di bawah ini</p>
            <p class="welcome-info">Login sebagai: <strong><?php echo htmlspecialchars($username); ?></strong> (<?php echo htmlspecialchars($role); ?>)</p>
        </div>

        <h3 class="section-title">Layanan Utama</h3>
        <div class="services-grid">
            <a href="opac.php" class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </div>
                <h4 class="service-title">OPAC Search</h4>
                <p class="service-desc">Cari dan telusuri koleksi buku perpustakaan dengan mudah</p>
            </a>

            <a href="bibliografi.php" class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <h4 class="service-title">Bibliografi</h4>
                <p class="service-desc">Kelola data katalog buku dan koleksi perpustakaan</p>
            </a>

            <a href="keanggotaan.php" class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h4 class="service-title">Keanggotaan</h4>
                <p class="service-desc">Kelola data anggota dan kartu keanggotaan perpustakaan</p>
            </a>

            <a href="../public_library/index.php" target="_blank" class="service-card" style="background: linear-gradient(135deg, rgba(248, 117, 170, 0.1) 0%, rgba(248, 117, 170, 0.05) 100%); border: 2px solid #F875AA;">
                <div class="service-icon" style="background: linear-gradient(135deg, #F875AA 0%, #ff4d9f 100%); color: white;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        <path d="M8 7h8"></path>
                        <path d="M8 11h8"></path>
                        <path d="M8 15h4"></path>
                    </svg>
                </div>
                <h4 class="service-title">📚 Digital Library</h4>
                <p class="service-desc">Akses perpustakaan digital dengan tema anime Jepang</p>
            </a>

            <a href="sirkulasi.php" class="service-card">
                <div class="service-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6"></path>
                        <path d="M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                    </svg>
                </div>
                <h4 class="service-title">Sirkulasi</h4>
                <p class="service-desc">Proses peminjaman dan pengembalian buku perpustakaan</p>
            </a>

            <a href="analytics.php" class="service-card" style="background: linear-gradient(135deg, rgba(248, 117, 170, 0.1) 0%, rgba(248, 117, 170, 0.05) 100%); border: 2px solid #F875AA;">
                <div class="service-icon" style="background: linear-gradient(135deg, #F875AA 0%, #ff4d9f 100%); color: white;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <h4 class="service-title">📊 Analytics</h4>
                <p class="service-desc">Dashboard analitik dan statistik perpustakaan</p>
            </a>
        </div>

        <h3 class="section-title">Statistik Real-Time</h3>
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div class="stat-number" data-target="<?php echo $total_borrowed; ?>">0</div>
                <div class="stat-label">Peminjaman Hari Ini</div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="stat-number" data-target="<?php echo $total_returned; ?>">0</div>
                <div class="stat-label">Pengembalian Hari Ini</div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <div class="stat-number" data-target="<?php echo $total_available; ?>">0</div>
                <div class="stat-label">Buku Tersedia</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-number" data-target="<?php echo $total_members; ?>">0</div>
                <div class="stat-label">Anggota Aktif</div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <div class="stat-number" data-target="<?php echo $total_on_loan; ?>">0</div>
                <div class="stat-label">Sedang Dipinjam</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="stat-number" data-target="<?php echo $total_overdue; ?>">0</div>
                <div class="stat-label">Buku Terlambat</div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 I Love Swiss Library. Tugas Akhir PATI</p>
    </footer>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>