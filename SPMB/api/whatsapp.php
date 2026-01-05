<?php
// =============================================
// MPWA WhatsApp API Helper
// =============================================

define('MPWA_API_URL', 'http://serverwa.hello-inv.com/send-message');
define('MPWA_API_KEY', 'VbM1epmqMKqrztVrWpd1YquAboWWFa');
define('MPWA_SENDER', '6282131871383');

/**
 * Send WhatsApp message via MPWA API
 * 
 * @param string $number Nomor tujuan (format: 628xxx)
 * @param string $message Pesan yang akan dikirim
 * @return array Response dari API
 */
function sendWhatsApp($number, $message)
{
    // Normalize phone number (remove +, spaces, etc)
    $number = preg_replace('/[^0-9]/', '', $number);

    // Convert 08xx to 628xx
    if (substr($number, 0, 1) === '0') {
        $number = '62' . substr($number, 1);
    }

    $data = [
        'api_key' => MPWA_API_KEY,
        'sender' => MPWA_SENDER,
        'number' => $number,
        'message' => $message
    ];

    $ch = curl_init(MPWA_API_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'success' => $httpCode == 200 && empty($error),
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

/**
 * Template: Selamat Pendaftaran
 */
function waTemplatePendaftaran($nama, $noReg, $lembaga, $noHp, $password)
{
    return "Selamat, *{$nama}*. Pendaftaran Anda berhasil dengan No. Registrasi {$noReg}

Login: https://daftar.mambaulhuda.ponpes.id/user/
No. HP: {$noHp}
Password: {$password}

Silakan login ke website untuk melengkapi data atau memantau status pendaftaran.";
}

/**
 * Template: Lupa Password - Reset Link
 */
function waTemplateLupaPassword($nama, $noHp, $resetLink)
{
    return "Halo {$nama},

Anda telah meminta reset password.

Klik link berikut untuk membuat password baru:
{$resetLink}

Link berlaku selama 1 jam.

Jika Anda tidak meminta reset password, abaikan pesan ini.";
}

/**
 * Template: Kekurangan Berkas
 */
function waTemplateKekuranganBerkas($nama, $noReg, $berkasKurang)
{
    $listBerkas = "";
    foreach ($berkasKurang as $berkas) {
        $listBerkas .= "- {$berkas}\n";
    }

    return "*PEMBERITAHUAN KELENGKAPAN BERKAS*

Assalamu'alaikum, {$nama}

Pendaftaran Anda dengan No. Registrasi {$noReg} memiliki berkas yang belum lengkap:

{$listBerkas}
Mohon segera lengkapi berkas melalui website.

Wassalamu'alaikum
SPMB Admin";
}
?>