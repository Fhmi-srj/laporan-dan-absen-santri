<?php
/**
 * Admin: Trash (Recycle Bin)
 * View and restore/permanently delete soft-deleted records
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDB();
$pageTitle = 'Trash';
$flash = getFlash();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? '';
    $ids = $_POST['ids'] ?? [];

    $allowedTables = ['data_induk', 'catatan_aktivitas', 'attendances', 'users', 'jadwal_absens'];

    // Empty trash doesn't need table validation
    if ($_POST['action'] === 'empty_trash') {
        $counts = 0;
        foreach ($allowedTables as $t) {
            try {
                $stmt = $pdo->prepare("DELETE FROM $t WHERE deleted_at IS NOT NULL");
                $stmt->execute();
                $counts += $stmt->rowCount();
            } catch (Exception $e) {
            }
        }
        logActivity('EMPTY_TRASH', null, null, null, null, null, "Dikosongkan trash: $counts data dihapus permanen");
        redirectWith('trash.php', 'success', "Trash dikosongkan! $counts data dihapus permanen.");
    }

    // Other actions require valid table
    if (!in_array($table, $allowedTables)) {
        redirectWith('trash.php', 'error', 'Tabel tidak valid!');
    }

    if ($_POST['action'] === 'restore' && $id) {
        // Get record name for logging
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        $recordName = $record['nama_lengkap'] ?? $record['name'] ?? $record['id'];

        if (restoreRecord($table, $id)) {
            logActivity('RESTORE', $table, $id, $recordName, null, null, "Restored from trash");
            redirectWith('trash.php', 'success', 'Data berhasil di-restore!');
        }
    }

    if ($_POST['action'] === 'permanent_delete' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        $recordName = $record['nama_lengkap'] ?? $record['name'] ?? $record['id'];

        if (permanentDelete($table, $id)) {
            logActivity('PERMANENT_DELETE', $table, $id, $recordName, $record, null, "Permanently deleted");
            redirectWith('trash.php', 'success', 'Data berhasil dihapus permanen!');
        }
    }

    if ($_POST['action'] === 'bulk_restore' && !empty($ids)) {
        $restored = 0;
        foreach ($ids as $id) {
            if (restoreRecord($table, $id))
                $restored++;
        }
        logActivity('BULK_RESTORE', $table, null, null, null, null, "Restored $restored records");
        redirectWith('trash.php', 'success', "$restored data berhasil di-restore!");
    }

    if ($_POST['action'] === 'bulk_delete' && !empty($ids)) {
        $deleted = 0;
        foreach ($ids as $id) {
            if (permanentDelete($table, $id))
                $deleted++;
        }
        logActivity('BULK_PERMANENT_DELETE', $table, null, null, null, null, "Permanently deleted $deleted records");
        redirectWith('trash.php', 'success', "$deleted data berhasil dihapus permanen!");
    }
}

// Get auto-purge settings
$autoPurgeEnabled = getSetting('auto_purge_enabled', '0') === '1';
$autoPurgeDays = getSetting('auto_purge_days', '30');

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    setSetting('auto_purge_enabled', isset($_POST['auto_purge_enabled']) ? '1' : '0');
    setSetting('auto_purge_days', intval($_POST['auto_purge_days']));
    redirectWith('trash.php', 'success', 'Pengaturan berhasil disimpan!');
}

// Active tab
$activeTab = $_GET['tab'] ?? 'santri';

// Get trash data for each table
$trashData = [];

// Santri
$stmt = $pdo->query("SELECT id, nama_lengkap, nisn, kelas, deleted_at, deleted_by FROM data_induk WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
$trashData['santri'] = $stmt->fetchAll();

// Aktivitas
$stmt = $pdo->query("
    SELECT ca.id, di.nama_lengkap, ca.kategori, ca.judul, ca.deleted_at, ca.deleted_by 
    FROM catatan_aktivitas ca 
    LEFT JOIN data_induk di ON ca.siswa_id = di.id 
    WHERE ca.deleted_at IS NOT NULL 
    ORDER BY ca.deleted_at DESC
");
$trashData['aktivitas'] = $stmt->fetchAll();

// Absensi
$stmt = $pdo->query("
    SELECT a.id, di.nama_lengkap, a.status, a.attendance_time, a.deleted_at, a.deleted_by 
    FROM attendances a 
    LEFT JOIN data_induk di ON a.user_id = di.id 
    WHERE a.deleted_at IS NOT NULL 
    ORDER BY a.deleted_at DESC
");
$trashData['absensi'] = $stmt->fetchAll();

// Users
$stmt = $pdo->query("SELECT id, name, email, role, deleted_at, deleted_by FROM users WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
$trashData['users'] = $stmt->fetchAll();

// Get deleters names
$deleters = $pdo->query("SELECT id, name FROM users")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<style>
    .nav-tabs-trash .nav-link {
        color: #64748b;
        border: none;
        border-bottom: 2px solid transparent;
    }

    .nav-tabs-trash .nav-link.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        background: none;
    }

    .nav-tabs-trash .nav-link .badge {
        font-size: 0.65rem;
    }

    .trash-table {
        font-size: 0.85rem;
    }

    .trash-table th {
        background: #f8fafc;
        white-space: nowrap;
    }

    .deleted-info {
        font-size: 0.75rem;
        color: #94a3b8;
    }
</style>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-trash-restore me-2"></i>Trash</h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalSettings">
                <i class="fas fa-cog me-1"></i>Pengaturan
            </button>
            <button type="button" class="btn btn-danger btn-sm" id="btn-empty-trash">
                <i class="fas fa-trash me-1"></i>Kosongkan Trash
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-trash mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'santri' ? 'active' : '' ?>" href="?tab=santri">
                <i class="fas fa-users me-1"></i>Santri
                <span class="badge bg-secondary">
                    <?= count($trashData['santri']) ?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'aktivitas' ? 'active' : '' ?>" href="?tab=aktivitas">
                <i class="fas fa-clipboard-list me-1"></i>Aktivitas
                <span class="badge bg-secondary">
                    <?= count($trashData['aktivitas']) ?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'absensi' ? 'active' : '' ?>" href="?tab=absensi">
                <i class="fas fa-calendar-check me-1"></i>Absensi
                <span class="badge bg-secondary">
                    <?= count($trashData['absensi']) ?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'users' ? 'active' : '' ?>" href="?tab=users">
                <i class="fas fa-user-cog me-1"></i>Users
                <span class="badge bg-secondary">
                    <?= count($trashData['users']) ?>
                </span>
            </a>
        </li>
    </ul>

    <!-- Bulk Actions -->
    <div id="bulk-actions" class="bg-primary bg-opacity-10 px-3 py-2 rounded mb-3 d-none">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-primary fw-bold"><i class="fas fa-check-circle me-1"></i><span
                    id="selected-count">0</span> dipilih</span>
            <div>
                <button type="button" class="btn btn-success btn-sm me-2" id="btn-bulk-restore">
                    <i class="fas fa-undo me-1"></i>Restore
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-bulk-delete">
                    <i class="fas fa-times me-1"></i>Hapus Permanen
                </button>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <?php if ($activeTab === 'santri'): ?>
                <input type="hidden" id="current-table" value="data_induk">
                <table class="table table-hover trash-table mb-0">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Dihapus</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trashData['santri'])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data di trash</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trashData['santri'] as $row): ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input row-checkbox" value="<?= $row['id'] ?>"></td>
                                    <td class="fw-bold">
                                        <?= e($row['nama_lengkap']) ?>
                                    </td>
                                    <td>
                                        <?= e($row['nisn'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= e($row['kelas'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <div class="deleted-info">
                                            <?= date('d/m/Y H:i', strtotime($row['deleted_at'])) ?>
                                            <?php if ($row['deleted_by'] && isset($deleters[$row['deleted_by']])): ?>
                                                <br>oleh
                                                <?= e($deleters[$row['deleted_by']]) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="table" value="data_induk">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm"><i
                                                    class="fas fa-undo"></i></button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen?')">
                                            <input type="hidden" name="action" value="permanent_delete">
                                            <input type="hidden" name="table" value="data_induk">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($activeTab === 'aktivitas'): ?>
                <input type="hidden" id="current-table" value="catatan_aktivitas">
                <table class="table table-hover trash-table mb-0">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th>Santri</th>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Dihapus</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trashData['aktivitas'])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data di trash</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trashData['aktivitas'] as $row): ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input row-checkbox" value="<?= $row['id'] ?>"></td>
                                    <td class="fw-bold">
                                        <?= e($row['nama_lengkap'] ?? '-') ?>
                                    </td>
                                    <td><span class="badge bg-secondary">
                                            <?= e($row['kategori']) ?>
                                        </span></td>
                                    <td>
                                        <?= e($row['judul'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <div class="deleted-info">
                                            <?= date('d/m/Y H:i', strtotime($row['deleted_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="table" value="catatan_aktivitas">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm"><i
                                                    class="fas fa-undo"></i></button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen?')">
                                            <input type="hidden" name="action" value="permanent_delete">
                                            <input type="hidden" name="table" value="catatan_aktivitas">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($activeTab === 'absensi'): ?>
                <input type="hidden" id="current-table" value="attendances">
                <table class="table table-hover trash-table mb-0">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th>Santri</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Dihapus</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trashData['absensi'])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data di trash</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trashData['absensi'] as $row): ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input row-checkbox" value="<?= $row['id'] ?>"></td>
                                    <td class="fw-bold">
                                        <?= e($row['nama_lengkap'] ?? '-') ?>
                                    </td>
                                    <td><span
                                            class="badge bg-<?= $row['status'] === 'hadir' ? 'success' : ($row['status'] === 'terlambat' ? 'warning' : 'danger') ?>">
                                            <?= e($row['status']) ?>
                                        </span></td>
                                    <td>
                                        <?= e($row['attendance_time'] ? date('d/m/Y H:i', strtotime($row['attendance_time'])) : '-') ?>
                                    </td>
                                    <td>
                                        <div class="deleted-info">
                                            <?= date('d/m/Y H:i', strtotime($row['deleted_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="table" value="attendances">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm"><i
                                                    class="fas fa-undo"></i></button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen?')">
                                            <input type="hidden" name="action" value="permanent_delete">
                                            <input type="hidden" name="table" value="attendances">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($activeTab === 'users'): ?>
                <input type="hidden" id="current-table" value="users">
                <table class="table table-hover trash-table mb-0">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Dihapus</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trashData['users'])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data di trash</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trashData['users'] as $row): ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input row-checkbox" value="<?= $row['id'] ?>"></td>
                                    <td class="fw-bold">
                                        <?= e($row['name']) ?>
                                    </td>
                                    <td>
                                        <?= e($row['email']) ?>
                                    </td>
                                    <td><span class="badge bg-info">
                                            <?= e($row['role']) ?>
                                        </span></td>
                                    <td>
                                        <div class="deleted-info">
                                            <?= date('d/m/Y H:i', strtotime($row['deleted_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="table" value="users">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm"><i
                                                    class="fas fa-undo"></i></button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen?')">
                                            <input type="hidden" name="action" value="permanent_delete">
                                            <input type="hidden" name="table" value="users">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Settings -->
<div class="modal fade" id="modalSettings" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="save_settings" value="1">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title"><i class="fas fa-cog me-2"></i>Pengaturan Trash</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="auto_purge_enabled"
                            id="auto_purge_enabled" <?= $autoPurgeEnabled ? 'checked' : '' ?>>
                        <label class="form-check-label" for="auto_purge_enabled">Aktifkan Auto-Hapus Permanen</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hapus otomatis setelah (hari)</label>
                        <input type="number" name="auto_purge_days" class="form-control"
                            value="<?= e($autoPurgeDays) ?>" min="1" max="365">
                        <small class="text-muted">Data di trash akan dihapus permanen setelah X hari</small>
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

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        const currentTable = $('#current-table').val();

        $('#select-all').on('change', function () {
            $('.row-checkbox').prop('checked', this.checked);
            toggleBulkActions();
        });

        $('.row-checkbox').on('change', toggleBulkActions);

        function toggleBulkActions() {
            let count = $('.row-checkbox:checked').length;
            $('#selected-count').text(count);
            $('#bulk-actions').toggleClass('d-none', count === 0);
        }

        function getSelectedIds() {
            return $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();
        }

        $('#btn-bulk-restore').on('click', function () {
            let ids = getSelectedIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Restore ' + ids.length + ' data?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Restore!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = $('<form method="POST"><input type="hidden" name="action" value="bulk_restore"><input type="hidden" name="table" value="' + currentTable + '"></form>');
                    ids.forEach(id => form.append('<input type="hidden" name="ids[]" value="' + id + '">'));
                    $('body').append(form);
                    form.submit();
                }
            });
        });

        $('#btn-bulk-delete').on('click', function () {
            let ids = getSelectedIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Hapus Permanen ' + ids.length + ' data?',
                text: 'Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus Permanen!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = $('<form method="POST"><input type="hidden" name="action" value="bulk_delete"><input type="hidden" name="table" value="' + currentTable + '"></form>');
                    ids.forEach(id => form.append('<input type="hidden" name="ids[]" value="' + id + '">'));
                    $('body').append(form);
                    form.submit();
                }
            });
        });

        $('#btn-empty-trash').on('click', function () {
            Swal.fire({
                title: 'Kosongkan Semua Trash?',
                text: 'Semua data di trash akan dihapus permanen dan tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Kosongkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = $('<form method="POST"><input type="hidden" name="action" value="empty_trash"></form>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>