<?php
/**
 * Helper Functions
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/config.php';

/**
 * Get single santri by ID from data_induk table
 */
function getSiswaById($siswaId)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM data_induk WHERE id = ?");
    $stmt->execute([$siswaId]);
    return $stmt->fetch();
}

/**
 * Get all santri with optional filters
 */
function getAllSiswa($filters = [])
{
    $pdo = getDB();
    $sql = "SELECT * FROM data_induk WHERE 1=1";
    $params = [];

    if (!empty($filters['kelas'])) {
        $sql .= " AND kelas = ?";
        $params[] = $filters['kelas'];
    }
    if (!empty($filters['status'])) {
        $sql .= " AND status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (nama_lengkap LIKE ? OR nisn LIKE ? OR nik LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    $sql .= " ORDER BY nama_lengkap ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Search santri by keyword (nama, nisn, nik)
 */
function searchSiswa($keyword, $limit = 10)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT id, nama_lengkap, nisn, kelas, alamat, no_wa_wali, kabupaten, kecamatan
        FROM data_induk 
        WHERE nama_lengkap LIKE ? OR nisn LIKE ? OR nik LIKE ?
        ORDER BY nama_lengkap ASC
        LIMIT ?
    ");
    $search = '%' . $keyword . '%';
    $stmt->execute([$search, $search, $search, $limit]);
    return $stmt->fetchAll();
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

/**
 * Get device name from User-Agent
 */
function getDeviceName()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Mobile devices
    if (preg_match('/iPhone/', $userAgent))
        return 'iPhone';
    if (preg_match('/iPad/', $userAgent))
        return 'iPad';
    if (preg_match('/SM-[A-Z0-9]+/i', $userAgent, $m))
        return 'Samsung ' . $m[0];
    if (preg_match('/SAMSUNG|Galaxy/i', $userAgent))
        return 'Samsung Galaxy';
    if (preg_match('/Pixel/', $userAgent))
        return 'Google Pixel';
    if (preg_match('/OPPO|CPH/i', $userAgent))
        return 'OPPO';
    if (preg_match('/vivo/i', $userAgent))
        return 'Vivo';
    if (preg_match('/Xiaomi|Redmi|POCO/i', $userAgent))
        return 'Xiaomi';
    if (preg_match('/Huawei/i', $userAgent))
        return 'Huawei';
    if (preg_match('/realme/i', $userAgent))
        return 'Realme';
    if (preg_match('/Android/', $userAgent))
        return 'Android Device';

    // Desktop/Laptop
    if (preg_match('/Macintosh/', $userAgent))
        return 'MacBook/iMac';
    if (preg_match('/Windows NT 10/', $userAgent))
        return 'Windows PC';
    if (preg_match('/Windows NT/', $userAgent))
        return 'Windows PC';
    if (preg_match('/Linux/', $userAgent))
        return 'Linux PC';
    if (preg_match('/CrOS/', $userAgent))
        return 'Chromebook';

    return 'Unknown Device';
}

/**
 * Log user activity to database
 */
function logActivity($action, $tableName = null, $recordId = null, $recordName = null, $oldData = null, $newData = null, $description = null)
{
    try {
        $pdo = getDB();
        $user = getCurrentUser();

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (user_id, user_name, device_name, ip_address, action, table_name, record_id, record_name, old_data, new_data, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user['id'] ?? 0,
            $user['name'] ?? 'System',
            getDeviceName(),
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $action,
            $tableName,
            $recordId,
            $recordName,
            $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            $description
        ]);

        return true;
    } catch (Exception $e) {
        // Silent fail - don't break the app if logging fails
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get system setting value
 */
function getSetting($key, $default = null)
{
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update system setting value
 */
function setSetting($key, $value)
{
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Soft delete a record
 */
function softDelete($table, $id)
{
    try {
        $pdo = getDB();
        $user = getCurrentUser();
        $stmt = $pdo->prepare("UPDATE $table SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        return $stmt->execute([$user['id'] ?? null, $id]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Restore a soft-deleted record
 */
function restoreRecord($table, $id)
{
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE $table SET deleted_at = NULL, deleted_by = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Permanently delete a record
 */
function permanentDelete($table, $id)
{
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        return false;
    }
}
