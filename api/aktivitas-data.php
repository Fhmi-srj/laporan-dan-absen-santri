<?php
/**
 * API: Get Aktivitas Data (for DataTables)
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$pdo = getDB();

$kategori = $_POST['kategori'] ?? $_GET['kategori'] ?? '';
$tanggalDari = $_POST['tanggal_dari'] ?? $_GET['tanggal_dari'] ?? '';
$tanggalSampai = $_POST['tanggal_sampai'] ?? $_GET['tanggal_sampai'] ?? '';
$searchKeyword = $_POST['search_keyword'] ?? $_GET['search_keyword'] ?? '';
$start = (int) ($_POST['start'] ?? $_GET['start'] ?? 0);
$length = (int) ($_POST['length'] ?? $_GET['length'] ?? 10);
$draw = (int) ($_POST['draw'] ?? $_GET['draw'] ?? 1);

// Build query
$where = [];
$params = [];

if ($kategori && $kategori !== 'all') {
    $where[] = "ca.kategori = ?";
    $params[] = $kategori;
}

if ($tanggalDari) {
    $where[] = "DATE(ca.tanggal) >= ?";
    $params[] = $tanggalDari;
}

if ($tanggalSampai) {
    $where[] = "DATE(ca.tanggal) <= ?";
    $params[] = $tanggalSampai;
}

// Search - simplified, will filter after SPMB enrichment for name search
if ($searchKeyword) {
    $where[] = "(ca.judul LIKE ? OR ca.keterangan LIKE ? OR ca.status_kegiatan LIKE ?)";
    $params[] = "%$searchKeyword%";
    $params[] = "%$searchKeyword%";
    $params[] = "%$searchKeyword%";
}

// Role-based filtering: kesehatan only sees 'sakit'
if ($user['role'] === 'kesehatan') {
    $where[] = "ca.kategori = 'sakit'";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countSql = "SELECT COUNT(*) FROM catatan_aktivitas ca JOIN siswa s ON ca.siswa_id = s.id $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();

// Handle DataTables ordering
$orderColumn = 'ca.tanggal';
$orderDir = 'DESC';

// Column mapping for sorting (matches DataTables column index)
$columnMap = [
    0 => 'ca.id',
    1 => 'ca.tanggal',
    2 => 'ca.tanggal_selesai',
    3 => 's.pendaftaran_id', // Will sort by ID as proxy for name
    4 => 'ca.kategori',
    5 => 'ca.judul',
    6 => 'ca.keterangan'
];

if (isset($_POST['order'][0]['column']) && isset($_POST['order'][0]['dir'])) {
    $colIndex = (int) $_POST['order'][0]['column'];
    $dir = strtoupper($_POST['order'][0]['dir']) === 'ASC' ? 'ASC' : 'DESC';
    if (isset($columnMap[$colIndex])) {
        $orderColumn = $columnMap[$colIndex];
        $orderDir = $dir;
    }
}

// Get data
$sql = "
    SELECT ca.*, s.nomor_induk, s.kelas, s.pendaftaran_id,
           u.name as pembuat_nama
    FROM catatan_aktivitas ca
    JOIN siswa s ON ca.siswa_id = s.id
    LEFT JOIN users u ON ca.dibuat_oleh = u.id
    $whereClause
    ORDER BY $orderColumn $orderDir
    LIMIT $start, $length
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Enrich with SPMB data
$pendaftaranIds = array_column($data, 'pendaftaran_id');
$pendaftaranData = getPendaftaranData($pendaftaranIds);
foreach ($data as &$row) {
    $p = $pendaftaranData[$row['pendaftaran_id']] ?? [];
    $row['nama_lengkap'] = $p['nama'] ?? '-';
    $row['no_wa_wali'] = $p['no_hp_wali'] ?? '-';
}
unset($row);

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalRecords,
    'data' => $data
]);

