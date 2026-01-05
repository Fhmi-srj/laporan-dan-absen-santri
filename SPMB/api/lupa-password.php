<?php
// =============================================
// Lupa Password - Send Reset Link via WhatsApp
// =============================================

require_once '../api/config.php';
require_once '../api/whatsapp.php';

header('Content-Type: application/json');

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$no_hp = isset($_POST['no_hp']) ? trim($_POST['no_hp']) : '';

if (empty($no_hp)) {
    jsonResponse(false, 'Nomor HP wajib diisi');
}

// Normalize phone number - strip all non-digits
$no_hp = preg_replace('/[^0-9]/', '', $no_hp);

// Build list of possible formats to try
$phoneFormats = [];

// If the number starts with 62
if (substr($no_hp, 0, 2) === '62') {
    $phoneFormats[] = $no_hp;                       // 6281234567890
    $phoneFormats[] = '+' . $no_hp;                  // +6281234567890
    $phoneFormats[] = '0' . substr($no_hp, 2);       // 081234567890
    $phoneFormats[] = substr($no_hp, 2);             // 81234567890
}
// If the number starts with 0
elseif (substr($no_hp, 0, 1) === '0') {
    $phoneFormats[] = $no_hp;                       // 081234567890
    $phoneFormats[] = '62' . substr($no_hp, 1);     // 6281234567890
    $phoneFormats[] = '+62' . substr($no_hp, 1);    // +6281234567890
    $phoneFormats[] = substr($no_hp, 1);            // 81234567890
}
// Raw number (8xxx)
else {
    $phoneFormats[] = $no_hp;                       // 81234567890
    $phoneFormats[] = '0' . $no_hp;                 // 081234567890
    $phoneFormats[] = '62' . $no_hp;                // 6281234567890
    $phoneFormats[] = '+62' . $no_hp;               // +6281234567890
}

// Try to find the phone number in any format
$user = null;
foreach ($phoneFormats as $tryPhone) {
    $stmt = $conn->prepare("SELECT id, nama, no_registrasi, no_hp_wali FROM pendaftaran WHERE no_hp_wali = ?");
    $stmt->bind_param("s", $tryPhone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        break;
    }
    $stmt->close();
}

if (!$user) {
    jsonResponse(false, 'Nomor HP tidak terdaftar dalam sistem');
}

// Generate unique token (9 characters)
$token = substr(bin2hex(random_bytes(5)), 0, 9);
date_default_timezone_set('Asia/Jakarta'); // Set timezone WIB
$expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

// Save token to database
$updateStmt = $conn->prepare("UPDATE pendaftaran SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
$updateStmt->bind_param("ssi", $token, $expires, $user['id']);

if ($updateStmt->execute()) {
    // Verify token was saved
    if ($updateStmt->affected_rows === 0) {
        jsonResponse(false, 'Token gagal disimpan - affected_rows: 0');
    }
    // Build reset link
    $resetLink = "https://daftar.mambaulhuda.ponpes.id/user/reset-password.php?token=" . $token;

    // Send WhatsApp with reset link
    $waMessage = waTemplateLupaPassword($user['nama'], $user['no_hp_wali'], $resetLink);
    $waResult = sendWhatsApp($user['no_hp_wali'], $waMessage);

    if ($waResult['success']) {
        jsonResponse(true, 'Link reset password telah dikirim ke WhatsApp Anda', [
            'nama' => $user['nama']
        ]);
    } else {
        jsonResponse(true, 'Link reset password sedang dikirim ke WhatsApp Anda.', [
            'nama' => $user['nama'],
            'wa_status' => 'pending'
        ]);
    }
} else {
    jsonResponse(false, 'Gagal membuat link reset password');
}

$conn->close();
?>