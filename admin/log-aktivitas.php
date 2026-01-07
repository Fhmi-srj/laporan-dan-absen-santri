<?php
/**
 * Admin: Log Aktivitas
 * View and manage user activity logs
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDB();
$pageTitle = 'Log Aktivitas';

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'bulk_delete' && !empty($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        redirectWith('log-aktivitas.php', 'success', count($ids) . ' log berhasil dihapus!');
    }
    if ($_POST['action'] === 'delete_single' && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        redirectWith('log-aktivitas.php', 'success', 'Log berhasil dihapus!');
    }
    if ($_POST['action'] === 'clear_all') {
        $pdo->exec("TRUNCATE TABLE activity_logs");
        redirectWith('log-aktivitas.php', 'success', 'Semua log berhasil dihapus!');
    }
}

// Filters
$filterUser = $_GET['user'] ?? '';
$filterAction = $_GET['action_type'] ?? '';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$whereClause = "1=1";
$params = [];

if ($filterUser) {
    $whereClause .= " AND user_name LIKE ?";
    $params[] = "%$filterUser%";
}
if ($filterAction) {
    $whereClause .= " AND action = ?";
    $params[] = $filterAction;
}
if ($filterDateFrom) {
    $whereClause .= " AND DATE(created_at) >= ?";
    $params[] = $filterDateFrom;
}
if ($filterDateTo) {
    $whereClause .= " AND DATE(created_at) <= ?";
    $params[] = $filterDateTo;
}

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Get logs
$stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get unique users for filter
$users = $pdo->query("SELECT DISTINCT user_name FROM activity_logs ORDER BY user_name")->fetchAll(PDO::FETCH_COLUMN);

$flash = getFlash();
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<style>
    .table-logs {
        font-size: 0.85rem;
    }

    .table-logs th {
        white-space: nowrap;
        background: #f1f5f9;
    }

    .badge-action {
        font-size: 0.7rem;
        padding: 0.35em 0.65em;
    }

    .badge-LOGIN {
        background: #3b82f6;
    }

    .badge-LOGOUT {
        background: #64748b;
    }

    .badge-CREATE {
        background: #10b981;
    }

    .badge-UPDATE {
        background: #f59e0b;
    }

    .badge-DELETE {
        background: #ef4444;
    }

    .badge-RESTORE {
        background: #8b5cf6;
    }

    .log-detail {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .device-badge {
        background: #e2e8f0;
        color: #475569;
        font-size: 0.7rem;
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
        <h4 class="fw-bold mb-0"><i class="fas fa-history me-2"></i>Log Aktivitas</h4>
        <div>
            <button type="button" class="btn btn-danger btn-sm" id="btn-clear-all">
                <i class="fas fa-trash me-1"></i>Hapus Semua
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">USER</label>
                    <select name="user" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= e($u) ?>" <?= $filterUser === $u ? 'selected' : '' ?>><?= e($u) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">AKTIVITAS</label>
                    <select name="action_type" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="LOGIN" <?= $filterAction === 'LOGIN' ? 'selected' : '' ?>>Login</option>
                        <option value="LOGOUT" <?= $filterAction === 'LOGOUT' ? 'selected' : '' ?>>Logout</option>
                        <option value="CREATE" <?= $filterAction === 'CREATE' ? 'selected' : '' ?>>Create</option>
                        <option value="UPDATE" <?= $filterAction === 'UPDATE' ? 'selected' : '' ?>>Update</option>
                        <option value="DELETE" <?= $filterAction === 'DELETE' ? 'selected' : '' ?>>Delete</option>
                        <option value="RESTORE" <?= $filterAction === 'RESTORE' ? 'selected' : '' ?>>Restore</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">DARI</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="<?= e($filterDateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">SAMPAI</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                        value="<?= e($filterDateTo) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i
                            class="fas fa-search me-1"></i>Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="log-aktivitas.php" class="btn btn-light border btn-sm w-100"><i
                            class="fas fa-times me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div id="bulk-actions" class="bg-danger bg-opacity-10 px-3 py-2 rounded mb-3 d-none">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-danger fw-bold"><i class="fas fa-check-circle me-1"></i><span id="selected-count">0</span>
                dipilih</span>
            <button type="button" class="btn btn-danger btn-sm" id="btn-bulk-delete">
                <i class="fas fa-trash me-1"></i>Hapus Terpilih
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-logs mb-0">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>No</th>
                        <th>User</th>
                        <th>Device</th>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                        <th>Waktu</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada log aktivitas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td><input type="checkbox" class="form-check-input row-checkbox" value="<?= $log['id'] ?>"></td>
                                <td><?= $offset + $i + 1 ?></td>
                                <td>
                                    <div class="fw-bold"><?= e($log['user_name']) ?></div>
                                </td>
                                <td><span class="badge device-badge"><?= e($log['device_name']) ?></span></td>
                                <td>
                                    <span class="badge badge-action badge-<?= $log['action'] ?>"><?= $log['action'] ?></span>
                                </td>
                                <td class="log-detail">
                                    <?php if ($log['description']): ?>
                                        <span><?= e($log['description']) ?></span>
                                    <?php elseif ($log['record_name']): ?>
                                        <span><?= e($log['record_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                    <?php if ($log['old_data'] || $log['new_data']): ?>
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 btn-view-detail"
                                            data-old="<?= e($log['old_data']) ?>" data-new="<?= e($log['new_data']) ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-single"
                                        data-id="<?= $log['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-center">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                            href="?page=<?= $p ?>&user=<?= e($filterUser) ?>&action_type=<?= e($filterAction) ?>&date_from=<?= e($filterDateFrom) ?>&date_to=<?= e($filterDateTo) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <div class="text-center text-muted mt-2">
        <small>Total: <?= number_format($total) ?> log</small>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detail Perubahan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-danger mb-3"><i class="fas fa-minus-circle me-1"></i>Data Lama</h6>
                        <div id="old-data-container" class="bg-light p-3 rounded"
                            style="max-height: 300px; overflow: auto;"></div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success mb-3"><i class="fas fa-plus-circle me-1"></i>Data Baru</h6>
                        <div id="new-data-container" class="bg-light p-3 rounded"
                            style="max-height: 300px; overflow: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Select all
        $('#select-all').on('change', function () {
            $('.row-checkbox').prop('checked', this.checked);
            toggleBulkActions();
        });

        $('.row-checkbox').on('change', toggleBulkActions);

        function toggleBulkActions() {
            let count = $('.row-checkbox:checked').length;
            $('#selected-count').text(count);
            if (count > 0) {
                $('#bulk-actions').removeClass('d-none');
            } else {
                $('#bulk-actions').addClass('d-none');
            }
        }

        // Bulk delete
        $('#btn-bulk-delete').on('click', function () {
            let ids = [];
            $('.row-checkbox:checked').each(function () {
                ids.push($(this).val());
            });

            if (ids.length === 0) return;

            Swal.fire({
                title: 'Hapus ' + ids.length + ' log?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = $('<form method="POST"><input type="hidden" name="action" value="bulk_delete"></form>');
                    ids.forEach(id => form.append('<input type="hidden" name="ids[]" value="' + id + '">'));
                    $('body').append(form);
                    form.submit();
                }
            });
        });

        // Clear all
        $('#btn-clear-all').on('click', function () {
            Swal.fire({
                title: 'Hapus Semua Log?',
                text: 'Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus Semua!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = $('<form method="POST"><input type="hidden" name="action" value="clear_all"></form>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });

        // View detail
        $('.btn-view-detail').on('click', function () {
            let oldData = $(this).data('old');
            let newData = $(this).data('new');

            function formatDataAsTable(data) {
                if (!data) return '<span class="text-muted">Tidak ada data</span>';

                // Convert to object if string
                let obj = data;
                if (typeof data === 'string') {
                    try {
                        obj = JSON.parse(data);
                    } catch (e) {
                        return '<span>' + data + '</span>';
                    }
                }

                // Build table
                let html = '<table class="table table-sm table-bordered mb-0">';
                for (let key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        let value = obj[key];
                        if (value === null || value === '') value = '-';
                        html += '<tr><td class="fw-bold bg-white" style="width:40%">' + key.replace(/_/g, ' ').toUpperCase() + '</td>';
                        html += '<td>' + value + '</td></tr>';
                    }
                }
                html += '</table>';
                return html;
            }

            $('#old-data-container').html(formatDataAsTable(oldData));
            $('#new-data-container').html(formatDataAsTable(newData));

            new bootstrap.Modal(document.getElementById('modalDetail')).show();
        });

        // Single delete with SweetAlert
        $(document).on('click', '.btn-delete-single', function () {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Log Ini?',
                text: 'Log akan dihapus permanen',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = $('<form method="POST"><input type="hidden" name="action" value="delete_single"><input type="hidden" name="id" value="' + id + '"></form>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>