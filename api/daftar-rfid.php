<?php
/**
 * API: Register RFID Card
 * Link an RFID card to a student in data_induk
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$pdo = getDB();

try {
    $siswaId = $_POST['siswa_id'] ?? null;
    $rfid = $_POST['rfid'] ?? null;

    if (!$siswaId || !$rfid) {
        throw new Exception('Data tidak lengkap');
    }

    // Validate RFID format (should be digits)
    if (!preg_match('/^\d{10,}$/', $rfid)) {
        throw new Exception('Format nomor kartu tidak valid');
    }

    // Check if card is already registered to another student
    $checkStmt = $pdo->prepare("SELECT id, nama_lengkap FROM data_induk WHERE nomor_rfid = ? AND id != ? AND deleted_at IS NULL");
    $checkStmt->execute([$rfid, $siswaId]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        throw new Exception("Kartu sudah terdaftar ke {$existing['nama_lengkap']}");
    }

    // Check if siswa exists in data_induk
    $siswaStmt = $pdo->prepare("SELECT * FROM data_induk WHERE id = ? AND deleted_at IS NULL");
    $siswaStmt->execute([$siswaId]);
    $siswa = $siswaStmt->fetch();

    if (!$siswa) {
        throw new Exception('Santri tidak ditemukan');
    }

    // Update data_induk with RFID
    $updateStmt = $pdo->prepare("UPDATE data_induk SET nomor_rfid = ?, updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$rfid, $siswaId]);

    echo json_encode([
        'success' => true,
        'message' => "Kartu berhasil didaftarkan ke {$siswa['nama_lengkap']}"
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
