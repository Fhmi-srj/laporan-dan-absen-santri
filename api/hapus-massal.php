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
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Soft delete - move to trash instead of permanent delete
    $params = array_merge([date('Y-m-d H:i:s'), $user['id']], $ids);
    $pdo->prepare("UPDATE catatan_aktivitas SET deleted_at = ?, deleted_by = ? WHERE id IN ($placeholders)")->execute($params);

    // Log activity
    logActivity('DELETE', 'catatan_aktivitas', null, null, null, null, "Hapus " . count($ids) . " data aktivitas ke trash");

    echo json_encode(['status' => 'success', 'message' => 'Data dipindahkan ke trash']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
