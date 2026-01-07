<?php
/**
 * Dashboard - Halaman Utama setelah Login
 * Laporan Santri - PHP Murni
 * Now uses data_induk table directly
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$pdo = getDB();
$today = date('Y-m-d');
$pageTitle = 'Dashboard';

// Statistics - now use data_induk
$siswaCount = $pdo->query("SELECT COUNT(*) FROM data_induk WHERE deleted_at IS NULL")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL")->fetchColumn();

// Aktivitas hari ini
$stmtAktivitas = $pdo->prepare("SELECT COUNT(*) FROM catatan_aktivitas WHERE DATE(tanggal) = ?");
$stmtAktivitas->execute([$today]);
$aktivitasToday = $stmtAktivitas->fetchColumn();

// Attendance hari ini
$stmtPresent = $pdo->prepare("
    SELECT COUNT(DISTINCT a.user_id) FROM attendances a
    WHERE a.attendance_date = ?
");
$stmtPresent->execute([$today]);
$presentToday = $stmtPresent->fetchColumn();

$absentToday = $siswaCount - $presentToday;

// Chart data - 7 hari terakhir
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN a.status != 'terlambat' THEN 1 END) as hadir,
            COUNT(CASE WHEN a.status = 'terlambat' THEN 1 END) as terlambat
        FROM attendances a
        WHERE a.attendance_date = ?
    ");
    $stmt->execute([$date]);
    $row = $stmt->fetch();
    $chartData[] = [
        'date' => date('d M', strtotime($date)),
        'hadir' => (int) $row['hadir'],
        'terlambat' => (int) $row['terlambat']
    ];
}

// Aktivitas terbaru - JOIN dengan data_induk
$stmtRecent = $pdo->prepare("
    SELECT ca.*, di.nama_lengkap, di.kelas
    FROM catatan_aktivitas ca 
    JOIN data_induk di ON ca.siswa_id = di.id 
    ORDER BY ca.created_at DESC LIMIT 5
");
$stmtRecent->execute();
$recentAktivitas = $stmtRecent->fetchAll();

// Siswa terlambat hari ini - JOIN dengan data_induk
$stmtLate = $pdo->prepare("
    SELECT a.*, di.nama_lengkap, di.kelas
    FROM attendances a 
    JOIN data_induk di ON a.user_id = di.id 
    WHERE a.attendance_date = ? AND a.status = 'terlambat'
    ORDER BY a.attendance_time DESC
");
$stmtLate->execute([$today]);
$lateSiswa = $stmtLate->fetchAll();

$flash = getFlash();

// Extra scripts for this page
$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>';
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show"
            role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4">Dashboard</h4>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value"><?= $siswaCount ?></div>
                <div class="stat-label">Total Santri</div>
                <div class="stat-icon mx-auto mt-2" style="background: #dbeafe; color: #3b82f6;">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value"><?= $presentToday ?></div>
                <div class="stat-label">Hadir Hari Ini</div>
                <div class="stat-icon mx-auto mt-2" style="background: #dcfce7; color: #22c55e;">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value"><?= $aktivitasToday ?></div>
                <div class="stat-label">Aktivitas Hari Ini</div>
                <div class="stat-icon mx-auto mt-2" style="background: #fef3c7; color: #f59e0b;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-value"><?= $userCount ?></div>
                <div class="stat-label">Total User</div>
                <div class="stat-icon mx-auto mt-2" style="background: #dbeafe; color: #3b82f6;">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chart -->
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-chart-bar me-2 text-primary"></i> Grafik Kehadiran 7 Hari Terakhir
                </div>
                <div class="p-3">
                    <div id="attendanceChart"></div>
                </div>
            </div>
        </div>

        <!-- Santri Terlambat -->
        <div class="col-lg-4">
            <div class="card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-clock me-2 text-warning"></i> Terlambat Hari Ini
                </div>
                <div class="p-3">
                    <?php if (count($lateSiswa) > 0): ?>
                        <?php foreach ($lateSiswa as $late): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-user text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= e($late['nama_lengkap']) ?></div>
                                    <small class="text-muted">
                                        <?= e($late['kelas']) ?> - <?= date('H:i', strtotime($late['attendance_time'])) ?>
                                    </small>
                                </div>
                                <span class="badge bg-warning"><?= $late['minutes_late'] ?> menit</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3" style="color: #22c55e;"></i>
                            <p class="mb-0">Tidak ada siswa terlambat</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Aktivitas -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card-custom">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history me-2 text-primary"></i> Aktivitas Terbaru</span>
                    <a href="aktivitas.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-sortable">
                        <thead class="bg-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Santri</th>
                                <th>Kategori</th>
                                <th>Judul</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentAktivitas) > 0): ?>
                                <?php foreach ($recentAktivitas as $akt): ?>
                                    <tr>
                                        <td><?= date('d/m H:i', strtotime($akt['tanggal'])) ?></td>
                                        <td>
                                            <strong><?= e($akt['nama_lengkap']) ?></strong><br>
                                            <small class="text-muted"><?= e($akt['kelas']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= e($akt['kategori']) ?></span></td>
                                        <td><?= e($akt['judul'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var chartData = <?= json_encode($chartData) ?>;
    var options = {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [
            { name: 'Hadir', data: chartData.map(d => d.hadir) },
            { name: 'Terlambat', data: chartData.map(d => d.terlambat) }
        ],
        xaxis: { categories: chartData.map(d => d.date) },
        colors: ['#22c55e', '#f59e0b'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top' }
    };
    new ApexCharts(document.querySelector("#attendanceChart"), options).render();
</script>
<?php include __DIR__ . '/include/footer.php'; ?>