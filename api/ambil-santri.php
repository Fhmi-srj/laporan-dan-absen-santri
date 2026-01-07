<?php
/**
 * API: Get Santri Data by ID
 * Returns all fields from data_induk
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM data_induk WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$santri = $stmt->fetch();

if (!$santri) {
    echo json_encode(['success' => false, 'message' => 'Data santri tidak ditemukan']);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => $santri
]);
