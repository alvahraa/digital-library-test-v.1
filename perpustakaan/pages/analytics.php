<?php
require_once __DIR__ . '/../includes/config.php';

// Cek login dan role admin
if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['error'] = "Anda harus login sebagai admin untuk mengakses Analytics.";
    redirect('../index.php');
}

$fullname = $_SESSION['fullname'] ?? 'Admin';
$role = ucfirst($_SESSION['role'] ?? 'Admin');

// ==========================================
// SUMMARY CARDS
// ==========================================

// Total buku
$total_books = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM books");
if ($res && $row = $res->fetch_assoc()) {
    $total_books = (int)$row['total'];
}

// Total anggota aktif
$total_members = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM members WHERE status = 'active'");
if ($res && $row = $res->fetch_assoc()) {
    $total_members = (int)$row['total'];
}

// Total peminjaman
$total_borrows = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM transactions");
if ($res && $row = $res->fetch_assoc()) {
    $total_borrows = (int)$row['total'];
}

// Total terlambat (sedang dipinjam & overdue)
$total_overdue = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM transactions WHERE status = 'borrowed' AND due_date < CURDATE()");
if ($res && $row = $res->fetch_assoc()) {
    $total_overdue = (int)$row['total'];
}

// ==========================================
// 6-MONTH BORROWING TRENDS
// ==========================================

$trend_labels = [];
$trend_values = [];

$trend_sql = "
    SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS ym, COUNT(*) AS total
    FROM transactions
    WHERE borrow_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY ym
    ORDER BY ym
";

$trend_res = $conn->query($trend_sql);
$trend_data = [];
if ($trend_res) {
    while ($row = $trend_res->fetch_assoc()) {
        $trend_data[$row['ym']] = (int)$row['total'];
    }
}

// Build last 6 months including current
$period = new DatePeriod(
    (new DateTime('first day of -5 month')),
    new DateInterval('P1M'),
    (new DateTime('first day of next month'))
);

foreach ($period as $dt) {
    $ym = $dt->format('Y-m');
    $trend_labels[] = $dt->format('M Y');
    $trend_values[] = $trend_data[$ym] ?? 0;
}

// ==========================================
// CATEGORY DISTRIBUTION
// ==========================================

$cat_labels = [];
$cat_values = [];

$cat_sql = "
    SELECT category, COUNT(*) AS total
    FROM books
    GROUP BY category
    ORDER BY total DESC
";
$cat_res = $conn->query($cat_sql);
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $label = $row['category'] ?: 'Tidak diketahui';
        $cat_labels[] = $label;
        $cat_values[] = (int)$row['total'];
    }
}

// ==========================================
// TOP 5 ACTIVE BORROWERS (All Types)
// ==========================================

$top_students = [];
$top_sql = "
    SELECT 
        m.member_code,
        m.full_name,
        m.member_type,
        COUNT(t.transaction_id) AS total_borrows
    FROM transactions t
    JOIN members m ON t.member_id = m.member_id
    WHERE m.status = 'active'
    GROUP BY t.member_id
    ORDER BY total_borrows DESC
    LIMIT 5
";
$top_res = $conn->query($top_sql);
if ($top_res) {
    while ($row = $top_res->fetch_assoc()) {
        $top_students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - I Love Swiss Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<body class="analytics-body">
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
            <a href="../public_library/index.php" target="_blank" class="btn-logout" style="background: #F875AA; margin-right: 10px;">Digital Library</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <section class="analytics-hero">
        <div class="analytics-hero-overlay"></div>
        <div class="analytics-hero-content">
            <h1 class="analytics-title">Library Analytics</h1>
            <p class="analytics-subtitle">Insight untuk pengambilan keputusan yang lebih baik</p>
        </div>
    </section>

    <div class="analytics-container">
        <!-- Summary Cards -->
        <div class="analytics-summary-grid">
            <div class="glass-card summary-card">
                <div class="summary-label">Total Buku</div>
                <div class="summary-number"><?php echo number_format($total_books); ?></div>
            </div>
            <div class="glass-card summary-card">
                <div class="summary-label">Anggota Aktif</div>
                <div class="summary-number"><?php echo number_format($total_members); ?></div>
            </div>
            <div class="glass-card summary-card">
                <div class="summary-label">Total Peminjaman</div>
                <div class="summary-number"><?php echo number_format($total_borrows); ?></div>
            </div>
            <div class="glass-card summary-card">
                <div class="summary-label">Sedang Terlambat</div>
                <div class="summary-number"><?php echo number_format($total_overdue); ?></div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="analytics-charts-grid">
            <div class="glass-card chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Tren Peminjaman 6 Bulan Terakhir</h3>
                </div>
                <canvas id="borrowTrendChart"></canvas>
            </div>
            <div class="glass-card chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Distribusi Kategori Buku</h3>
                </div>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Top Students -->
        <div class="glass-card leaderboard-card">
            <div class="leaderboard-header">
                <h3 class="chart-title">🏆 Top 5 Peminjam Paling Aktif</h3>
            </div>
            <div class="leaderboard-table-wrapper">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Member ID</th>
                            <th>Nama</th>
                            <th>Total Peminjaman</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($top_students) > 0): ?>
                            <?php foreach ($top_students as $index => $student): ?>
                                <tr>
                                    <td>
                                        <span style="font-weight: 700; color: #F875AA;">
                                            <?php 
                                            $medal = ['🥇', '🥈', '🥉'];
                                            echo isset($medal[$index]) ? $medal[$index] : '#' . ($index + 1); 
                                            ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($student['member_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><strong style="color: #F875AA;"><?php echo (int)$student['total_borrows']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 40px; color:#999;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">📚</div>
                                    <div>Belum ada data aktivitas peminjam.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const trendLabels = <?php echo json_encode($trend_labels); ?>;
        const trendData = <?php echo json_encode($trend_values); ?>;
        const catLabels = <?php echo json_encode($cat_labels); ?>;
        const catData = <?php echo json_encode($cat_values); ?>;

        // Line Chart - Borrowing Trends
        const trendCtx = document.getElementById('borrowTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: trendData,
                    borderColor: '#F875AA',
                    backgroundColor: 'rgba(248, 117, 170, 0.15)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#F875AA'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });

        // Doughnut Chart - Category Distribution
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const pastelColors = [
            '#F875AA',
            '#A78BFA',
            '#34D399',
            '#60A5FA',
            '#FBBF24',
            '#F97316',
            '#EC4899',
            '#22D3EE'
        ];

        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: pastelColors,
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '60%'
            }
        });
    </script>
</body>
</html>




