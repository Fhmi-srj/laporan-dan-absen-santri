<?php
/**
 * Admin: User Management
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Manajemen User';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
        // Soft delete user - move to trash (can't delete yourself)
        if ($_POST['id'] != $user['id']) {
            $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
            $stmt->execute([$user['id'], $_POST['id']]);
            logActivity('DELETE', 'users', $_POST['id'], null, null, null, 'Hapus user ke trash');
        }
        redirectWith('pengguna.php', 'success', 'User dipindahkan ke trash!');
    }
    if ($_POST['action'] === 'reset_device' && !empty($_POST['id'])) {
        $pdo->prepare("DELETE FROM user_devices WHERE user_id = ?")->execute([$_POST['id']]);
        redirectWith('pengguna.php', 'success', 'Device berhasil direset!');
    }
    if ($_POST['action'] === 'store') {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['role'],
            $_POST['phone'] ?? null,
            $_POST['address'] ?? null
        ]);
        redirectWith('pengguna.php', 'success', 'User berhasil ditambahkan!');
    }
    if ($_POST['action'] === 'update' && !empty($_POST['id'])) {
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role=?, phone=?, address=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$_POST['name'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role'], $_POST['phone'], $_POST['address'], $_POST['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, phone=?, address=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$_POST['name'], $_POST['email'], $_POST['role'], $_POST['phone'], $_POST['address'], $_POST['id']]);
        }
        redirectWith('pengguna.php', 'success', 'User berhasil diperbarui!');
    }
}

// Get users
$filterRole = $_GET['role'] ?? '';
$sql = "SELECT u.*, (SELECT COUNT(*) FROM user_devices ud WHERE ud.user_id = u.id) as device_count FROM users u WHERE u.deleted_at IS NULL";
if ($filterRole) {
    $sql .= " AND u.role = ? ORDER BY u.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$filterRole]);
} else {
    $sql .= " ORDER BY u.name ASC";
    $stmt = $pdo->query($sql);
}
$users = $stmt->fetchAll();

// Roles from Laravel: admin, karyawan, pengurus, guru, keamanan, kesehatan
$roles = ['admin', 'karyawan', 'pengurus', 'guru', 'keamanan', 'kesehatan'];
$roleLabels = [
    'admin' => 'Administrator',
    'karyawan' => 'Karyawan',
    'pengurus' => 'Pengurus',
    'guru' => 'Guru',
    'keamanan' => 'Keamanan',
    'kesehatan' => 'Kesehatan'
];
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-users me-2 text-primary"></i>Manajemen User</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUser" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i> Tambah User
        </button>
    </div>

    <div class="card-custom">
        <div class="p-3 border-bottom">
            <select class="form-select w-auto" onchange="location.href='pengguna.php?role='+this.value">
                <option value="">Semua Role</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r ?>" <?= $filterRole === $r ? 'selected' : '' ?>>
                        <?= $roleLabels[$r] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sortable">
                <thead class="bg-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Device</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?= e($u['name']) ?></strong></td>
                            <td><?= e($u['email']) ?></td>
                            <td><span
                                    class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'secondary' ?>"><?= $roleLabels[$u['role']] ?? ucfirst($u['role']) ?></span>
                            </td>
                            <td><?= e($u['phone'] ?? '-') ?></td>
                            <td>
                                <?= $u['device_count'] > 0 ? '<i class="fas fa-mobile-alt text-success"></i>' : '<i class="fas fa-mobile-alt text-muted"></i>' ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)"><i
                                        class="fas fa-edit"></i></button>
                                <?php if ($u['device_count'] > 0): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Reset device user ini?')">
                                        <input type="hidden" name="action" value="reset_device">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-info"><i class="fas fa-sync"></i></button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($u['id'] != $user['id']): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" id="form_action" value="store">
                <input type="hidden" name="id" id="form_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" id="form_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="form_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <small class="text-muted">(kosongkan jika tidak
                                ubah)</small></label>
                        <input type="password" name="password" id="form_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="phone" id="form_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="form_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="form_role" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r ?>"><?= $roleLabels[$r] ?></option>
                            <?php endforeach; ?>
                        </select>
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
        document.getElementById('form_name').value = '';
        document.getElementById('form_email').value = '';
        document.getElementById('form_password').value = '';
        document.getElementById('form_phone').value = '';
        document.getElementById('form_address').value = '';
        document.getElementById('form_role').value = 'karyawan';
        document.getElementById('modalTitle').textContent = 'Tambah User';
    }
    function editUser(u) {
        document.getElementById('form_action').value = 'update';
        document.getElementById('form_id').value = u.id;
        document.getElementById('form_name').value = u.name;
        document.getElementById('form_email').value = u.email;
        document.getElementById('form_password').value = '';
        document.getElementById('form_phone').value = u.phone || '';
        document.getElementById('form_address').value = u.address || '';
        document.getElementById('form_role').value = u.role;
        document.getElementById('modalTitle').textContent = 'Edit User';
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    }
</script>
<?php include __DIR__ . '/../include/footer.php'; ?>