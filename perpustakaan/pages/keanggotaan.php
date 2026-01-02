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
$role = ucfirst($_SESSION['role']); // Admin atau Member
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keanggotaan - I Love Swiss Library</title>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- CSS Keanggotaan -->
    <link rel="stylesheet" href="../assets/css/keanggotaan.css">
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
			<a href="dashboard.php" class="btn-logout" style="background: transparent; border: 1px solid #F875AA; color: #F875AA; margin-right: 10px;">Dashboard</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">Manajemen Keanggotaan</h1>
            <p class="page-subtitle">Kelola data anggota perpustakaan dengan mudah dan efisien</p>
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value" id="totalMembers">0</div>
                <div class="stat-label">Total Anggota</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="activeMembers">0</div>
                <div class="stat-label">Anggota Aktif</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="newThisMonth">0</div>
                <div class="stat-label">Baru Bulan Ini</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="expiringThisMonth">0</div>
                <div class="stat-label">Akan Berakhir</div>
            </div>
        </div>

        <div class="action-bar">
            <div class="search-box">
                <input type="text" class="search-input" id="searchInput" placeholder="Cari nama, email, atau nomor anggota...">
                <button class="btn btn-secondary" onclick="searchMembers()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Cari
                </button>
            </div>
            <div class="filter-group">
                <select class="filter-select" id="statusFilter" onchange="filterMembers()">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                    <option value="suspended">Ditangguhkan</option>
                </select>
                <select class="filter-select" id="roleFilter" onchange="filterMembers()">
                    <option value="">Semua Role</option>
                    <option value="library_member">Anggota Perpustakaan</option>
                    <option value="intern">Anak Magang</option>
                    <option value="staff">Staff Perpustakaan</option>
                </select>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tambah Anggota
                </button>
            </div>
        </div>

        <div class="content-card">
            <div class="table-container">
                <table id="membersTable">
                    <thead>
                        <tr>
                            <th>No. Anggota</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Tipe</th>
                            <th>Role</th>
                            <th>Bergabung</th>
                            <th>Berakhir</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        <!-- Data will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Member Modal -->
    <div class="modal" id="memberModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Tambah Anggota Baru</h2>
            </div>
            <form id="memberForm" onsubmit="saveMember(event)">
                <input type="hidden" id="memberId">
                
                <div class="form-group">
                    <label class="form-label">Nomor Anggota *</label>
                    <input type="text" class="form-input" id="memberNumber" placeholder="Otomatis dibuat" readonly>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Depan *</label>
                        <input type="text" class="form-input" id="firstName" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Belakang *</label>
                        <input type="text" class="form-input" id="lastName" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-input" id="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telepon *</label>
                        <input type="tel" class="form-input" id="phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Lengkap *</label>
                    <input type="text" class="form-input" id="address" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir *</label>
                        <input type="date" class="form-input" id="birthDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin *</label>
                        <select class="form-input" id="gender" required>
                            <option value="">Pilih</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipe Keanggotaan *</label>
                        <select class="form-input" id="memberType" required>
                            <option value="">Pilih</option>
                            <option value="student">Pelajar/Mahasiswa</option>
                            <option value="teacher">Guru/Dosen</option>
                            <option value="public">Umum</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role Anggota *</label>
                        <select class="form-input" id="memberRole" required>
                            <option value="library_member">Anggota Perpustakaan</option>
                            <option value="intern">Anak Magang</option>
                            <option value="staff">Staff Perpustakaan</option>
                        </select>
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                            💡 Library Member: dapat pinjam buku | Intern: input bibliografi | Staff: akses penuh
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Institusi/Sekolah</label>
                        <input type="text" class="form-input" id="institution" placeholder="Opsional">
                    </div>
                    <div class="form-group" id="durationGroup">
                        <label class="form-label">Durasi (Bulan) *</label>
                        <select class="form-input" id="duration" required>
                            <option value="3">3 Bulan</option>
                            <option value="6">6 Bulan</option>
                            <option value="12" selected>12 Bulan</option>
                            <option value="24">24 Bulan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="border-top: 2px solid #f0f0f0; padding-top: 20px; margin-top: 20px;">
                    <h3 style="color: #F875AA; font-size: 16px; margin-bottom: 15px;">
                        🔐 Akun Login
                    </h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-input" id="username" required placeholder="Contoh: budi_santoso">
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                            Username untuk login (hanya huruf, angka, dan underscore)
                        </small>
                    </div>
                    <div class="form-group" id="passwordGroup">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-input" id="password" required placeholder="Minimal 6 karakter">
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                            Password untuk login (minimal 6 karakter)
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto Profil</label>
                    <input type="file" class="form-input" id="photoUpload" name="photo" accept="image/jpeg,image/png,image/gif">
                    <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                    <div id="photoPreview" style="margin-top: 10px;"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Member Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
            <div class="modal-header">
                <h2 class="modal-title">Detail Anggota</h2>
            </div>
            <div id="memberDetails"></div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal" id="resetPasswordModal">
        <div class="modal-content" style="max-width: 500px;">
            <button class="modal-close" onclick="closeResetPasswordModal()">&times;</button>
            <div class="modal-header">
                <h2 class="modal-title">Reset Password Anggota</h2>
            </div>
            
            <input type="hidden" id="resetPasswordMemberId">
            
            <div style="background: #FFF5F9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="margin-bottom: 8px;">
                    <label style="font-weight: 500; color: #F875AA; font-size: 12px;">NAMA ANGGOTA</label>
                    <p style="margin-top: 5px; font-size: 16px; font-weight: 500;" id="resetPasswordMemberName"></p>
                </div>
                <div>
                    <label style="font-weight: 500; color: #F875AA; font-size: 12px;">USERNAME</label>
                    <p style="margin-top: 5px; font-size: 14px;" id="resetPasswordUsername"></p>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password Baru *</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" class="form-input" id="newPasswordInput" placeholder="Masukkan password baru" style="flex: 1;">
                    <button type="button" class="btn btn-secondary" onclick="generateRandomPassword()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        Generate
                    </button>
                </div>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Minimal 6 karakter. Klik "Generate" untuk password acak.</small>
            </div>

            <div id="newPasswordResult" style="display: none; background: #E8F5E9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 2px solid #4CAF50;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <label style="font-weight: 500; color: #4CAF50; font-size: 12px;">✅ PASSWORD BARU BERHASIL DIRESET</label>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <code id="newPasswordDisplay" style="flex: 1; padding: 10px; background: white; border-radius: 6px; font-size: 16px; font-weight: 600; color: #333;"></code>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="copyPassword()" title="Copy Password">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </button>
                </div>
                <small style="color: #4CAF50; font-size: 12px; display: block; margin-top: 10px;">⚠️ Catat password ini! Berikan kepada anggota untuk login.</small>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeResetPasswordModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmResetPassword()">Reset Password</button>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification">
        <div class="notification-icon">✓</div>
        <div class="notification-text" id="notificationText"></div>
    </div>

    <footer>
        <p>&copy; 2025 I Love Swiss Library. Tugas Akhir PATI</p>
    </footer>

    <!-- JS Utama untuk Sakura -->
    <script src="../assets/js/script.js"></script>
    <!-- JS Keanggotaan -->
    <script src="../assets/js/keanggotaan.js"></script>
</body>
</html>