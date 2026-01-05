<?php
/**
 * API: Check RFID Card
 * Check if an RFID card is already registered to a student
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

$pdo = getDB();

try {
    $rfid = $_GET['rfid'] ?? '';

    if (empty($rfid)) {
        throw new Exception('RFID number required');
    }

    // Check if card is registered
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE no_kartu_rfid = ?");
    $stmt->execute([$rfid]);
    $siswa = $stmt->fetch();

    if ($siswa) {
        // Enrich with SPMB data
        $siswaList = [$siswa];
        enrichSiswaWithSPMB($siswaList);
        $siswa = $siswaList[0];

        echo json_encode([
            'registered' => true,
            'siswa_id' => $siswa['id'],
            'siswa_name' => $siswa['nama_lengkap'] ?? 'Unknown',
            'siswa_kelas' => $siswa['kelas'] ?? ''
        ]);
    } else {
        echo json_encode([
            'registered' => false
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
