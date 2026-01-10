<?php
/**
 * API: Print Izin Sekolah
 * Fetch santri data for printing & generate surat nomor
 */

require_once __DIR__ . '/../functions.php';
requireLogin();

header('Content-Type: application/json');

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get santri with sakit or izin_pulang status (today or recent)
        $kategori = $_GET['kategori'] ?? '';

        if (!in_array($kategori, ['sakit', 'izin_pulang'])) {
            jsonResponse(['success' => false, 'message' => 'Kategori tidak valid'], 400);
        }

        // Get recent aktivitas for selected kategori (last 7 days)
        $stmt = $pdo->prepare("
            SELECT 
                ca.id as aktivitas_id,
                ca.siswa_id,
                di.nama_lengkap,
                di.kelas,
                ca.judul,
                ca.keterangan,
                ca.tanggal,
                ca.kategori
            FROM catatan_aktivitas ca
            JOIN data_induk di ON ca.siswa_id = di.id
            WHERE ca.kategori = ?
              AND ca.deleted_at IS NULL
              AND ca.tanggal >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY ca.tanggal DESC
        ");
        $stmt->execute([$kategori]);
        $data = $stmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        // Generate nomor surat and save print history
        $input = json_decode(file_get_contents('php://input'), true);

        $kategori = $input['kategori'] ?? '';
        $santriIds = $input['santri_ids'] ?? [];
        $santriNames = $input['santri_names'] ?? [];
        $tujuanGuru = $input['tujuan_guru'] ?? '';
        $kelas = $input['kelas'] ?? '';
        $tanggal = $input['tanggal'] ?? date('Y-m-d');

        // Validation
        if (!in_array($kategori, ['sakit', 'izin_pulang'])) {
            jsonResponse(['success' => false, 'message' => 'Kategori tidak valid'], 400);
        }
        if (empty($santriIds) || count($santriIds) > 5) {
            jsonResponse(['success' => false, 'message' => 'Pilih 1-5 santri'], 400);
        }

        // Generate nomor surat: NNN/SKA.001/PPMH/MM/YYYY
        $year = date('Y');
        $month = date('n'); // 1-12
        $romanMonth = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$month - 1];

        // Get next number for this month
        $stmt = $pdo->prepare("
            SELECT COUNT(*) + 1 as next_num 
            FROM print_izin_history 
            WHERE YEAR(printed_at) = ? AND MONTH(printed_at) = ?
        ");
        $stmt->execute([$year, $month]);
        $nextNum = str_pad($stmt->fetchColumn(), 3, '0', STR_PAD_LEFT);

        $nomorSurat = "$nextNum/SKA.001/PPMH/$romanMonth/$year";

        // Save to history
        $stmt = $pdo->prepare("
            INSERT INTO print_izin_history 
            (nomor_surat, kategori, santri_ids, santri_names, tujuan_guru, kelas, tanggal, printed_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nomorSurat,
            $kategori,
            json_encode($santriIds),
            implode(', ', $santriNames),
            $tujuanGuru,
            $kelas,
            $tanggal,
            $user['id']
        ]);

        // Log activity
        logActivity('CREATE', 'print_izin_history', $pdo->lastInsertId(), $nomorSurat, null, [
            'kategori' => $kategori,
            'santri' => $santriNames
        ], 'Print surat izin sekolah');

        jsonResponse([
            'success' => true,
            'nomor_surat' => $nomorSurat,
            'message' => 'Nomor surat berhasil digenerate'
        ]);

    } else {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log("Print Izin API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan server'], 500);
}
