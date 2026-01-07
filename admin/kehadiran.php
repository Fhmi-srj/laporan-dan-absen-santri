<?php
/**
 * Admin: Attendances (Data Absensi)
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Data Absensi';

// Filters
$filterDate = $_GET['date'] ?? date('Y-m-d');
$filterJadwal = $_GET['jadwal'] ?? '';

// Get jadwal options
$jadwalList = $pdo->query("SELECT * FROM jadwal_absens WHERE deleted_at IS NULL ORDER BY start_time")->fetchAll();

// Build query - JOIN with data_induk
$sql = "
    SELECT a.*, di.nama_lengkap, di.nisn as nomor_induk, di.kelas, j.name as jadwal_name, j.type as jadwal_type
    FROM attendances a
    JOIN data_induk di ON a.user_id = di.id AND di.deleted_at IS NULL
    LEFT JOIN jadwal_absens j ON a.jadwal_id = j.id
    WHERE a.deleted_at IS NULL AND a.attendance_date = ?
";
$params = [$filterDate];

if ($filterJadwal) {
    $sql .= " AND a.jadwal_id = ?";
    $params[] = $filterJadwal;
}

$sql .= " ORDER BY a.attendance_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendances = $stmt->fetchAll();

// Stats
$totalHadir = count(array_filter($attendances, fn($a) => $a['status'] === 'hadir'));
$totalTerlambat = count(array_filter($attendances, fn($a) => $a['status'] === 'terlambat'));
$totalAbsen = count(array_filter($attendances, fn($a) => $a['status'] === 'absen'));
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<style>
    .stat-mini {
        padding: 1rem;
        border-radius: 12px;
        text-align: center;
    }
</style>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4"><i class="fas fa-calendar-check me-2"></i>Data Absensi</h4>

    <!-- Filters -->
    <div class="card-custom p-3 mb-4">
        <form class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Tanggal</label>
                <input type="date" name="date" class="form-control" value="<?= $filterDate ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Jadwal</label>
                <select name="jadwal" class="form-select">
                    <option value="">Semua Jadwal</option>
                    <?php foreach ($jadwalList as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= $filterJadwal == $j['id'] ? 'selected' : '' ?>>
                            <?= e($j['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-mini bg-primary bg-opacity-10">
                <div class="fs-3 fw-bold text-primary"><?= count($attendances) ?></div>
                <div class="small text-muted">Total Absensi</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini bg-success bg-opacity-10">
                <div class="fs-3 fw-bold text-success"><?= $totalHadir ?></div>
                <div class="small text-muted">Hadir</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini bg-warning bg-opacity-10">
                <div class="fs-3 fw-bold text-warning"><?= $totalTerlambat ?></div>
                <div class="small text-muted">Terlambat</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini bg-danger bg-opacity-10">
                <div class="fs-3 fw-bold text-danger"><?= $totalAbsen ?></div>
                <div class="small text-muted">Absen</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sortable">
                <thead class="bg-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Siswa</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Terlambat</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendances)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data absensi</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendances as $a): ?>
                            <tr>
                                <td><strong><?= date('H:i', strtotime($a['attendance_time'])) ?></strong></td>
                                <td>
                                    <div class="fw-semibold"><?= e($a['nama_lengkap']) ?></div>
                                    <small class="text-muted">
                                        <?= e($a['kelas']) ?> - <?= e($a['nomor_induk']) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= e($a['jadwal_name'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($a['status'] === 'terlambat'): ?>
                                        <span class="badge bg-warning">Terlambat</span>
                                    <?php elseif ($a['status'] === 'hadir'): ?>
                                        <span class="badge bg-success">Hadir</span>
                                    <?php elseif ($a['status'] === 'absen'): ?>
                                        <span class="badge bg-danger">Absen</span>
                                    <?php elseif ($a['status'] === 'izin'): ?>
                                        <span class="badge bg-info">Izin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= e($a['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $a['minutes_late'] ? $a['minutes_late'] . ' menit' : '-' ?></td>
                                <td><?= e($a['notes'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>