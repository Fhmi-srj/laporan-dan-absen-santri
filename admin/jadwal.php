<?php
/**
 * Admin: Jadwal Absen
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Jadwal Absen';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
        // Soft delete - move to trash
        $pdo->prepare("UPDATE jadwal_absens SET deleted_at = NOW(), deleted_by = ? WHERE id = ?")->execute([$user['id'], $_POST['id']]);
        logActivity('DELETE', 'jadwal_absens', $_POST['id'], null, null, null, 'Hapus jadwal ke trash');
        redirectWith('jadwal.php', 'success', 'Jadwal dipindahkan ke trash!');
    }
    if ($_POST['action'] === 'store') {
        $stmt = $pdo->prepare("INSERT INTO jadwal_absens (name, type, start_time, scheduled_time, end_time, late_tolerance_minutes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $_POST['name'],
            $_POST['type'],
            $_POST['start_time'],
            $_POST['scheduled_time'],
            $_POST['end_time'],
            $_POST['late_tolerance_minutes']
        ]);
        redirectWith('jadwal.php', 'success', 'Jadwal berhasil ditambahkan!');
    }
    if ($_POST['action'] === 'update' && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE jadwal_absens SET name=?, type=?, start_time=?, scheduled_time=?, end_time=?, late_tolerance_minutes=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['type'],
            $_POST['start_time'],
            $_POST['scheduled_time'],
            $_POST['end_time'],
            $_POST['late_tolerance_minutes'],
            $_POST['id']
        ]);
        redirectWith('jadwal.php', 'success', 'Jadwal berhasil diperbarui!');
    }
}

$jadwalList = $pdo->query("SELECT * FROM jadwal_absens WHERE deleted_at IS NULL ORDER BY start_time ASC")->fetchAll();
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0"><i class="fas fa-clock me-2"></i>Jadwal Absen</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalJadwal" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i> Tambah Jadwal
        </button>
    </div>

    <div class="row g-4">
        <?php foreach ($jadwalList as $j): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><?= e($j['name']) ?></h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="editJadwal(<?= htmlspecialchars(json_encode($j)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" onsubmit="return confirm('Hapus jadwal ini?')" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="row text-center g-2 mb-3">
                        <div class="col-4">
                            <small class="text-muted d-block">Mulai</small>
                            <strong class="text-success"><?= substr($j['start_time'], 0, 5) ?></strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Tepat</small>
                            <strong class="text-primary"><?= substr($j['scheduled_time'], 0, 5) ?></strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Tutup</small>
                            <strong class="text-danger"><?= substr($j['end_time'], 0, 5) ?></strong>
                        </div>
                    </div>
                    <div class="text-center border-top pt-3">
                        <small class="text-muted">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Toleransi: <strong><?= $j['late_tolerance_minutes'] ?> menit</strong>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalJadwal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" id="form_action" value="store">
                <input type="hidden" name="id" id="form_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Jadwal <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="f_name" class="form-control" required
                            placeholder="e.g. Absen Masuk">
                    </div>
                    <input type="hidden" name="type" id="f_type" value="absen">
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label">Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="f_start" class="form-control" required step="60">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Tepat Waktu <span class="text-danger">*</span></label>
                            <input type="time" name="scheduled_time" id="f_scheduled" class="form-control" required
                                step="60">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Tutup <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="f_end" class="form-control" required step="60">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Toleransi Terlambat (menit)</label>
                        <input type="number" name="late_tolerance_minutes" id="f_tolerance" class="form-control"
                            value="15" min="0">
                        <small class="text-muted">Berapa menit setelah waktu tepat masih dianggap tidak
                            terlambat</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function resetForm() {
        document.getElementById('form_action').value = 'store';
        document.getElementById('form_id').value = '';
        document.getElementById('f_name').value = '';
        document.getElementById('f_start').value = '';
        document.getElementById('f_scheduled').value = '';
        document.getElementById('f_end').value = '';
        document.getElementById('f_tolerance').value = '15';
        document.getElementById('modalTitle').textContent = 'Tambah Jadwal';
    }
    function editJadwal(j) {
        document.getElementById('form_action').value = 'update';
        document.getElementById('form_id').value = j.id;
        document.getElementById('f_name').value = j.name;
        document.getElementById('f_type').value = j.type;
        document.getElementById('f_start').value = j.start_time;
        document.getElementById('f_scheduled').value = j.scheduled_time;
        document.getElementById('f_end').value = j.end_time;
        document.getElementById('f_tolerance').value = j.late_tolerance_minutes;
        document.getElementById('modalTitle').textContent = 'Edit Jadwal';
        new bootstrap.Modal(document.getElementById('modalJadwal')).show();
    }
</script>
<?php include __DIR__ . '/../include/footer.php'; ?>