<?php
/**
 * API: Bulk Delete Aktivitas
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

// Only admin can delete
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only admin can delete']);
    exit;
}

$pdo = getDB();

try {
    $ids = $_POST['ids'] ?? [];

    if (empty($ids)) {
        throw new Exception('Tidak ada data yang dipilih');
    }

    // Ensure all IDs are integers
    $ids = array_map('intval', $ids);

    // Delete associated files
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT foto_dokumen_1, foto_dokumen_2 FROM catatan_aktivitas WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        if ($row['foto_dokumen_1'] && file_exists(__DIR__ . '/../uploads/' . $row['foto_dokumen_1'])) {
            unlink(__DIR__ . '/../uploads/' . $row['foto_dokumen_1']);
        }
        if ($row['foto_dokumen_2'] && file_exists(__DIR__ . '/../uploads/' . $row['foto_dokumen_2'])) {
            unlink(__DIR__ . '/../uploads/' . $row['foto_dokumen_2']);
        }
    }

    // Delete records
    $pdo->prepare("DELETE FROM catatan_aktivitas WHERE id IN ($placeholders)")->execute($ids);

    echo json_encode(['status' => 'success', 'message' => 'Data terpilih berhasil dihapus']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
