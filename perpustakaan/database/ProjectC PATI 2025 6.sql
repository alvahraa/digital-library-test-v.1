-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Des 2025 pada 02.51
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `publish_year` year(4) DEFAULT NULL,
  `publish_place` varchar(100) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `call_number` varchar(50) DEFAULT NULL,
  `total_copies` int(11) DEFAULT NULL,
  `available_copies` int(11) DEFAULT NULL,
  `use_copy_tracking` tinyint(1) DEFAULT 0 COMMENT '0=old system, 1=new copy tracking',
  `cover_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `publisher`, `publish_year`, `publish_place`, `pages`, `language`, `category`, `call_number`, `total_copies`, `available_copies`, `use_copy_tracking`, `cover_image`, `description`, `created_at`) VALUES
(1, 'Pemrograman Web', 'Abdul Kadir', '978-123-001', 'Andi Publisher', '2023', NULL, NULL, NULL, 'Teknologi', 'TECH-001', 5, 4, 0, NULL, 'Buku panduan pemrograman web modern', '2025-12-09 12:09:22'),
(2, 'Database MySQL', 'Rinaldi Munir', '978-123-002', 'Informatika', '2023', NULL, NULL, NULL, 'Teknologi', 'TECH-002', 3, 3, 0, NULL, 'Panduan lengkap database MySQL', '2025-12-09 12:09:22'),
(3, 'Algoritma Pemrograman', 'Rosa A.S.', '978-123-003', 'Modula', '2022', NULL, NULL, NULL, 'Teknologi', 'TECH-003', 4, 3, 0, NULL, 'Dasar-dasar algoritma pemrograman', '2025-12-09 12:09:22'),
(4, 'Belajar Python untuk Pemula', 'Budi Raharjo', '978-602-001-111', 'Informatika', '2023', 'Bandung', 320, 'Indonesia', 'Teknologi', 'TECH-004', 7, 5, 0, 'cover_1765324890_6938b85a232b5.png', 'Buku panduan lengkap Python untuk pemula dengan contoh praktis dan studi kasus real-world.', '2025-12-09 14:36:30'),
(5, 'Jaringan Komputer Modern', 'Onno W. Purbo', '978-602-001-112', 'Andi Publisher', '2023', 'Yogyakarta', 450, 'Indonesia', 'Teknologi', 'TECH-005', 3, 1, 0, NULL, 'Membahas konsep jaringan komputer dari dasar hingga advanced, termasuk routing, switching, dan security.', '2025-12-09 14:36:30'),
(6, 'Machine Learning dengan TensorFlow', 'Ahmad Yani', '978-602-001-113', 'Elex Media', '2024', 'Jakarta', 520, 'Indonesia', 'Teknologi', 'TECH-006', 3, 2, 0, NULL, 'Panduan praktis machine learning menggunakan TensorFlow dengan contoh kasus industri.', '2025-12-09 14:36:30'),
(7, 'Sistem Informasi Manajemen', 'Jogiyanto HM', '978-602-001-114', 'Andi Publisher', '2022', 'Yogyakarta', 380, 'Indonesia', 'Teknologi', 'TECH-007', 6, 5, 0, NULL, 'Konsep dan implementasi sistem informasi dalam organisasi modern.', '2025-12-09 14:36:30'),
(8, 'Keamanan Siber', 'Rudi Hermawan', '978-602-001-115', 'Informatika', '2023', 'Bandung', 410, 'Indonesia', 'Teknologi', 'TECH-008', 4, 4, 0, NULL, 'Panduan lengkap tentang keamanan siber, ethical hacking, dan proteksi sistem.', '2025-12-09 14:36:30'),
(9, 'Laskar Pelangi', 'Andrea Hirata', '978-602-002-201', 'Bentang Pustaka', '2019', 'Yogyakarta', 540, 'Indonesia', 'Fiksi', 'FIK-001', 5, 4, 0, NULL, 'Novel inspiratif tentang perjuangan anak-anak Belitung meraih mimpi melalui pendidikan.', '2025-12-09 14:36:30'),
(10, 'Bumi Manusia', 'Pramoedya Ananta Toer', '978-602-002-202', 'Lentera Dipantara', '2020', 'Jakarta', 620, 'Indonesia', 'Fiksi', 'FIK-002', 4, 3, 0, NULL, 'Karya masterpiece tentang perjuangan melawan kolonialisme di era Hindia Belanda.', '2025-12-09 14:36:30'),
(11, 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', '978-602-002-203', 'Republika', '2021', 'Jakarta', 420, 'Indonesia', 'Fiksi', 'FIK-003', 6, 5, 0, NULL, 'Novel romantis yang mengangkat nilai-nilai Islam dalam kehidupan modern.', '2025-12-09 14:36:30'),
(12, 'Cantik Itu Luka', 'Eka Kurniawan', '978-602-002-204', 'Gramedia', '2020', 'Jakarta', 520, 'Indonesia', 'Fiksi', 'FIK-004', 3, 2, 0, NULL, 'Novel magis realis yang mengisahkan kehidupan seorang wanita cantik bernama Dewi Ayu.', '2025-12-09 14:36:30'),
(13, 'Fisika Kuantum untuk Pemula', 'Prof. Dr. Terry Mart', '978-602-003-301', 'Erlangga', '2023', 'Jakarta', 450, 'Indonesia', 'Sains', 'SCI-001', 4, 4, 0, NULL, 'Pengantar fisika kuantum yang mudah dipahami dengan ilustrasi dan contoh praktis.', '2025-12-09 14:36:30'),
(14, 'Biologi Molekuler', 'Dr. Sangkot Marzuki', '978-602-003-302', 'Erlangga', '2022', 'Jakarta', 560, 'Indonesia', 'Sains', 'SCI-002', 5, 4, 0, NULL, 'Membahas struktur dan fungsi molekul dalam sistem kehidupan.', '2025-12-09 14:36:30'),
(15, 'Kimia Organik Dasar', 'Dr. Ismunandar', '978-602-003-303', 'ITB Press', '2023', 'Bandung', 480, 'Indonesia', 'Sains', 'SCI-003', 4, 3, 0, NULL, 'Dasar-dasar kimia organik dengan pendekatan praktis dan aplikatif.', '2025-12-09 14:36:30'),
(16, 'Sejarah Indonesia Modern', 'Prof. Anhar Gonggong', '978-602-004-401', 'Gramedia', '2021', 'Jakarta', 680, 'Indonesia', 'Sejarah', 'HIST-001', 5, 5, 0, NULL, 'Kajian komprehensif sejarah Indonesia dari masa kolonial hingga reformasi.', '2025-12-09 14:36:30'),
(17, 'Majapahit: Kerajaan Agraris Terbesar', 'Dr. Agus Aris Munandar', '978-602-004-402', 'Komunitas Bambu', '2022', 'Jakarta', 420, 'Indonesia', 'Sejarah', 'HIST-002', 4, 4, 0, NULL, 'Mengungkap kejayaan Majapahit sebagai kerajaan maritim terbesar di Nusantara.', '2025-12-09 14:36:30'),
(18, 'Perang Diponegoro', 'Peter Carey', '978-602-004-403', 'KPG', '2020', 'Jakarta', 750, 'Indonesia', 'Sejarah', 'HIST-003', 3, 2, 0, NULL, 'Kajian mendalam tentang Perang Diponegoro dan perlawanan terhadap kolonial Belanda.', '2025-12-09 14:36:30'),
(19, 'Seni Rupa Indonesia', 'Dr. Mikke Susanto', '978-602-005-501', 'Dicti', '2022', 'Yogyakarta', 380, 'Indonesia', 'Seni', 'ART-001', 4, 4, 0, NULL, 'Perkembangan seni rupa Indonesia dari tradisional hingga kontemporer.', '2025-12-09 14:36:30'),
(20, 'Filosofi Seni Jawa', 'Ki Supriyoko', '978-602-005-502', 'Penerbit Kanisius', '2021', 'Yogyakarta', 320, 'Indonesia', 'Seni', 'ART-002', 3, 3, 0, NULL, 'Makna dan filosofi di balik karya seni tradisional Jawa.', '2025-12-09 14:36:30'),
(21, 'Psikologi Pendidikan', 'Prof. Dr. Muhibbin Syah', '978-602-006-601', 'Remaja Rosdakarya', '2023', 'Bandung', 420, 'Indonesia', 'Pendidikan', 'EDU-001', 6, 6, 0, NULL, 'Teori dan praktik psikologi dalam konteks pendidikan modern.', '2025-12-09 14:36:30'),
(22, 'Metodologi Penelitian Pendidikan', 'Prof. Dr. Sugiyono', '978-602-006-602', 'Alfabeta', '2022', 'Bandung', 460, 'Indonesia', 'Pendidikan', 'EDU-002', 5, 4, 0, NULL, 'Panduan lengkap metodologi penelitian kuantitatif dan kualitatif dalam pendidikan.', '2025-12-09 14:36:30'),
(23, 'Ekonomi Makro Indonesia', 'Prof. Dr. Boediono', '978-602-007-701', 'BPFE', '2023', 'Yogyakarta', 520, 'Indonesia', 'Ekonomi', 'ECO-001', 5, 5, 0, NULL, 'Analisis kebijakan ekonomi makro Indonesia dan tantangan pembangunan.', '2025-12-09 14:36:30'),
(24, 'Manajemen Keuangan Perusahaan', 'Lukas Setia Atmaja', '978-602-007-702', 'Andi Publisher', '2022', 'Yogyakarta', 480, 'Indonesia', 'Ekonomi', 'ECO-002', 4, 3, 0, NULL, 'Konsep dan aplikasi manajemen keuangan untuk perusahaan modern.', '2025-12-09 14:36:30'),
(25, 'Psikologi Kepribadian', 'Dr. Alwisol', '978-602-008-801', 'UMM Press', '2022', 'Malang', 380, 'Indonesia', 'Psikologi', 'PSY-001', 5, 5, 0, NULL, 'Teori-teori kepribadian dari berbagai perspektif psikologi.', '2025-12-09 14:36:30'),
(26, 'Mindfulness untuk Kehidupan Modern', 'Dr. Adrianto Djokosoetono', '978-602-008-802', 'Gramedia', '2023', 'Jakarta', 280, 'Indonesia', 'Psikologi', 'PSY-002', 4, 4, 0, NULL, 'Praktik mindfulness untuk mengatasi stres dan meningkatkan kualitas hidup.', '2025-12-09 14:36:30'),
(27, 'Tafsir Al-Misbah', 'Prof. Dr. M. Quraish Shihab', '978-602-009-901', 'Lentera Hati', '2020', 'Jakarta', 680, 'Indonesia', 'Agama', 'REL-001', 3, 2, 0, NULL, 'Tafsir Al-Quran dengan pendekatan kontekstual dan mudah dipahami.', '2025-12-09 14:36:30'),
(28, 'Fiqih Islam Kontemporer', 'Dr. KH. Ali Mustafa Yaqub', '978-602-009-902', 'Pustaka Firdaus', '2021', 'Jakarta', 450, 'Indonesia', 'Agama', 'REL-002', 4, 5, 0, NULL, 'Pandangan fiqih Islam terhadap isu-isu kontemporer dalam masyarakat modern.', '2025-12-09 14:36:30'),
(33, 'buku', 'aaaa', 'aaaa', 'aaa', '2095', 'aaaa', 4, 'Indonesia', 'Fiksi', '0', 15, 1, 0, NULL, '', '2025-12-11 01:32:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `book_categories`
--

CREATE TABLE `book_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `book_categories`
--

INSERT INTO `book_categories` (`category_id`, `category_name`, `category_code`, `description`, `created_at`) VALUES
(1, 'Fiksi', 'FIK', 'Novel, cerpen, dan karya fiksi lainnya', '2025-12-09 11:06:35'),
(2, 'Non-Fiksi', 'NON', 'Buku berdasarkan fakta dan realitas', '2025-12-09 11:06:35'),
(3, 'Sains', 'SCI', 'Buku ilmu pengetahuan alam', '2025-12-09 11:06:35'),
(4, 'Teknologi', 'TECH', 'Buku tentang teknologi dan komputer', '2025-12-09 11:06:35'),
(5, 'Sejarah', 'HIST', 'Buku sejarah dan biografi', '2025-12-09 11:06:35'),
(6, 'Seni', 'ART', 'Buku seni, musik, dan budaya', '2025-12-09 11:06:35'),
(7, 'Pendidikan', 'EDU', 'Buku pelajaran dan pendidikan', '2025-12-09 11:06:35'),
(8, 'Agama', 'REL', 'Buku keagamaan', '2025-12-09 11:06:35'),
(9, 'Ekonomi', 'ECO', 'Buku ekonomi dan bisnis', '2025-12-09 11:06:35'),
(10, 'Psikologi', 'PSY', 'Buku psikologi dan pengembangan diri', '2025-12-09 11:06:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `book_copies`
--

CREATE TABLE `book_copies` (
  `copy_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `copy_number` varchar(20) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `status` enum('available','borrowed','maintenance','lost','damaged','reserved') DEFAULT 'available',
  `condition` enum('good','fair','poor') DEFAULT 'good',
  `location` varchar(100) DEFAULT 'Perpustakaan',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `member_code` varchar(20) NOT NULL,
  `full_name` varchar(100) GENERATED ALWAYS AS (concat(ifnull(`first_name`,''),' ',ifnull(`last_name`,''))) STORED,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `member_type` enum('student','teacher','public') DEFAULT 'student',
  `member_role` enum('library_member','intern','staff') DEFAULT 'library_member',
  `permissions` text DEFAULT NULL COMMENT 'JSON array of permissions',
  `institution` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `join_date` date NOT NULL,
  `expired_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `members`
--

INSERT INTO `members` (`member_id`, `member_code`, `first_name`, `last_name`, `email`, `phone`, `address`, `birth_date`, `gender`, `member_type`, `member_role`, `permissions`, `institution`, `photo`, `status`, `join_date`, `expired_date`, `created_at`) VALUES
(1, 'MBR001', 'Budi', 'Santoso', 'budi@email.com', '081234567890', 'Jl. Merdeka No. 123, Semarang', '2000-05-15', 'L', 'student', 'library_member', NULL, 'Universitas Diponegoro', NULL, 'active', '2025-01-01', '2026-01-01', '2025-12-09 12:09:22'),
(4, 'MBR000', 'Admin', 'User', 'admin@library.com', '081234567890', 'Perpustakaan', '2025-12-12', 'L', 'teacher', 'library_member', '{\"can_borrow_books\":true,\"can_add_bibliography\":false,\"can_view_catalog\":true,\"can_request_books\":true,\"can_view_reports\":false}', '', NULL, 'active', '2025-01-01', '2026-12-31', '2025-12-09 16:38:47'),
(100, 'MBR004', 'alvah', 'Rabbany', 'alvahrabbany22@gmail.com', '081234567890', 'Jl. salam no 21 RT 1/ rw1', '2025-12-12', 'L', 'student', 'intern', '{\"can_borrow_books\":false,\"can_add_bibliography\":true,\"can_view_catalog\":true,\"can_request_books\":false,\"can_view_reports\":false}', 'AAAA', 'member_1765402548_6939e7b4e3db8.png', 'active', '2025-12-10', '2026-12-10', '2025-12-10 21:35:48'),
(101, 'MBR005', 'aaaaaa', 'aaaaaaaaaa', 'alvahrabba@yahoo.co.id', 'aaaaaaaaaa', 'aaa', '2025-12-12', 'L', 'student', 'library_member', '{\"can_borrow_books\":true,\"can_add_bibliography\":false,\"can_view_catalog\":true,\"can_request_books\":true,\"can_view_reports\":false}', 'aaaaaaaaaaaaa', 'member_1765403481_6939eb59baa3e.png', 'active', '2025-12-10', '2026-12-10', '2025-12-10 21:51:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `members_backup`
--

CREATE TABLE `members_backup` (
  `member_id` int(11) NOT NULL DEFAULT 0,
  `member_code` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `member_type` enum('student','teacher','public') DEFAULT 'student',
  `member_role` enum('library_member','intern','staff') DEFAULT 'library_member',
  `permissions` text DEFAULT NULL COMMENT 'JSON array of permissions',
  `institution` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `join_date` date NOT NULL,
  `expired_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `members_backup`
--

INSERT INTO `members_backup` (`member_id`, `member_code`, `username`, `password`, `full_name`, `first_name`, `last_name`, `email`, `phone`, `address`, `birth_date`, `gender`, `member_type`, `member_role`, `permissions`, `institution`, `photo`, `status`, `join_date`, `expired_date`, `created_at`) VALUES
(1, 'MBR001', 'budi_santoso', '482c811da5d5b4bc6d497ffa98491e38', 'Budi Santoso', 'Budi', 'Santoso', 'budi@email.com', '081234567890', 'Jl. Merdeka No. 123, Semarang', '2000-05-15', 'L', 'student', 'library_member', NULL, 'Universitas Diponegoro', NULL, 'active', '2025-01-01', '2026-01-01', '2025-12-09 12:09:22'),
(2, 'MBR002', 'siti_nurhaliza', '482c811da5d5b4bc6d497ffa98491e38', 'Siti Nurhaliza', 'Siti', 'Nurhaliza', 'siti@email.com', '081234567891', 'Jl. Pahlawan No. 45, Semarang', '1985-08-20', 'P', 'student', 'library_member', NULL, 'SMA Negeri 1 Semarang', NULL, 'active', '2025-01-15', '2026-01-15', '2025-12-09 12:09:22'),
(3, 'MBR003', 'ahmad_zaki', '482c811da5d5b4bc6d497ffa98491e38', 'Ahmad Zaki', 'Ahmad', 'Zaki', 'ahmad@email.com', '081234567892', 'Jl. Veteran No. 78, Semarang', '1990-12-10', 'L', 'teacher', 'library_member', NULL, NULL, NULL, 'inactive', '2025-02-01', '2026-02-01', '2025-12-09 12:09:22'),
(4, 'MBR000', 'member4', '482c811da5d5b4bc6d497ffa98491e38', 'Admin User', 'Admin', 'User', 'admin@library.com', '081234567890', 'Perpustakaan', NULL, NULL, 'teacher', 'library_member', NULL, NULL, NULL, 'active', '2025-01-01', '2026-12-31', '2025-12-09 16:38:47'),
(100, 'MBR004', 'alvah_rabbany', '482c811da5d5b4bc6d497ffa98491e38', 'alvah Rabbany', 'alvah', 'Rabbany', 'alvahrabbany22@gmail.com', '081234567890', 'Jl. salam no 21 RT 1/ rw1', '2025-12-12', 'L', 'student', 'intern', '{\"can_borrow_books\":false,\"can_add_bibliography\":true,\"can_view_catalog\":true,\"can_request_books\":false,\"can_view_reports\":false}', 'AAAA', 'member_1765402548_6939e7b4e3db8.png', 'active', '2025-12-10', '2026-12-10', '2025-12-10 21:35:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `member_roles`
--

CREATE TABLE `member_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_code` varchar(20) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `default_permissions` text DEFAULT NULL COMMENT 'JSON array of default permissions',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `member_roles`
--

INSERT INTO `member_roles` (`role_id`, `role_name`, `role_code`, `display_name`, `description`, `default_permissions`, `created_at`) VALUES
(1, 'Anggota Perpustakaan', 'library_member', 'Anggota Perpustakaan', 'Anggota reguler yang dapat meminjam buku', '{\"can_borrow_books\":true,\"can_add_bibliography\":false,\"can_view_catalog\":true,\"can_request_books\":true,\"can_view_reports\":false}', '2025-12-10 12:00:51'),
(2, 'Anak Magang', 'intern', 'Anak Magang', 'Magang yang fokus pada input bibliografi dan katalog', '{\"can_borrow_books\":false,\"can_add_bibliography\":true,\"can_view_catalog\":true,\"can_request_books\":false,\"can_view_reports\":false}', '2025-12-10 12:00:51'),
(3, 'Staff Perpustakaan', 'staff', 'Staff Perpustakaan', 'Staff yang membantu operasional perpustakaan', '{\"can_borrow_books\":true,\"can_add_bibliography\":true,\"can_view_catalog\":true,\"can_request_books\":true,\"can_view_reports\":true}', '2025-12-10 12:00:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `transaction_code` varchar(20) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `book_condition` enum('good','light_damage','heavy_damage','lost') DEFAULT 'good',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `transaction_code`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`, `fine_amount`, `book_condition`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'TRX001', 1, 1, '2025-12-01', '2025-12-08', NULL, 'borrowed', 0.00, 'good', NULL, NULL, '2025-12-09 12:09:22', '2025-12-09 12:09:22'),
(2, 'TRX002', 1, 2, '2025-11-28', '2025-12-05', NULL, 'returned', 0.00, 'good', NULL, NULL, '2025-12-09 12:09:22', '2025-12-10 22:51:49'),
(3, 'TRX003', 1, 3, '2025-11-25', '2025-12-02', NULL, 'overdue', 7000.00, 'good', NULL, NULL, '2025-12-09 12:09:22', '2025-12-10 22:51:49'),
(4, 'TRX202512097916', 4, 4, '2025-12-09', '2025-12-16', NULL, 'borrowed', 0.00, 'good', NULL, 1, '2025-12-09 16:39:30', '2025-12-09 16:39:30'),
(5, 'TRX202512097998', 4, 16, '2025-12-09', '2025-12-16', NULL, 'borrowed', 0.00, 'good', '', 1, '2025-12-09 17:22:35', '2025-12-09 17:22:35'),
(6, 'TRX202512103602', 4, 28, '2025-12-10', '2025-12-17', '2025-12-10', 'returned', 50000.00, 'heavy_damage', '', 1, '2025-12-09 23:14:50', '2025-12-10 01:40:01'),
(7, 'TRX202512101728', 4, 5, '2025-12-10', '2025-12-17', '2025-12-10', 'returned', 150000.00, 'lost', NULL, 1, '2025-12-10 08:36:02', '2025-12-10 22:55:27'),
(8, 'TRX202512107211', 101, 5, '2025-12-10', '2025-12-17', NULL, 'borrowed', 0.00, 'good', NULL, 1, '2025-12-10 22:54:07', '2025-12-10 22:54:07'),
(9, 'TRX202512101331', 4, 5, '2025-12-10', '2025-12-17', NULL, 'borrowed', 0.00, 'good', NULL, 1, '2025-12-10 22:55:55', '2025-12-10 22:55:55'),
(10, 'TRX202512103883', 100, 5, '2025-12-10', '2025-12-17', NULL, 'borrowed', 0.00, 'good', NULL, 1, '2025-12-10 22:59:12', '2025-12-10 22:59:12'),
(11, 'TRX202512109102', 100, 5, '2025-12-10', '2025-12-17', '2025-12-10', 'returned', 0.00, 'good', NULL, 1, '2025-12-10 22:59:30', '2025-12-10 23:32:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `member_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `username`, `password`, `role`, `member_id`, `created_at`) VALUES
(1, 'Admin User', 'admin@library.com', 'admin', '0192023a7bbd73250516f069df18b500', 'admin', 4, '2025-12-08 11:45:14'),
(2, 'alvah Rabbany', 'alvahrabbany22@gmail.com', 'alvah_rabbany', '25d55ad283aa400af464c76d713c07ad', 'member', 100, '2025-12-10 21:35:48'),
(3, 'aaaaaa aaaaaaaaaa', 'alvahrabba@yahoo.co.id', 'aaaaaa_aaaaaaaaaa', '482c811da5d5b4bc6d497ffa98491e38', 'member', 101, '2025-12-10 21:51:04');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_author` (`author`),
  ADD KEY `idx_isbn` (`isbn`),
  ADD KEY `idx_category` (`category`);

--
-- Indeks untuk tabel `book_categories`
--
ALTER TABLE `book_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_code` (`category_code`);

--
-- Indeks untuk tabel `book_copies`
--
ALTER TABLE `book_copies`
  ADD PRIMARY KEY (`copy_id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_book_id` (`book_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_barcode` (`barcode`);

--
-- Indeks untuk tabel `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `member_code` (`member_code`),
  ADD KEY `idx_members_email` (`email`),
  ADD KEY `idx_member_role` (`member_role`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `member_roles`
--
ALTER TABLE `member_roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_code` (`role_code`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_member` (`member_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `book_categories`
--
ALTER TABLE `book_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `book_copies`
--
ALTER TABLE `book_copies`
  MODIFY `copy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT untuk tabel `member_roles`
--
ALTER TABLE `member_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `book_copies`
--
ALTER TABLE `book_copies`
  ADD CONSTRAINT `book_copies_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `fk_transaction_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
