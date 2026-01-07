<?php
/**
 * API: Cari Siswa
 * Search siswa by keyword (nama, NISN, atau NIK)
 * Returns data from data_induk table
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'pesan' => 'Unauthorized']);
    exit;
}

$keyword = $_GET['keyword'] ?? '';

if (strlen($keyword) < 2) {
    echo json_encode(['status' => 'error', 'pesan' => 'Ketik minimal 2 huruf']);
    exit;
}

// Search from data_induk table
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT id, nama_lengkap, nisn as nomor_induk, kelas, lembaga_sekolah as lembaga, 
           CONCAT(COALESCE(alamat,''), ', ', COALESCE(kecamatan,''), ', ', COALESCE(kabupaten,'')) as alamat,
           no_wa_wali, nomor_rfid
    FROM data_induk 
    WHERE deleted_at IS NULL AND (nama_lengkap LIKE ? OR nisn LIKE ? OR nik LIKE ? OR no_wa_wali LIKE ?)
    ORDER BY nama_lengkap ASC 
    LIMIT 10
");
$search = "%$keyword%";
$stmt->execute([$search, $search, $search, $search]);
$result = $stmt->fetchAll();

// Format result
$formatted = [];
foreach ($result as $r) {
    $formatted[] = [
        'id' => $r['id'],
        'nama_lengkap' => $r['nama_lengkap'] ?? '-',
        'nomor_induk' => $r['nomor_induk'] ?? '-',
        'kelas' => $r['kelas'] ?? '-',
        'lembaga' => $r['lembaga'] ?? '-',
        'alamat' => trim($r['alamat'], ', ') ?: '-',
        'no_wa_wali' => $r['no_wa_wali'] ?? '-'
    ];
}

if (count($formatted) > 0) {
    echo json_encode(['status' => 'success', 'data' => $formatted]);
} else {
    echo json_encode(['status' => 'error', 'pesan' => 'Siswa tidak ditemukan']);
}
