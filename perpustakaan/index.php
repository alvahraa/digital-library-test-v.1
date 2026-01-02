<?php
require_once __DIR__ . '/includes/config.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    redirect('pages/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Love Swiss Library</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Alert Notifications */
        .alert {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 600px;
            border-radius: 8px;
            font-size: 14px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .alert-error {
            background: #fee;
            border-left: 4px solid #c33;
            color: #c33;
        }
        .alert-success {
            background: #efe;
            border-left: 4px solid #3c3;
            color: #3c3;
        }
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Sakura Container -->
    <div id="sakura-container-bg"></div>
    <div id="sakura-container-fg"></div>

    <!-- Notifikasi Error/Success -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error" id="alert">
        <?php 
        echo htmlspecialchars($_SESSION['error']); 
        unset($_SESSION['error']);
        ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" id="alert">
        <?php 
        echo htmlspecialchars($_SESSION['success']); 
        unset($_SESSION['success']);
        ?>
    </div>
    <?php endif; ?>

    <nav>
        <div class="logo">
            <div class="logo-image">
                <img src="assets/img/logo.jpg" alt="I Love Swiss Logo">
            </div>
            <span class="logo-text">I love swiss</span>
        </div>
        <a href="#" class="btn-login" onclick="openModal('login'); return false;">Login</a>
    </nav>

    <section class="hero">
        <div class="hero-image-container">
            <img src="assets/img/hero.jpg" alt="Hero Image">
        </div>

        <div class="hero-content">
            <h1>Perpustakaan Modern</h1>
            <p class="hero-subtitle">
                Sistem otomasi perpustakaan dengan OPAC search, manajemen anggota, 
                dan sirkulasi peminjaman yang terintegrasi
            </p>
            <div class="hero-buttons">
                <button class="btn-primary" onclick="openModal('login')">
                    Login Sekarang
                </button>
                <button class="btn-secondary">
                    Pelajari Lebih Lanjut
                </button>
            </div>
        </div>
    </section>

    <section class="features">
        <h2 class="section-title">Fitur Unggulan</h2>
        <div class="features-grid">
            <div class="feature-card">
                <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <h3>OPAC Search</h3>
                <p>Pencarian buku berdasarkan judul, pengarang, atau kategori dengan hasil yang akurat dan cepat</p>
            </div>
            <div class="feature-card">
                <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <h3>Bibliografi</h3>
                <p>Manajemen katalog buku lengkap dengan detail informasi dan cover</p>
            </div>
            <div class="feature-card">
                <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <h3>Keanggotaan</h3>
                <p>Sistem manajemen anggota perpustakaan yang terintegrasi</p>
            </div>
            <div class="feature-card">
                <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21.5 2v6h-6M2.5 22v-6h6"></path>
                    <path d="M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                </svg>
                <h3>Sirkulasi</h3>
                <p>Peminjaman dan pengembalian otomatis dengan tracking stok dan perhitungan denda</p>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="stats-grid">
            <div>
                <div class="stat-number" data-target="28">0</div>
                <div class="stat-label">Koleksi Buku</div>
            </div>
            <div>
                <div class="stat-number" data-target="4">0</div>
                <div class="stat-label">Anggota Aktif</div>
            </div>
            <div>
                <div class="stat-number" data-target="3">0</div>
                <div class="stat-label">Transaksi Hari Ini</div>
            </div>
        </div>
    </section>

    <section class="cta">
        <h2>Sistem Perpustakaan Terintegrasi</h2>
        <p>Login sebagai admin untuk mengelola perpustakaan</p>
        <button class="btn-primary" onclick="openModal('login')">
            Login Sekarang
        </button>
    </section>

    <footer>
        <p>&copy; 2025 I Love Swiss Library. Tugas Akhir PATI</p>
    </footer>

    <!-- Modal Login -->
    <div class="modal-overlay" id="loginModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('login')">&times;</button>
            <h2 class="modal-title">Login</h2>
            <p class="modal-subtitle">Masuk ke sistem perpustakaan</p>
            
            <form action="pages/login.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" name="username" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-input" name="password" placeholder="Masukkan password" required>
                </div>
                
                <div class="form-checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>
                
                <button type="submit" class="form-submit">Masuk</button>
            </form>
            
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        // Auto hide alert after 5 seconds
        setTimeout(function() {
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);

        // SweetAlert2 for logout notification
        <?php if (isset($_SESSION['logout_success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'おかえりなさい！',
            html: '<p style="font-size: 18px; margin: 20px 0;">Anda telah berhasil logout!</p><p style="color: #666;">Terima kasih telah menggunakan sistem perpustakaan.</p>',
            confirmButtonColor: '#F875AA',
            confirmButtonText: '了解',
            customClass: {
                popup: 'anime-swal-popup',
                title: 'anime-swal-title',
                confirmButton: 'anime-swal-button'
            }
        });
        <?php 
        unset($_SESSION['logout_success']);
        endif; 
        ?>
    </script>
</body>
</html>