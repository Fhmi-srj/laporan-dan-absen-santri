<?php
/**
 * API: Get Single Aktivitas for Edit
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$pdo = getDB();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM catatan_aktivitas WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$catatan = $stmt->fetch();

if (!$catatan) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    exit;
}

// Format for datetime-local input
if ($catatan['tanggal']) {
    $catatan['tanggal'] = date('Y-m-d\TH:i', strtotime($catatan['tanggal']));
}
if ($catatan['tanggal_selesai']) {
    $catatan['tanggal_selesai'] = date('Y-m-d\TH:i', strtotime($catatan['tanggal_selesai']));
}

echo json_encode($catatan);
