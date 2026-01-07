<?php
/**
 * API: Update Aktivitas
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

$pdo = getDB();

try {
    $id = $_POST['log_id'] ?? null;
    if (!$id) {
        throw new Exception('ID tidak ditemukan');
    }

    // Get existing record
    $stmt = $pdo->prepare("SELECT * FROM catatan_aktivitas WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        throw new Exception('Data tidak ditemukan');
    }

    $kategori = $_POST['kategori'] ?? $existing['kategori'];

    // For paket category, use status_paket field for status_kegiatan
    $statusKegiatan = $_POST['status_kegiatan'] ?? null;
    if ($kategori === 'paket') {
        $statusKegiatan = $_POST['status_paket'] ?? $existing['status_kegiatan'];

        // If changing status to "Sudah Diterima", validate requirements
        if ($statusKegiatan === 'Sudah Diterima' && $existing['status_kegiatan'] !== 'Sudah Diterima') {
            if (empty($_POST['tanggal_selesai'])) {
                throw new Exception('Tanggal Terima wajib diisi saat status Sudah Diterima');
            }
            if (empty($_FILES['foto_dokumen_1']['name'])) {
                throw new Exception('Foto Penerima+Paket wajib diupload saat status Sudah Diterima');
            }
        }
    }

    // Handle file uploads
    $foto1 = $existing['foto_dokumen_1'];
    $foto2 = $existing['foto_dokumen_2'];

    if (!empty($_FILES['foto_dokumen_1']['name'])) {
        // Delete old file
        if ($foto1 && file_exists(__DIR__ . '/../uploads/' . $foto1)) {
            unlink(__DIR__ . '/../uploads/' . $foto1);
        }
        $foto1 = uploadFile($_FILES['foto_dokumen_1'], 'bukti_aktivitas');
    }
    if (!empty($_FILES['foto_dokumen_2']['name'])) {
        if ($foto2 && file_exists(__DIR__ . '/../uploads/' . $foto2)) {
            unlink(__DIR__ . '/../uploads/' . $foto2);
        }
        $foto2 = uploadFile($_FILES['foto_dokumen_2'], 'bukti_aktivitas');
    }

    $stmt = $pdo->prepare("
        UPDATE catatan_aktivitas SET
        kategori = ?, judul = ?, keterangan = ?, status_sambangan = ?, status_kegiatan = ?,
        tanggal = ?, batas_waktu = ?, tanggal_selesai = ?, foto_dokumen_1 = ?, foto_dokumen_2 = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $kategori,
        $_POST['judul'] ?? null,
        $_POST['keterangan'] ?? null,
        $_POST['status_sambangan'] ?? null,
        $statusKegiatan,
        $_POST['tanggal'],
        !empty($_POST['batas_waktu']) ? $_POST['batas_waktu'] : null,
        !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null,
        $foto1,
        $foto2,
        $id
    ]);

    // Get santri name for logging
    $santriStmt = $pdo->prepare("SELECT di.nama_lengkap FROM catatan_aktivitas ca JOIN data_induk di ON ca.siswa_id = di.id WHERE ca.id = ?");
    $santriStmt->execute([$id]);
    $santriName = $santriStmt->fetchColumn();

    // Log activity with complete old and new data
    logActivity('UPDATE', 'catatan_aktivitas', $id, $santriName, [
        'nama' => $santriName,
        'kategori' => $existing['kategori'],
        'judul' => $existing['judul'],
        'tanggal_mulai' => $existing['tanggal'],
        'tanggal_selesai' => $existing['tanggal_selesai'],
        'keterangan' => $existing['keterangan'],
        'status' => $existing['status_kegiatan']
    ], [
        'nama' => $santriName,
        'kategori' => $kategori,
        'judul' => $_POST['judul'] ?? null,
        'tanggal_mulai' => $_POST['tanggal'] ?? null,
        'tanggal_selesai' => $_POST['tanggal_selesai'] ?? null,
        'keterangan' => $_POST['keterangan'] ?? null,
        'status' => $statusKegiatan
    ], "Ubah aktivitas $kategori untuk $santriName");

    echo json_encode(['status' => 'success', 'message' => 'Data aktivitas berhasil diperbarui!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
