<?php
/**
 * Admin: Attendance Report
 * Report with filters and export
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Laporan Absensi';

// Filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$filterSiswa = $_GET['siswa_id'] ?? '';
$filterJadwal = $_GET['jadwal_id'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Get filter options (enriched with SPMB)
$siswaList = $pdo->query("SELECT * FROM siswa ORDER BY id ASC")->fetchAll();
enrichSiswaWithSPMB($siswaList);
$jadwalList = $pdo->query("SELECT * FROM jadwal_absens ORDER BY start_time")->fetchAll();

// Build query
$sql = "
    SELECT a.*, s.nomor_induk, s.kelas, s.pendaftaran_id, j.name as jadwal_name
    FROM attendances a
    JOIN siswa s ON a.user_id = s.id
    LEFT JOIN jadwal_absens j ON a.jadwal_id = j.id
    WHERE a.attendance_date BETWEEN ? AND ?
";
$params = [$dateFrom, $dateTo];

if ($filterSiswa) {
    $sql .= " AND a.user_id = ?";
    $params[] = $filterSiswa;
}
if ($filterJadwal) {
    $sql .= " AND a.jadwal_id = ?";
    $params[] = $filterJadwal;
}
if ($filterStatus) {
    $sql .= " AND a.status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY a.attendance_date DESC, a.attendance_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendances = $stmt->fetchAll();

// Enrich with SPMB data
$pendaftaranIds = array_column($attendances, 'pendaftaran_id');
$pendaftaranData = getPendaftaranData($pendaftaranIds);
foreach ($attendances as &$a) {
    $p = $pendaftaranData[$a['pendaftaran_id']] ?? [];
    $a['nama_lengkap'] = $p['nama'] ?? '-';
}
unset($a);

// Statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
        SUM(CASE WHEN status IN ('absen', 'sakit', 'izin') THEN 1 ELSE 0 END) as tidak_hadir
    FROM attendances a
    WHERE a.attendance_date BETWEEN ? AND ?
";
$statsParams = [$dateFrom, $dateTo];
if ($filterSiswa) {
    $statsQuery .= " AND a.user_id = ?";
    $statsParams[] = $filterSiswa;
}
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute($statsParams);
$stats = $statsStmt->fetch();

// Handle Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan_absensi_' . $dateFrom . '_' . $dateTo . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Tanggal', 'Waktu', 'NIS', 'Nama Siswa', 'Kelas', 'Jadwal', 'Status', 'Terlambat (menit)', 'Catatan']);

    foreach ($attendances as $a) {
        fputcsv($output, [
            $a['attendance_date'],
            $a['attendance_time'],
            $a['nomor_induk'],
            $a['nama_lengkap'],
            $a['kelas'],
            $a['jadwal_name'] ?? '-',
            $a['status'],
            $a['minutes_late'] ?? 0,
            $a['notes'] ?? ''
        ]);
    }
    fclose($output);
    exit;
}
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2"></i>Laporan Absensi</h4>
        <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-success">
            <i class="fas fa-download me-1"></i> Export CSV
        </a>
    </div>

    <!-- Filters -->
    <div class="card-custom p-3 mb-4">
        <form class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Siswa</label>
                <select name="siswa_id" class="form-select">
                    <option value="">Semua Siswa</option>
                    <?php foreach ($siswaList as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $filterSiswa == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['nama_lengkap']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Jadwal</label>
                <select name="jadwal_id" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($jadwalList as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= $filterJadwal == $j['id'] ? 'selected' : '' ?>>
                            <?= e($j['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="hadir" <?= $filterStatus === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                    <option value="terlambat" <?= $filterStatus === 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                    <option value="izin" <?= $filterStatus === 'izin' ? 'selected' : '' ?>>Izin</option>
                    <option value="sakit" <?= $filterStatus === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                    <option value="absen" <?= $filterStatus === 'absen' ? 'selected' : '' ?>>Absen</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center bg-primary bg-opacity-10">
                <div class="fs-4 fw-bold text-primary">
                    <?= $stats['total'] ?? 0 ?>
                </div>
                <div class="small text-muted">Total Record</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center bg-success bg-opacity-10">
                <div class="fs-4 fw-bold text-success">
                    <?= $stats['hadir'] ?? 0 ?>
                </div>
                <div class="small text-muted">Hadir</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center bg-warning bg-opacity-10">
                <div class="fs-4 fw-bold text-warning">
                    <?= $stats['terlambat'] ?? 0 ?>
                </div>
                <div class="small text-muted">Terlambat</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center bg-danger bg-opacity-10">
                <div class="fs-4 fw-bold text-danger">
                    <?= $stats['tidak_hadir'] ?? 0 ?>
                </div>
                <div class="small text-muted">Tidak Hadir</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sortable">
                <thead class="bg-light">
                    <tr>
                        <th>Tanggal</th>
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
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendances as $a): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        <?= date('d/m/Y', strtotime($a['attendance_date'])) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= substr($a['attendance_time'], 0, 5) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <?= e($a['nama_lengkap']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= e($a['kelas']) ?> |
                                        <?= e($a['nomor_induk']) ?>
                                    </small>
                                </td>
                                <td><span class="badge bg-secondary">
                                        <?= e($a['jadwal_name'] ?? '-') ?>
                                    </span></td>
                                <td>
                                    <span
                                        class="badge bg-<?= $a['status'] === 'hadir' ? 'success' : ($a['status'] === 'terlambat' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($a['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $a['minutes_late'] ? $a['minutes_late'] . ' menit' : '-' ?>
                                </td>
                                <td>
                                    <?= e($a['notes'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>