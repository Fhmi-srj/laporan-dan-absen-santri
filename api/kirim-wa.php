<?php
/**
 * API: Send WhatsApp Message
 * Sends WhatsApp message directly via WA Gateway API
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

try {
    $phone = $_POST['phone'] ?? null;
    $message = $_POST['message'] ?? null;
    $imagePath = $_POST['image'] ?? null; // Optional image path

    if (empty($phone)) {
        throw new Exception('Nomor WhatsApp tidak tersedia');
    }

    if (empty($message)) {
        throw new Exception('Pesan tidak boleh kosong');
    }

    // Send via WhatsApp API (with or without image)
    if (!empty($imagePath)) {
        $result = sendWhatsAppWithImage($phone, $message, $imagePath);
    } else {
        $result = sendWhatsApp($phone, $message);
    }

    if ($result['success']) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Pesan WhatsApp berhasil dikirim!'
        ]);
    } else {
        throw new Exception($result['message']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
