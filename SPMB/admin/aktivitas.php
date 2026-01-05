<?php
require_once '../api/config.php';
requireLogin();

$conn = getConnection();

// Filters
$action = sanitize($conn, $_GET['action'] ?? '');
$dateFrom = sanitize($conn, $_GET['from'] ?? '');
$dateTo = sanitize($conn, $_GET['to'] ?? '');

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];
$types = '';

if ($action) {
    $where[] = "al.action = ?";
    $params[] = $action;
    $types .= 's';
}

if ($dateFrom) {
    $where[] = "DATE(al.created_at) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo) {
    $where[] = "DATE(al.created_at) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countSql = "SELECT COUNT(*) as total FROM activity_log al $whereClause";
$countStmt = $conn->prepare($countSql);
if ($params) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Get paginated data
$sql = "SELECT al.*, a.nama as admin_nama 
        FROM activity_log al 
        LEFT JOIN admin a ON al.admin_id = a.id 
        $whereClause 
        ORDER BY al.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

// Get distinct actions for filter
$actionsResult = $conn->query("SELECT DISTINCT action FROM activity_log ORDER BY action");
$actions = [];
while ($row = $actionsResult->fetch_assoc()) {
    $actions[] = $row['action'];
}

$conn->close();

// Page config
$pageTitle = 'Log Aktivitas - Admin SPMB';
$currentPage = 'aktivitas';

// Build pagination query string
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>
<?php include 'includes/header.php'; ?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Log Aktivitas</h2>
            <p class="text-gray-500 text-sm">Riwayat aktivitas admin di sistem</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <select name="action" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    <option value="">Semua Aksi</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= $action === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="from" value="<?= htmlspecialchars($dateFrom) ?>" placeholder="Dari tanggal"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                <input type="date" name="to" value="<?= htmlspecialchars($dateTo) ?>" placeholder="Sampai tanggal"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="aktivitas.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <?php if (empty($logs)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-history text-4xl mb-3"></i>
                <p>Belum ada aktivitas tercatat</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <?= date('d M Y', strtotime($log['created_at'])) ?><br>
                                <span class="text-xs"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800"><?= htmlspecialchars($log['admin_nama'] ?? 'System') ?></td>
                            <td class="px-4 py-3">
                                <?php
                                $actionColors = [
                                    'LOGIN' => 'bg-green-100 text-green-800',
                                    'LOGOUT' => 'bg-gray-100 text-gray-800',
                                    'DELETE' => 'bg-red-100 text-red-800',
                                    'STATUS_UPDATE' => 'bg-blue-100 text-blue-800',
                                    'PROFILE_UPDATE' => 'bg-yellow-100 text-yellow-800',
                                    'PASSWORD_CHANGE' => 'bg-purple-100 text-purple-800'
                                ];
                                $color = $actionColors[$log['action']] ?? 'bg-gray-100 text-gray-700';
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?= $color ?>"><?= htmlspecialchars($log['action']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($log['description']) ?></td>
                            <td class="px-4 py-3 text-xs text-gray-400 font-mono"><?= htmlspecialchars($log['ip_address']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-gray-500">
                    Menampilkan <?= $offset + 1 ?>-<?= min($offset + $perPage, $totalRows) ?> dari <?= $totalRows ?> log
                </p>
                <?php if ($totalPages > 1): ?>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?>
                    <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>" class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($p = $start; $p <= $end; $p++):
                    ?>
                    <a href="?<?= $queryString ?>&page=<?= $p ?>" 
                       class="px-3 py-1 border rounded-lg text-sm <?= $p === $page ? 'bg-primary text-white border-primary' : 'border-gray-300 hover:bg-gray-50' ?>">
                        <?= $p ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?<?= $queryString ?>&page=<?= $page + 1 ?>" class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
