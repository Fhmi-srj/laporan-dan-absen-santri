<?php
/**
 * History Page - User Attendance History
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDB();
$pageTitle = 'Riwayat Absensi';

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Get attendances for the selected month/year - JOIN with data_induk
$stmt = $pdo->prepare("
    SELECT a.*, di.nama_lengkap, di.kelas, j.name as jadwal_name, j.type as jadwal_type
    FROM attendances a
    JOIN data_induk di ON a.user_id = di.id
    LEFT JOIN jadwal_absens j ON a.jadwal_id = j.id
    WHERE MONTH(a.attendance_date) = ? AND YEAR(a.attendance_date) = ?
    ORDER BY a.attendance_date DESC, a.attendance_time DESC
");
$stmt->execute([$month, $year]);
$attendances = $stmt->fetchAll();

// Group by date
$groupedAttendances = [];
foreach ($attendances as $a) {
    $date = $a['attendance_date'];
    if (!isset($groupedAttendances[$date])) {
        $groupedAttendances[$date] = [];
    }
    $groupedAttendances[$date][] = $a;
}

// Stats for the month
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
        SUM(CASE WHEN status = 'pulang' THEN 1 ELSE 0 END) as pulang
    FROM attendances
    WHERE MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?
");
$statsStmt->execute([$month, $year]);
$stats = $statsStmt->fetch();

$months = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

$dayNames = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<style>
    .date-accordion {
        background: white;
        border-radius: 12px;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .date-header {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        color: white;
        padding: 1rem 1.25rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s;
    }

    .date-header:hover {
        filter: brightness(1.05);
    }

    .date-header .date-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .date-header .date-text {
        font-weight: 600;
    }

    .date-header .date-stats {
        display: flex;
        gap: 0.5rem;
    }

    .date-header .badge-stat {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
    }

    .date-header .chevron {
        transition: transform 0.3s ease;
    }

    .date-header.collapsed .chevron {
        transform: rotate(-90deg);
    }

    .date-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .date-content.show {
        max-height: 2000px;
    }

    .attendance-item {
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
    }

    .attendance-item:last-child {
        border-bottom: none;
    }

    .attendance-item:hover {
        background: #f8fafc;
    }

    .student-info .name {
        font-weight: 600;
        color: #1e293b;
    }

    .student-info .detail {
        font-size: 0.8rem;
        color: #64748b;
    }

    .time-info {
        text-align: right;
    }

    .time-info .time {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
    }
</style>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0"><i class="fas fa-history me-2"></i>Riwayat Absensi</h4>
        <form class="d-flex gap-2">
            <select name="month" class="form-select" style="width: auto;">
                <?php foreach ($months as $m => $name): ?>
                    <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>>
                        <?= $name ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="form-select" style="width: auto;">
                <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
            <button class="btn btn-primary"><i class="fas fa-filter"></i></button>
        </form>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fs-4 fw-bold text-primary">
                    <?= $stats['total'] ?? 0 ?>
                </div>
                <div class="small text-muted">Total Record</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fs-4 fw-bold text-success">
                    <?= $stats['hadir'] ?? 0 ?>
                </div>
                <div class="small text-muted">Hadir</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fs-4 fw-bold text-warning">
                    <?= $stats['terlambat'] ?? 0 ?>
                </div>
                <div class="small text-muted">Terlambat</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fs-4 fw-bold text-info">
                    <?= $stats['pulang'] ?? 0 ?>
                </div>
                <div class="small text-muted">Pulang</div>
            </div>
        </div>
    </div>

    <!-- History List - Collapsible by Day -->
    <?php if (empty($groupedAttendances)): ?>
        <div class="card-custom p-5 text-center">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data absensi</h5>
            <p class="text-muted mb-0">Belum ada catatan absensi untuk bulan
                <?= $months[$month] ?>
                <?= $year ?>
            </p>
        </div>
    <?php else: ?>
        <?php $firstDate = true; ?>
        <?php foreach ($groupedAttendances as $date => $records): ?>
            <?php
            $dayName = date('l', strtotime($date));
            $dayNameId = $dayNames[$dayName] ?? $dayName;
            $dateFormatted = date('d', strtotime($date)) . ' ' . $months[date('m', strtotime($date))] . ' ' . date('Y', strtotime($date));

            // Count stats for this day
            $dayHadir = 0;
            $dayTerlambat = 0;
            foreach ($records as $r) {
                if ($r['status'] === 'hadir')
                    $dayHadir++;
                elseif ($r['status'] === 'terlambat')
                    $dayTerlambat++;
            }
            ?>
            <div class="date-accordion">
                <div class="date-header <?= $firstDate ? '' : 'collapsed' ?>" onclick="toggleAccordion(this)">
                    <div class="date-info">
                        <i class="fas fa-calendar-day"></i>
                        <span class="date-text"><?= $dayNameId ?>, <?= $dateFormatted ?></span>
                        <div class="date-stats">
                            <span class="badge-stat"><i class="fas fa-users me-1"></i><?= count($records) ?> siswa</span>
                            <?php if ($dayHadir > 0): ?>
                                <span class="badge-stat"><i class="fas fa-check me-1"></i><?= $dayHadir ?> hadir</span>
                            <?php endif; ?>
                            <?php if ($dayTerlambat > 0): ?>
                                <span class="badge-stat"><i class="fas fa-clock me-1"></i><?= $dayTerlambat ?> terlambat</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="date-content <?= $firstDate ? 'show' : '' ?>">
                    <?php foreach ($records as $r): ?>
                        <div class="attendance-item">
                            <div class="student-info">
                                <div class="name"><?= e($r['nama_lengkap']) ?></div>
                                <div class="detail">
                                    <?= e($r['kelas']) ?> • <?= e($r['jadwal_name'] ?? 'Absen') ?>
                                </div>
                            </div>
                            <div class="time-info">
                                <div class="time"><?= substr($r['attendance_time'], 0, 5) ?></div>
                                <span
                                    class="badge bg-<?= $r['status'] === 'hadir' ? 'success' : ($r['status'] === 'terlambat' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php $firstDate = false; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    function toggleAccordion(header) {
        const content = header.nextElementSibling;
        const isCollapsed = header.classList.contains('collapsed');

        if (isCollapsed) {
            header.classList.remove('collapsed');
            content.classList.add('show');
        } else {
            header.classList.add('collapsed');
            content.classList.remove('show');
        }
    }
</script>

<?php include __DIR__ . '/include/footer.php'; ?>