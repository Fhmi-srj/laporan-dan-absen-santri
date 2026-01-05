<?php
/**
 * Helper Functions
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/config.php';

/**
 * Get pendaftaran data from SPMB for list of pendaftaran_ids
 * Returns associative array: pendaftaran_id => pendaftaran_data
 */
function getPendaftaranData($pendaftaranIds)
{
    if (empty($pendaftaranIds))
        return [];

    $spmb = getSPMBDB();
    $placeholders = str_repeat('?,', count($pendaftaranIds) - 1) . '?';
    $stmt = $spmb->prepare("SELECT * FROM pendaftaran WHERE id IN ($placeholders)");
    $stmt->execute($pendaftaranIds);

    $result = [];
    while ($row = $stmt->fetch()) {
        $result[$row['id']] = $row;
    }
    return $result;
}

/**
 * Enrich siswa data with pendaftaran data from SPMB
 * Adds nama_lengkap, alamat, no_wa, no_wa_wali, etc. to each siswa
 */
function enrichSiswaWithSPMB(&$siswaList)
{
    if (empty($siswaList))
        return;

    $pendaftaranIds = array_column($siswaList, 'pendaftaran_id');
    $pendaftaranData = getPendaftaranData($pendaftaranIds);

    foreach ($siswaList as &$s) {
        $p = $pendaftaranData[$s['pendaftaran_id']] ?? [];
        $s['nama_lengkap'] = $p['nama'] ?? '-';
        $s['no_wa'] = $p['no_hp_wali'] ?? '-';
        $s['no_wa_wali'] = $p['no_hp_wali'] ?? '-';
        $s['alamat'] = isset($p['alamat']) ? trim($p['alamat'] . ', ' . ($p['kelurahan_desa'] ?? '') . ', ' . ($p['kecamatan'] ?? '') . ', ' . ($p['kota_kab'] ?? ''), ', ') : '-';
        $s['jenis_kelamin'] = $p['jenis_kelamin'] ?? '-';
        $s['lembaga'] = $p['lembaga'] ?? '-';
        $s['nik'] = $p['nik'] ?? '-';
        $s['nisn'] = $p['nisn'] ?? '-';
        $s['tempat_lahir'] = $p['tempat_lahir'] ?? '-';
        $s['tanggal_lahir'] = $p['tanggal_lahir'] ?? '-';
        $s['nama_ayah'] = $p['nama_ayah'] ?? '-';
        $s['nama_ibu'] = $p['nama_ibu'] ?? '-';
        $s['status_spmb'] = $p['status'] ?? '-';
    }
    unset($s);
}

/**
 * Get single siswa with SPMB data by siswa ID
 */
function getSiswaById($siswaId)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->execute([$siswaId]);
    $siswa = $stmt->fetch();

    if ($siswa) {
        $siswaList = [$siswa];
        enrichSiswaWithSPMB($siswaList);
        return $siswaList[0];
    }
    return null;
}


/**
 * Format nomor telepon untuk WhatsApp (62xxx)
 */
function formatPhoneNumber($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (empty($phone))
        return null;

    if (str_starts_with($phone, '0')) {
        $phone = '62' . substr($phone, 1);
    }
    if (!str_starts_with($phone, '62')) {
        $phone = '62' . $phone;
    }
    return $phone;
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal($datetime, $format = 'd M Y H:i')
{
    if (empty($datetime))
        return '-';
    $date = new DateTime($datetime);
    return $date->format($format);
}

/**
 * Format tanggal untuk input datetime-local
 */
function formatDateTimeLocal($datetime)
{
    if (empty($datetime))
        return '';
    $date = new DateTime($datetime);
    return $date->format('Y-m-d\TH:i');
}

/**
 * Kirim notifikasi WhatsApp
 */
function sendWhatsApp($phone, $message)
{
    $phoneNumber = formatPhoneNumber($phone);
    if (empty($phoneNumber)) {
        return ['success' => false, 'message' => 'Nomor tidak valid'];
    }

    $data = [
        'api_key' => WA_API_KEY,
        'sender' => WA_SENDER,
        'number' => $phoneNumber,
        'message' => $message
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => WA_API_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'message' => 'CURL Error: ' . $error];
    }

    $result = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        if (isset($result['status']) && $result['status'] === false) {
            return ['success' => false, 'message' => $result['msg'] ?? 'Gagal mengirim'];
        }
        return ['success' => true, 'message' => 'Pesan terkirim'];
    }

    return ['success' => false, 'message' => 'HTTP Error: ' . $httpCode];
}

/**
 * Kirim notifikasi WhatsApp dengan Gambar
 */
function sendWhatsAppWithImage($phone, $message, $imagePath)
{
    $phoneNumber = formatPhoneNumber($phone);
    if (empty($phoneNumber)) {
        return ['success' => false, 'message' => 'Nomor tidak valid'];
    }

    // Check if image exists
    $fullImagePath = BASE_PATH . '/uploads/' . $imagePath;
    if (!file_exists($fullImagePath)) {
        // Fallback to text-only message
        return sendWhatsApp($phone, $message);
    }

    // Get image URL (must be publicly accessible)
    $imageUrl = rtrim(APP_URL, '/') . '/uploads/' . $imagePath;

    $data = [
        'api_key' => WA_API_KEY,
        'sender' => WA_SENDER,
        'number' => $phoneNumber,
        'media_type' => 'image',
        'caption' => $message,  // Caption for image (was: message)
        'url' => $imageUrl      // Direct URL to image (was: media_url)
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => str_replace('send-message', 'send-media', WA_API_URL), // Use media endpoint
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'message' => 'CURL Error: ' . $error];
    }

    $result = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        if (isset($result['status']) && $result['status'] === false) {
            return ['success' => false, 'message' => $result['msg'] ?? 'Gagal mengirim'];
        }
        return ['success' => true, 'message' => 'Pesan dengan gambar terkirim'];
    }

    return ['success' => false, 'message' => 'HTTP Error: ' . $httpCode];
}

/**
 * Upload file dengan validasi dan kompresi gambar
 */
function uploadFile($file, $folder = 'bukti_aktivitas')
{
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
    }

    // Validasi ukuran
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Ukuran file terlalu besar (max 5MB)');
    }

    // Validasi tipe file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipe file tidak diizinkan');
    }

    // Buat folder jika belum ada
    $uploadDir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate nama file unik (selalu simpan sebagai jpg untuk kompresi)
    $filename = bin2hex(random_bytes(16)) . '.jpg';
    $filepath = $uploadDir . $filename;

    // Kompresi gambar
    $compressed = compressImage($file['tmp_name'], $filepath, $mimeType, 80, 1200);

    if (!$compressed) {
        // Fallback jika kompresi gagal, simpan file asli
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $filepath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Gagal mengupload file');
        }
    }

    return $folder . '/' . $filename;
}

/**
 * Kompresi gambar dengan resize dan quality adjustment
 */
function compressImage($source, $destination, $mimeType, $quality = 80, $maxWidth = 1200)
{
    // Buat image dari source berdasarkan tipe
    switch ($mimeType) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    // Get original dimensions
    $origWidth = imagesx($image);
    $origHeight = imagesy($image);

    // Calculate new dimensions (resize if too large)
    if ($origWidth > $maxWidth) {
        $ratio = $maxWidth / $origWidth;
        $newWidth = $maxWidth;
        $newHeight = (int) ($origHeight * $ratio);
    } else {
        $newWidth = $origWidth;
        $newHeight = $origHeight;
    }

    // Create new resized image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG
    if ($mimeType === 'image/png') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Resize
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    // Save as JPEG with compression
    $result = imagejpeg($newImage, $destination, $quality);

    // Free memory
    imagedestroy($image);
    imagedestroy($newImage);

    return $result;
}

/**
 * Hapus file upload
 */
function deleteFile($path)
{
    if (empty($path))
        return;
    $fullPath = UPLOAD_PATH . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

/**
 * Escape output HTML
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * JSON Response
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect dengan flash message
 */
function redirectWith($url, $type, $message)
{
    setFlash($type, $message);
    header('Location: ' . $url);
    exit;
}

/**
 * Hitung jarak antara 2 koordinat (dalam meter)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2))
        return 0;

    $earthRadius = 6371000; // dalam meter
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    return $angle * $earthRadius;
}

/**
 * Generate Device Fingerprint Hash
 */
function generateDeviceHash()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

/**
 * Get kategori label dengan warna
 */
function getKategoriInfo($kategori)
{
    $map = [
        'sakit' => ['label' => 'Sakit', 'color' => '#ef4444', 'bg' => '#fef2f2', 'icon' => 'fas fa-procedures'],
        'izin_keluar' => ['label' => 'Izin Keluar', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'icon' => 'fas fa-sign-out-alt'],
        'izin_pulang' => ['label' => 'Izin Pulang', 'color' => '#f97316', 'bg' => '#fff7ed', 'icon' => 'fas fa-home'],
        'sambangan' => ['label' => 'Sambangan', 'color' => '#10b981', 'bg' => '#ecfdf5', 'icon' => 'fas fa-users'],
        'pelanggaran' => ['label' => 'Pelanggaran', 'color' => '#db2777', 'bg' => '#fdf2f8', 'icon' => 'fas fa-exclamation-triangle'],
        'paket' => ['label' => 'Paket', 'color' => '#3b82f6', 'bg' => '#eff6ff', 'icon' => 'fas fa-box-open'],
        'hafalan' => ['label' => 'Hafalan', 'color' => '#3b82f6', 'bg' => '#dbeafe', 'icon' => 'fas fa-quran'],
    ];
    return $map[$kategori] ?? ['label' => $kategori, 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fas fa-info'];
}
