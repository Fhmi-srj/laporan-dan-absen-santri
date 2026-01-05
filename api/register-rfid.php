<?php
/**
 * API: Register RFID Card
 * Link an RFID card to a student
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
    $checkStmt = $pdo->prepare("SELECT s.id, s.pendaftaran_id FROM siswa s WHERE s.no_kartu_rfid = ? AND s.id != ?");
    $checkStmt->execute([$rfid, $siswaId]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        // Get name of existing student
        $existingList = [$existing];
        enrichSiswaWithSPMB($existingList);
        $existingName = $existingList[0]['nama_lengkap'] ?? 'siswa lain';
        throw new Exception("Kartu sudah terdaftar ke {$existingName}");
    }

    // Check if siswa exists
    $siswaStmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $siswaStmt->execute([$siswaId]);
    $siswa = $siswaStmt->fetch();

    if (!$siswa) {
        throw new Exception('Santri tidak ditemukan');
    }

    // Update siswa with RFID
    $updateStmt = $pdo->prepare("UPDATE siswa SET no_kartu_rfid = ?, updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$rfid, $siswaId]);

    // Get updated siswa info
    $siswaList = [$siswa];
    enrichSiswaWithSPMB($siswaList);
    $siswaName = $siswaList[0]['nama_lengkap'] ?? 'Santri';

    echo json_encode([
        'success' => true,
        'message' => "Kartu berhasil didaftarkan ke {$siswaName}"
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
