<?php
require_once '../api/config.php';
requireLogin();

$conn = getConnection();

// Get statistics
$stats = [];

// Total pendaftar
$result = $conn->query("SELECT COUNT(*) as total FROM pendaftaran");
$stats['total'] = $result->fetch_assoc()['total'];

// Per lembaga
$result = $conn->query("SELECT lembaga, COUNT(*) as total FROM pendaftaran GROUP BY lembaga");
$stats['per_lembaga'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['per_lembaga'][$row['lembaga']] = $row['total'];
}

// Per status
$result = $conn->query("SELECT status, COUNT(*) as total FROM pendaftaran GROUP BY status");
$stats['per_status'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['per_status'][$row['status']] = $row['total'];
}

// Monthly registration data (last 6 months)
$monthlyData = [];
$result = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total 
    FROM pendaftaran 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month ASC
");
while ($row = $result->fetch_assoc()) {
    $monthlyData[$row['month']] = $row['total'];
}

// Latest registrations
$result = $conn->query("SELECT id, nama, lembaga, jenis_kelamin, status, created_at FROM pendaftaran ORDER BY created_at DESC LIMIT 5");
$latest = [];
while ($row = $result->fetch_assoc()) {
    $latest[] = $row;
}

// Recent activity log
$activityLog = [];
$result = $conn->query("
    SELECT al.*, a.nama as admin_nama 
    FROM activity_log al 
    LEFT JOIN admin a ON al.admin_id = a.id 
    ORDER BY al.created_at DESC 
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activityLog[] = $row;
    }
}

// Gender distribution
$genderStats = ['L' => 0, 'P' => 0];
$result = $conn->query("SELECT jenis_kelamin, COUNT(*) as total FROM pendaftaran GROUP BY jenis_kelamin");
while ($row = $result->fetch_assoc()) {
    $genderStats[$row['jenis_kelamin']] = $row['total'];
}

// Document completion stats
$docFields = ['file_kk', 'file_ktp_ortu', 'file_akta', 'file_ijazah', 'file_sertifikat'];
$docStats = [];
foreach ($docFields as $field) {
    $result = $conn->query("SELECT COUNT(*) as total FROM pendaftaran WHERE $field IS NOT NULL AND $field != ''");
    $docStats[$field] = $result->fetch_assoc()['total'];
}

// Sumber info stats
$sumberInfoStats = [];
$result = $conn->query("SELECT sumber_info, COUNT(*) as total FROM pendaftaran WHERE sumber_info IS NOT NULL AND sumber_info != '' GROUP BY sumber_info ORDER BY total DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $sumberInfoStats[$row['sumber_info']] = $row['total'];
}

// Daily registrations (last 14 days)
$dailyData = [];
$result = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as total 
    FROM pendaftaran 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(created_at) 
    ORDER BY date ASC
");
while ($row = $result->fetch_assoc()) {
    $dailyData[$row['date']] = $row['total'];
}

$conn->close();

// Page config
$pageTitle = 'Dashboard - Admin SPMB';
$currentPage = 'dashboard';

// Prepare chart data
$months = [];
$monthLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = $monthlyData[$month] ?? 0;
    $monthLabels[] = date('M Y', strtotime("-$i months"));
}
?>
<?php include 'includes/header.php'; ?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
            <p class="text-gray-500 text-sm">Selamat datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?>!</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-primary text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['total'] ?></p>
                        <p class="text-xs text-gray-500">Total Pendaftar</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-school text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['per_lembaga']['SMP NU BP'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">SMP NU BP</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-mosque text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['per_lembaga']['MA ALHIKAM'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">MA ALHIKAM</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['per_status']['pending'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Menunggu</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-mars text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $genderStats['L'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Laki-laki</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-venus text-pink-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $genderStats['P'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Perempuan</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['per_status']['verified'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Terverifikasi</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['per_status']['rejected'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Ditolak</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Monthly Chart -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i
                        class="fas fa-chart-bar text-primary mr-2"></i>Pendaftaran 6 Bulan Terakhir</h3>
                <div class="h-64">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Lembaga Chart -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i
                        class="fas fa-chart-pie text-primary mr-2"></i>Distribusi per Lembaga</h3>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="lembagaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Second Row Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Document Completion -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-folder-open text-primary mr-2"></i>Kelengkapan Dokumen</h3>
                <div class="space-y-3">
                    <?php 
                    $docLabels = [
                        'file_kk' => 'Kartu Keluarga',
                        'file_ktp_ortu' => 'KTP Orang Tua',
                        'file_akta' => 'Akta Kelahiran',
                        'file_ijazah' => 'Ijazah',
                        'file_sertifikat' => 'Sertifikat'
                    ];
                    foreach ($docLabels as $field => $label):
                        $count = $docStats[$field] ?? 0;
                        $total = $stats['total'] ?: 1;
                        $percentage = round(($count / $total) * 100);
                    ?>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600"><?= $label ?></span>
                            <span class="text-gray-500"><?= $count ?>/<?= $stats['total'] ?></span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full transition-all" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Gender Chart -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-venus-mars text-primary mr-2"></i>Distribusi Gender</h3>
                <div class="h-48 flex items-center justify-center">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

            <!-- Source Info Chart -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-bullhorn text-primary mr-2"></i>Sumber Informasi</h3>
                <div class="h-48">
                    <canvas id="sumberChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Two Column: Recent Registrations & Activity Log -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Registrations -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Pendaftaran Terbaru</h3>
                    <a href="pendaftaran.php" class="text-primary text-sm hover:underline">Lihat Semua</a>
                </div>

                <?php if (empty($latest)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>Belum ada pendaftaran</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($latest as $row): ?>
                            <div class="p-3 hover:bg-gray-50 flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-sm text-gray-800"><?= htmlspecialchars($row['nama']) ?></p>
                                    <p class="text-xs text-gray-500"><?= $row['lembaga'] ?> •
                                        <?= $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?> •
                                        <?= date('d M', strtotime($row['created_at'])) ?>
                                    </p>
                                </div>
                                <?php
                                $statusClass = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'verified' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ][$row['status']];
                                $statusText = ['pending' => 'Menunggu', 'verified' => 'Verif', 'rejected' => 'Tolak'][$row['status']];
                                ?>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Activity Log -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Aktivitas Terbaru</h3>
                    <a href="aktivitas.php" class="text-primary text-sm hover:underline">Lihat Semua</a>
                </div>

                <?php if (empty($activityLog)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-history text-4xl mb-3"></i>
                        <p>Belum ada aktivitas tercatat</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($activityLog as $log): ?>
                            <div class="p-3 hover:bg-gray-50">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-medium"><?= htmlspecialchars($log['action']) ?></span>
                                    <span
                                        class="text-xs text-gray-400"><?= date('d M H:i', strtotime($log['created_at'])) ?></span>
                                </div>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($log['description']) ?></p>
                                <p class="text-xs text-gray-400">oleh <?= htmlspecialchars($log['admin_nama'] ?? 'System') ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php
// Prepare daily data for chart
$dailyLabels = [];
$dailyCounts = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dailyLabels[] = date('d M', strtotime($date));
    $dailyCounts[] = $dailyData[$date] ?? 0;
}
?>

<script>
    // Monthly Registration Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($monthLabels) ?>,
            datasets: [{
                label: 'Pendaftaran',
                data: <?= json_encode($months) ?>,
                backgroundColor: 'rgba(230, 126, 34, 0.8)',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Lembaga Distribution Chart
    const lembagaCtx = document.getElementById('lembagaChart').getContext('2d');
    new Chart(lembagaCtx, {
        type: 'doughnut',
        data: {
            labels: ['SMP NU BP', 'MA ALHIKAM'],
            datasets: [{
                data: [<?= $stats['per_lembaga']['SMP NU BP'] ?? 0 ?>, <?= $stats['per_lembaga']['MA ALHIKAM'] ?? 0 ?>],
                backgroundColor: ['#3B82F6', '#10B981'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Gender Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Laki-laki', 'Perempuan'],
            datasets: [{
                data: [<?= $genderStats['L'] ?? 0 ?>, <?= $genderStats['P'] ?? 0 ?>],
                backgroundColor: ['#3B82F6', '#EC4899'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Sumber Info Chart
    const sumberCtx = document.getElementById('sumberChart').getContext('2d');
    new Chart(sumberCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($sumberInfoStats)) ?>,
            datasets: [{
                label: 'Jumlah',
                data: <?= json_encode(array_values($sumberInfoStats)) ?>,
                backgroundColor: [
                    'rgba(230, 126, 34, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>
</body>

</html>