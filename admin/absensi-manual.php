<?php
/**
 * Admin: Manual Attendance
 * Input attendance manually
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Absensi Manual';

// Get siswa list from data_induk
$siswaList = $pdo->query("SELECT id, nama_lengkap, kelas FROM data_induk WHERE deleted_at IS NULL ORDER BY nama_lengkap ASC")->fetchAll();

// Get jadwal list
$jadwalList = $pdo->query("SELECT * FROM jadwal_absens WHERE deleted_at IS NULL ORDER BY start_time")->fetchAll();

// Handle Store
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'store') {
        $siswaId = $_POST['siswa_id'];
        $jadwalId = $_POST['jadwal_id'];
        $date = $_POST['attendance_date'];
        $time = $_POST['attendance_time'];
        $status = $_POST['status'];
        $notes = $_POST['notes'] ?? '';

        // Check if already exists
        $check = $pdo->prepare("SELECT id FROM attendances WHERE user_id = ? AND attendance_date = ? AND jadwal_id = ?");
        $check->execute([$siswaId, $date, $jadwalId]);
        if ($check->fetch()) {
            // Update existing
            $stmt = $pdo->prepare("UPDATE attendances SET attendance_time = ?, status = ?, notes = ?, updated_at = NOW() WHERE user_id = ? AND attendance_date = ? AND jadwal_id = ?");
            $stmt->execute([$time, $status, $notes, $siswaId, $date, $jadwalId]);
            redirectWith('absensi-manual.php', 'success', 'Data absensi berhasil diperbarui!');
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO attendances (user_id, jadwal_id, attendance_date, attendance_time, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$siswaId, $jadwalId, $date, $time, $status, $notes]);
            redirectWith('absensi-manual.php', 'success', 'Data absensi berhasil ditambahkan!');
        }
    }
    if ($_POST['action'] === 'delete') {
        // Soft delete - move to trash
        $pdo->prepare("UPDATE attendances SET deleted_at = NOW(), deleted_by = ? WHERE id = ?")->execute([$user['id'], $_POST['id']]);
        logActivity('DELETE', 'attendances', $_POST['id'], null, null, null, 'Hapus absensi ke trash');
        redirectWith('absensi-manual.php', 'success', 'Data absensi dipindahkan ke trash!');
    }
}

// Get recent manual entries - JOIN with data_induk
$recentAttendances = $pdo->query("
    SELECT a.*, di.nama_lengkap, di.kelas, j.name as jadwal_name
    FROM attendances a
    JOIN data_induk di ON a.user_id = di.id AND di.deleted_at IS NULL
    LEFT JOIN jadwal_absens j ON a.jadwal_id = j.id
    WHERE a.deleted_at IS NULL
    ORDER BY a.created_at DESC
    LIMIT 20
")->fetchAll();
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4"><i class="fas fa-edit me-2"></i>Absensi Manual</h4>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-5">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4">Input Absensi</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="store">

                    <div class="mb-3">
                        <label class="form-label">Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= e($s['nama_lengkap']) ?> (
                                    <?= e($s['kelas']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jadwal <span class="text-danger">*</span></label>
                        <select name="jadwal_id" class="form-select" required>
                            <option value="">-- Pilih Jadwal --</option>
                            <?php foreach ($jadwalList as $j): ?>
                                <option value="<?= $j['id'] ?>">
                                    <?= e($j['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="attendance_date" class="form-control" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Waktu <span class="text-danger">*</span></label>
                            <input type="time" name="attendance_time" class="form-control" value="<?= date('H:i') ?>"
                                required>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="absen">Absen/Alpha</option>
                            <option value="pulang">Pulang</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Opsional..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i> Simpan Absensi
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent List -->
        <div class="col-lg-7">
            <div class="card-custom">
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold mb-0">Data Terbaru</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-sortable">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAttendances as $a): ?>
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
                                        <div>
                                            <?= e($a['nama_lengkap']) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= e($a['kelas']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= e($a['jadwal_name'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-<?= $a['status'] === 'hadir' ? 'success' : ($a['status'] === 'terlambat' ? 'warning' : ($a['status'] === 'absen' ? 'danger' : 'secondary')) ?>">
                                            <?= ucfirst($a['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline form-delete-attendance">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-delete-attendance"
                                                data-nama="<?= e($a['nama_lengkap']) ?>"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-attendance').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                const nama = this.dataset.nama || 'ini';

                Swal.fire({
                    title: 'Hapus Data Absensi?',
                    html: `Data absensi <strong>${nama}</strong> akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>