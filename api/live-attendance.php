<?php
/**
 * API: Live Attendance Summary
 * Returns attendance data for live dashboard
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pdo = getDB();
$jadwalId = $_GET['jadwal_id'] ?? null;

if (!$jadwalId) {
    echo json_encode(['error' => 'Jadwal ID required']);
    exit;
}

// Get jadwal
$stmt = $pdo->prepare("SELECT * FROM jadwal_absens WHERE id = ?");
$stmt->execute([$jadwalId]);
$jadwal = $stmt->fetch();

if (!$jadwal) {
    echo json_encode(['error' => 'Jadwal not found']);
    exit;
}

$today = date('Y-m-d');

// Get all siswa
$allSiswa = $pdo->query("SELECT * FROM siswa ORDER BY id ASC")->fetchAll();
enrichSiswaWithSPMB($allSiswa);

// Get attendances for today and this jadwal
$stmt = $pdo->prepare("
    SELECT a.*, s.kelas, s.pendaftaran_id
    FROM attendances a
    JOIN siswa s ON a.user_id = s.id
    WHERE a.attendance_date = ? AND a.jadwal_id = ?
");
$stmt->execute([$today, $jadwalId]);
$attendances = $stmt->fetchAll();

// Enrich attendances with SPMB data
$pendaftaranIds = array_column($attendances, 'pendaftaran_id');
$pendaftaranData = getPendaftaranData($pendaftaranIds);
foreach ($attendances as &$a) {
    $p = $pendaftaranData[$a['pendaftaran_id']] ?? [];
    $a['nama_lengkap'] = $p['nama'] ?? '-';
}
unset($a);

// Index by siswa id
$attendanceByUser = [];
foreach ($attendances as $a) {
    $attendanceByUser[$a['user_id']] = $a;
}

// Categorize
$hadir = [];
$terlambat = [];
$belumHadir = [];

foreach ($allSiswa as $siswa) {
    $siswaData = [
        'nama_lengkap' => $siswa['nama_lengkap'],
        'kelas' => $siswa['kelas']
    ];

    if (isset($attendanceByUser[$siswa['id']])) {
        $att = $attendanceByUser[$siswa['id']];
        $siswaData['waktu_absen'] = substr($att['attendance_time'], 0, 5);

        if ($att['status'] === 'terlambat') {
            $terlambat[] = $siswaData;
        } else {
            $hadir[] = $siswaData;
        }
    } else {
        $belumHadir[] = $siswaData;
    }
}

echo json_encode([
    'hadir' => $hadir,
    'terlambat' => $terlambat,
    'belum_hadir' => $belumHadir,
    'count' => [
        'hadir' => count($hadir),
        'terlambat' => count($terlambat),
        'belum_hadir' => count($belumHadir),
        'total' => count($allSiswa)
    ]
]);
