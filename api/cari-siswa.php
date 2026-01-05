<?php
/**
 * API: Cari Siswa
 * Search siswa by keyword (nama atau nomor WA wali)
 * Returns siswa data from SPMB pendaftaran
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'pesan' => 'Unauthorized']);
    exit;
}

$pdo = getDB();
$spmb = getSPMBDB();
$keyword = $_GET['keyword'] ?? '';

if (strlen($keyword) < 2) {
    echo json_encode(['status' => 'error', 'pesan' => 'Ketik minimal 2 huruf']);
    exit;
}

// Search from SPMB pendaftaran (primary source)
$stmt = $spmb->prepare("SELECT * FROM pendaftaran WHERE nama LIKE ? OR no_hp_wali LIKE ? ORDER BY nama ASC LIMIT 10");
$stmt->execute(["%$keyword%", "%$keyword%"]);
$pendaftaranList = $stmt->fetchAll();

// Get additional data from siswa table (nomor_induk, kelas, RFID)
$pendaftaranIds = array_column($pendaftaranList, 'id');
$siswaData = [];
if (!empty($pendaftaranIds)) {
    $placeholders = str_repeat('?,', count($pendaftaranIds) - 1) . '?';
    $siswaStmt = $pdo->prepare("SELECT * FROM siswa WHERE pendaftaran_id IN ($placeholders)");
    $siswaStmt->execute($pendaftaranIds);
    while ($row = $siswaStmt->fetch()) {
        $siswaData[$row['pendaftaran_id']] = $row;
    }
}

// Merge and format data
$result = [];
foreach ($pendaftaranList as $p) {
    $s = $siswaData[$p['id']] ?? [];
    $result[] = [
        'id' => $s['id'] ?? null,
        'pendaftaran_id' => $p['id'],
        'nama_lengkap' => $p['nama'] ?? '-',
        'nomor_induk' => $s['nomor_induk'] ?? '-',
        'kelas' => $s['kelas'] ?? '-',
        'lembaga' => $p['lembaga'] ?? '-',
        'alamat' => trim(($p['alamat'] ?? '') . ', ' . ($p['kelurahan_desa'] ?? '') . ', ' . ($p['kecamatan'] ?? ''), ', '),
        'no_wa' => $p['no_hp_wali'] ?? '-',
        'no_wa_wali' => $p['no_hp_wali'] ?? '-'
    ];
}

if (count($result) > 0) {
    echo json_encode(['status' => 'success', 'data' => $result]);
} else {
    echo json_encode(['status' => 'error', 'pesan' => 'Siswa tidak ditemukan']);
}

