<?php
/**
 * API: Store Attendance
 * Process attendance from QR scan or RFID
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$pdo = getDB();

try {
    $nomorInduk = $_POST['nomor_induk'] ?? null;
    $rfid = $_POST['no_kartu_rfid'] ?? null;
    $jadwalId = $_POST['jadwal_id'] ?? null;
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    if (!$jadwalId) {
        throw new Exception('Jadwal tidak dipilih');
    }

    // Find siswa by NISN or RFID from data_induk
    $siswa = null;
    if ($nomorInduk) {
        $stmt = $pdo->prepare("SELECT * FROM data_induk WHERE nisn = ? AND deleted_at IS NULL");
        $stmt->execute([$nomorInduk]);
        $siswa = $stmt->fetch();
    } elseif ($rfid) {
        $stmt = $pdo->prepare("SELECT * FROM data_induk WHERE nomor_rfid = ? AND deleted_at IS NULL");
        $stmt->execute([$rfid]);
        $siswa = $stmt->fetch();
    }

    if (!$siswa) {
        throw new Exception('Siswa tidak ditemukan dalam sistem');
    }

    // Get jadwal
    $stmt = $pdo->prepare("SELECT * FROM jadwal_absens WHERE id = ?");
    $stmt->execute([$jadwalId]);
    $jadwal = $stmt->fetch();

    if (!$jadwal) {
        throw new Exception('Jadwal absen tidak ditemukan');
    }

    $today = date('Y-m-d');
    $currentTime = new DateTime();
    $currentTimeStr = $currentTime->format('H:i:s');

    // Check if already attended for this jadwal today
    $stmt = $pdo->prepare("SELECT * FROM attendances WHERE user_id = ? AND attendance_date = ? AND jadwal_id = ?");
    $stmt->execute([$siswa['id'], $today, $jadwal['id']]);
    if ($stmt->fetch()) {
        // Return error with siswa name so popup shows who tried to scan
        echo json_encode([
            'success' => false,
            'message' => 'Sudah absen untuk jadwal "' . $jadwal['name'] . '" hari ini',
            'siswa_name' => $siswa['nama_lengkap'],
            'siswa_kelas' => $siswa['kelas'] ?? '',
            'already_attended' => true
        ]);
        exit;
    }

    // Parse jadwal times
    $startTime = new DateTime($jadwal['start_time']);
    $scheduledTime = new DateTime($jadwal['scheduled_time']);
    $endTime = new DateTime($jadwal['end_time']);
    $lateTolerance = (int) $jadwal['late_tolerance_minutes'];
    $eventType = $jadwal['type'];

    // Check if within time window
    $currentTimeOnly = new DateTime($currentTimeStr);
    if ($currentTimeOnly < $startTime) {
        throw new Exception('Belum waktunya absen. Sesi dibuka jam ' . $startTime->format('H:i'));
    }
    if ($currentTimeOnly > $endTime) {
        throw new Exception('Sesi absen sudah ditutup (jam ' . $endTime->format('H:i') . ')');
    }

    $status = 'hadir';
    $minutesLate = 0;
    $message = '';

    // Calculate lateness for all jadwal types
    $lateThresholdTime = clone $scheduledTime;
    $lateThresholdTime->modify("+{$lateTolerance} minutes");

    if ($currentTimeOnly > $scheduledTime) {
        $interval = $scheduledTime->diff($currentTimeOnly);
        $minutesLate = ($interval->h * 60) + $interval->i;
    }

    if ($minutesLate > $lateTolerance) {
        $status = 'terlambat';
        $message = "Absen ({$jadwal['name']}) berhasil (Terlambat {$minutesLate} menit)";
    } else {
        $minutesLate = 0;
        $message = "Absen ({$jadwal['name']}) berhasil (Tepat Waktu)";
    }

    // Insert attendance record
    $stmt = $pdo->prepare("
        INSERT INTO attendances (user_id, jadwal_id, status, attendance_date, attendance_time, minutes_late, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $siswa['id'],
        $jadwal['id'],
        $status,
        $today,
        $currentTimeStr,
        $minutesLate
    ]);

    // Send WhatsApp notification to wali
    if (!empty($siswa['no_wa_wali'])) {
        if ($status === 'terlambat') {
            $waMessage = "⏰ *{$jadwal['name']} (TERLAMBAT)*\n\n*Nama:* {$siswa['nama_lengkap']}\n*Waktu:* {$currentTime->format('H:i:s')} WIB\n*Keterangan:* Terlambat {$minutesLate} menit\n\nTerima kasih.";
        } else {
            $waMessage = "✅ *{$jadwal['name']}*\n\n*Nama:* {$siswa['nama_lengkap']}\n*Waktu:* {$currentTime->format('H:i:s')} WIB\n*Status:* Tepat Waktu\n\nTerima kasih.";
        }
        sendWhatsApp($siswa['no_wa_wali'], $waMessage);
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'siswa_name' => $siswa['nama_lengkap'],
        'siswa_kelas' => $siswa['kelas'] ?? '',
        'siswa_gender' => $siswa['jenis_kelamin'] ?? '',
        'siswa_rfid' => $siswa['nomor_rfid'] ?? '',
        'status' => $status
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
