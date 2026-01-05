<?php
// =============================================
// API: Submit Pendaftaran
// =============================================

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$conn = getConnection();

// ================================
// Phone duplicate check (GET)
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_phone'])) {
    $phone = sanitize($conn, $_GET['check_phone']);
    $stmt = $conn->prepare("SELECT id FROM pendaftaran WHERE no_hp_wali = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode(['exists' => $result->num_rows > 0]);
    $stmt->close();
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

// Get form data
$nama = strtoupper(sanitize($conn, $_POST['nama'] ?? ''));
$lembaga = sanitize($conn, $_POST['lembaga'] ?? '');
$nisn = sanitize($conn, $_POST['nisn'] ?? '');
$tempat_lahir = strtoupper(sanitize($conn, $_POST['tempat_lahir'] ?? ''));
$tanggal_lahir = sanitize($conn, $_POST['tanggal_lahir'] ?? '');
$jenis_kelamin = sanitize($conn, $_POST['jenis_kelamin'] ?? '');
$jumlah_saudara = intval($_POST['jumlah_saudara'] ?? 0);
$no_kk = sanitize($conn, $_POST['no_kk'] ?? '');
$nik = sanitize($conn, $_POST['nik'] ?? '');
$provinsi = strtoupper(sanitize($conn, $_POST['provinsi'] ?? ''));
$kota_kab = strtoupper(sanitize($conn, $_POST['kota_kab'] ?? ''));
$kecamatan = strtoupper(sanitize($conn, $_POST['kecamatan'] ?? ''));
$kelurahan_desa = strtoupper(sanitize($conn, $_POST['kelurahan_desa'] ?? ''));
$alamat = strtoupper(sanitize($conn, $_POST['alamat'] ?? ''));
$asal_sekolah = strtoupper(sanitize($conn, $_POST['asal_sekolah'] ?? ''));

// Prestasi
$prestasi = strtoupper(sanitize($conn, $_POST['prestasi'] ?? ''));
$tingkat_prestasi = strtoupper(sanitize($conn, $_POST['tingkat_prestasi'] ?? ''));
$juara = strtoupper(sanitize($conn, $_POST['juara'] ?? ''));

// Additional
$pip_pkh = strtoupper(sanitize($conn, $_POST['pip_pkh'] ?? ''));
$status_mukim = strtoupper(sanitize($conn, $_POST['status_mukim'] ?? ''));
$sumber_info = strtoupper(sanitize($conn, $_POST['sumber_info'] ?? ''));

// Data Ayah
$nama_ayah = strtoupper(sanitize($conn, $_POST['nama_ayah'] ?? ''));
$tempat_lahir_ayah = strtoupper(sanitize($conn, $_POST['tempat_lahir_ayah'] ?? ''));
$tanggal_lahir_ayah = sanitize($conn, $_POST['tanggal_lahir_ayah'] ?? '');
$nik_ayah = sanitize($conn, $_POST['nik_ayah'] ?? '');
$pekerjaan_ayah = strtoupper(sanitize($conn, $_POST['pekerjaan_ayah'] ?? ''));
$penghasilan_ayah = strtoupper(sanitize($conn, $_POST['penghasilan_ayah'] ?? ''));

// Data Ibu
$nama_ibu = strtoupper(sanitize($conn, $_POST['nama_ibu'] ?? ''));
$tempat_lahir_ibu = strtoupper(sanitize($conn, $_POST['tempat_lahir_ibu'] ?? ''));
$tanggal_lahir_ibu = sanitize($conn, $_POST['tanggal_lahir_ibu'] ?? '');
$nik_ibu = sanitize($conn, $_POST['nik_ibu'] ?? '');
$pekerjaan_ibu = strtoupper(sanitize($conn, $_POST['pekerjaan_ibu'] ?? ''));
$penghasilan_ibu = strtoupper(sanitize($conn, $_POST['penghasilan_ibu'] ?? ''));

// Kontak
$no_hp_wali = sanitize($conn, $_POST['no_hp_wali'] ?? '');

// Password
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validation
if (empty($nama) || empty($lembaga) || empty($jenis_kelamin) || empty($status_mukim) || empty($no_hp_wali)) {
    jsonResponse(false, 'Field wajib tidak boleh kosong');
}

// Password validation
if (empty($password) || strlen($password) < 6) {
    jsonResponse(false, 'Password harus minimal 6 karakter');
}
if ($password !== $password_confirm) {
    jsonResponse(false, 'Konfirmasi password tidak cocok');
}

// Check if phone already exists
$stmt = $conn->prepare("SELECT id FROM pendaftaran WHERE no_hp_wali = ?");
$stmt->bind_param("s", $no_hp_wali);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    jsonResponse(false, 'Nomor WA sudah terdaftar! Silakan login melalui tombol Profil di halaman utama.');
}
$stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Handle sertifikat file upload
$file_sertifikat = '';
if (isset($_FILES['file_sertifikat']) && $_FILES['file_sertifikat']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/sertifikat/';
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0777, true);

    $fileExt = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array($fileExt, $allowedExt)) {
        jsonResponse(false, 'File sertifikat harus berformat JPG, PNG, atau PDF');
    }
    if ($_FILES['file_sertifikat']['size'] > 5 * 1024 * 1024) {
        jsonResponse(false, 'Ukuran file sertifikat maksimal 5MB');
    }

    $newFileName = 'sertifikat_' . time() . '_' . uniqid() . '.' . $fileExt;
    if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $uploadDir . $newFileName)) {
        $file_sertifikat = $newFileName;
    }
}

// Handle document uploads (KK, KTP, Akta, Ijazah)
$docUploadDir = '../uploads/dokumen/';
if (!is_dir($docUploadDir))
    mkdir($docUploadDir, 0755, true);

$docFields = ['file_kk', 'file_ktp_ortu', 'file_akta', 'file_ijazah'];
$docValues = [];

foreach ($docFields as $field) {
    $docValues[$field] = '';
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowedDocExt = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowedDocExt)) {
            jsonResponse(false, "File $field harus dalam format PDF, JPG, atau PNG");
        }
        if ($_FILES[$field]['size'] > 2 * 1024 * 1024) {
            jsonResponse(false, "Ukuran file $field maksimal 2MB");
        }

        $docFileName = $field . '_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $docUploadDir . $docFileName)) {
            $docValues[$field] = $docFileName;
        }
    }
}

// Prepare empty values for nullable ENUM fields
$tingkat_prestasi = !empty($tingkat_prestasi) ? $tingkat_prestasi : null;
$juara = !empty($juara) ? $juara : null;

// Insert to database
$sql = "INSERT INTO pendaftaran (
    nama, lembaga, nisn, tempat_lahir, tanggal_lahir, jenis_kelamin, jumlah_saudara,
    no_kk, nik, provinsi, kota_kab, kecamatan, kelurahan_desa, alamat, asal_sekolah, 
    prestasi, tingkat_prestasi, juara, file_sertifikat,
    pip_pkh, status_mukim, sumber_info, nama_ayah, tempat_lahir_ayah, tanggal_lahir_ayah,
    nik_ayah, pekerjaan_ayah, penghasilan_ayah, nama_ibu, tempat_lahir_ibu, tanggal_lahir_ibu,
    nik_ibu, pekerjaan_ibu, penghasilan_ibu, no_hp_wali, password, file_kk, file_ktp_ortu, file_akta, file_ijazah
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    jsonResponse(false, 'Prepare failed: ' . $conn->error);
}

// 40 parameters: s=string, i=integer
// nama, lembaga, nisn, tempat_lahir, tanggal_lahir, jenis_kelamin, jumlah_saudara(i),
// no_kk, nik, provinsi, kota_kab, kecamatan, kelurahan_desa, alamat, asal_sekolah, 
// prestasi, tingkat_prestasi, juara, file_sertifikat,
// pip_pkh, status_mukim, sumber_info, nama_ayah, tempat_lahir_ayah, tanggal_lahir_ayah,
// nik_ayah, pekerjaan_ayah, penghasilan_ayah, nama_ibu, tempat_lahir_ibu, tanggal_lahir_ibu,
// nik_ibu, pekerjaan_ibu, penghasilan_ibu, no_hp_wali, password, file_kk, file_ktp_ortu, file_akta, file_ijazah
$stmt->bind_param(
    "ssssssisssssssssssssssssssssssssssssssss",
    $nama,
    $lembaga,
    $nisn,
    $tempat_lahir,
    $tanggal_lahir,
    $jenis_kelamin,
    $jumlah_saudara,
    $no_kk,
    $nik,
    $provinsi,
    $kota_kab,
    $kecamatan,
    $kelurahan_desa,
    $alamat,
    $asal_sekolah,
    $prestasi,
    $tingkat_prestasi,
    $juara,
    $file_sertifikat,
    $pip_pkh,
    $status_mukim,
    $sumber_info,
    $nama_ayah,
    $tempat_lahir_ayah,
    $tanggal_lahir_ayah,
    $nik_ayah,
    $pekerjaan_ayah,
    $penghasilan_ayah,
    $nama_ibu,
    $tempat_lahir_ibu,
    $tanggal_lahir_ibu,
    $nik_ibu,
    $pekerjaan_ibu,
    $penghasilan_ibu,
    $no_hp_wali,
    $hashed_password,
    $docValues['file_kk'],
    $docValues['file_ktp_ortu'],
    $docValues['file_akta'],
    $docValues['file_ijazah']
);

if ($stmt->execute()) {
    $insertId = $conn->insert_id;

    // Generate registration number: sequence.MMYY (e.g., 001.1226)
    $countResult = $conn->query("SELECT COUNT(*) as total FROM pendaftaran");
    $count = $countResult->fetch_assoc()['total'];
    $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);
    $monthYear = date('my'); // e.g., 1226 for December 2026
    $noRegistrasi = $sequence . '.' . $monthYear;

    // Store no_registrasi in database
    $updateReg = $conn->prepare("UPDATE pendaftaran SET no_registrasi = ? WHERE id = ?");
    $updateReg->bind_param("si", $noRegistrasi, $insertId);
    $updateReg->execute();

    // =============================================
    // FITUR 3: Kirim WhatsApp Selamat Pendaftaran
    // =============================================
    require_once 'whatsapp.php';
    $waMessage = waTemplatePendaftaran($nama, $noRegistrasi, $lembaga, $no_hp_wali, $password);
    sendWhatsApp($no_hp_wali, $waMessage);

    jsonResponse(true, 'Pendaftaran berhasil! Silakan login menggunakan No. HP dan password Anda.', [
        'id' => $insertId,
        'no_registrasi' => $noRegistrasi,
        'nama' => $nama,
        'no_hp_wali' => $no_hp_wali
    ]);
} else {
    jsonResponse(false, 'Gagal menyimpan data: ' . $stmt->error);
}

$stmt->close();
$conn->close();
?>