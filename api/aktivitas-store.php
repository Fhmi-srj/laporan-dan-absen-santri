<?php
/**
 * API: Store New Aktivitas
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();
$pdo = getDB();

try {
    $siswaId = $_POST['siswa_id'] ?? null;
    $kategori = $_POST['kategori'] ?? null;
    $tanggal = !empty($_POST['tanggal']) ? $_POST['tanggal'] : null;
    $tanggalSelesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $batasWaktu = !empty($_POST['batas_waktu']) ? $_POST['batas_waktu'] : null;
    $judul = trim($_POST['judul'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $statusSambangan = $_POST['status_sambangan'] ?? null;

    // Build list of missing fields
    $missingFields = [];

    if (!$siswaId) {
        $missingFields[] = 'Siswa belum dipilih';
    }
    if (!$kategori) {
        $missingFields[] = 'Kategori tidak valid';
    }
    if (!$tanggal) {
        $missingFields[] = 'Tanggal Mulai/Pergi';
    }

    // Validation per category
    switch ($kategori) {
        case 'sakit':
            if (empty($judul))
                $missingFields[] = 'Diagnosa (Judul)';
            break;
        case 'izin_keluar':
            if (empty($judul))
                $missingFields[] = 'Keperluan';
            if (empty($keterangan))
                $missingFields[] = 'Keterangan';
            if (!$batasWaktu)
                $missingFields[] = 'Batas Waktu';
            // tanggal_selesai (Tanggal Kembali) is optional
            break;
        case 'izin_pulang':
            if (empty($judul))
                $missingFields[] = 'Alasan';
            if (empty($keterangan))
                $missingFields[] = 'Keterangan';
            if (!$batasWaktu)
                $missingFields[] = 'Batas Waktu';
            // tanggal_selesai (Tanggal Kembali) is optional
            break;
        case 'sambangan':
            if (empty($judul))
                $missingFields[] = 'Nama Penjenguk';
            if (!$statusSambangan)
                $missingFields[] = 'Hubungan Penjenguk';
            break;
        case 'pelanggaran':
            if (empty($judul))
                $missingFields[] = 'Jenis Pelanggaran';
            if (empty($keterangan))
                $missingFields[] = 'Keterangan Pelanggaran';
            break;
        case 'paket':
            if (empty($judul))
                $missingFields[] = 'Isi Paket';
            // Foto paket wajib untuk paket baru
            if (empty($_FILES['foto_dokumen_1']['name']))
                $missingFields[] = 'Foto Paket';
            break;
        case 'hafalan':
            if (empty($judul))
                $missingFields[] = 'Nama Kitab/Surat';
            break;
    }

    if (!empty($missingFields)) {
        throw new Exception('Data belum lengkap:\\n• ' . implode('\\n• ', $missingFields));
    }

    // Handle file uploads
    $foto1 = null;
    $foto2 = null;

    if (!empty($_FILES['foto_dokumen_1']['name'])) {
        $foto1 = uploadFile($_FILES['foto_dokumen_1'], 'bukti_aktivitas');
    }
    if (!empty($_FILES['foto_dokumen_2']['name'])) {
        $foto2 = uploadFile($_FILES['foto_dokumen_2'], 'bukti_aktivitas');
    }

    $stmt = $pdo->prepare("
        INSERT INTO catatan_aktivitas 
        (siswa_id, kategori, judul, keterangan, status_sambangan, status_kegiatan, tanggal, batas_waktu, tanggal_selesai, foto_dokumen_1, foto_dokumen_2, dibuat_oleh, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $siswaId,
        $kategori,
        $_POST['judul'] ?? null,
        $_POST['keterangan'] ?? null,
        $_POST['status_sambangan'] ?? null,
        // Force status_kegiatan to 'Belum Diterima' for new paket
        $kategori === 'paket' ? 'Belum Diterima' : ($_POST['status_kegiatan'] ?? null),
        $tanggal,
        $batasWaktu,
        $tanggalSelesai,
        $foto1,
        $foto2,
        $user['id']
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Data aktivitas berhasil disimpan!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
