<?php
require_once '../api/config.php';
requireLogin();

$conn = getConnection();

// Get filter parameters (same as pendaftaran.php)
$status = $_GET['status'] ?? '';
$lembaga = $_GET['lembaga'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM pendaftaran WHERE 1=1";
$params = [];
$types = "";

if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($lembaga) {
    $sql .= " AND lembaga = ?";
    $params[] = $lembaga;
    $types .= "s";
}

if ($search) {
    $sql .= " AND (nama LIKE ? OR nisn LIKE ? OR no_hp_wali LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Set headers for Excel download
$filename = "Data_Pendaftaran_" . date('Y-m-d_His') . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Log activity
logActivity('EXPORT', 'Export data pendaftaran ke Excel');

// Output Excel content
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>';
echo '<body>';
echo '<table border="1">';

// Header row
echo '<tr style="background-color:#E67E22; color:white; font-weight:bold;">';
echo '<th>No</th>';
echo '<th>No. Registrasi</th>';
echo '<th>Nama Lengkap</th>';
echo '<th>Lembaga</th>';
echo '<th>NISN</th>';
echo '<th>NIK</th>';
echo '<th>No KK</th>';
echo '<th>Tempat Lahir</th>';
echo '<th>Tanggal Lahir</th>';
echo '<th>Jenis Kelamin</th>';
echo '<th>Jumlah Saudara</th>';
echo '<th>Provinsi</th>';
echo '<th>Kota/Kabupaten</th>';
echo '<th>Kecamatan</th>';
echo '<th>Kelurahan/Desa</th>';
echo '<th>Detail Alamat</th>';
echo '<th>Asal Sekolah</th>';
echo '<th>Status Mukim</th>';
echo '<th>PIP/PKH</th>';
echo '<th>Sumber Info</th>';
echo '<th>Prestasi</th>';
echo '<th>Tingkat Prestasi</th>';
echo '<th>Juara</th>';
echo '<th>Nama Ayah</th>';
echo '<th>NIK Ayah</th>';
echo '<th>Tempat Lahir Ayah</th>';
echo '<th>Tanggal Lahir Ayah</th>';
echo '<th>Pekerjaan Ayah</th>';
echo '<th>Penghasilan Ayah</th>';
echo '<th>Nama Ibu</th>';
echo '<th>NIK Ibu</th>';
echo '<th>Tempat Lahir Ibu</th>';
echo '<th>Tanggal Lahir Ibu</th>';
echo '<th>Pekerjaan Ibu</th>';
echo '<th>Penghasilan Ibu</th>';
echo '<th>No. HP Wali</th>';
echo '<th>Status</th>';
echo '<th>Tanggal Daftar</th>';
echo '</tr>';

// Data rows
$no = 1;
while ($row = $result->fetch_assoc()) {
    $jk = $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan';
    $statusText = ['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'][$row['status']] ?? $row['status'];

    echo '<tr>';
    echo '<td>' . $no++ . '</td>';
    echo '<td>' . htmlspecialchars($row['no_registrasi'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
    echo '<td>' . htmlspecialchars($row['lembaga']) . '</td>';
    echo '<td>' . htmlspecialchars($row['nisn']) . '</td>';
    echo '<td style="mso-number-format:\@">' . htmlspecialchars($row['nik']) . '</td>';
    echo '<td style="mso-number-format:\@">' . htmlspecialchars($row['no_kk']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tempat_lahir']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tanggal_lahir']) . '</td>';
    echo '<td>' . $jk . '</td>';
    echo '<td>' . htmlspecialchars($row['jumlah_saudara']) . '</td>';
    echo '<td>' . htmlspecialchars($row['provinsi'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['kota_kab'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['kecamatan'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['kelurahan_desa'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['alamat']) . '</td>';
    echo '<td>' . htmlspecialchars($row['asal_sekolah']) . '</td>';
    echo '<td>' . htmlspecialchars($row['status_mukim']) . '</td>';
    echo '<td>' . htmlspecialchars($row['pip_pkh']) . '</td>';
    echo '<td>' . htmlspecialchars($row['sumber_info']) . '</td>';
    echo '<td>' . htmlspecialchars($row['prestasi'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['tingkat_prestasi'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['juara'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['nama_ayah']) . '</td>';
    echo '<td style="mso-number-format:\@">' . htmlspecialchars($row['nik_ayah']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tempat_lahir_ayah']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tanggal_lahir_ayah']) . '</td>';
    echo '<td>' . htmlspecialchars($row['pekerjaan_ayah']) . '</td>';
    echo '<td>' . htmlspecialchars($row['penghasilan_ayah']) . '</td>';
    echo '<td>' . htmlspecialchars($row['nama_ibu']) . '</td>';
    echo '<td style="mso-number-format:\@">' . htmlspecialchars($row['nik_ibu']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tempat_lahir_ibu']) . '</td>';
    echo '<td>' . htmlspecialchars($row['tanggal_lahir_ibu']) . '</td>';
    echo '<td>' . htmlspecialchars($row['pekerjaan_ibu']) . '</td>';
    echo '<td>' . htmlspecialchars($row['penghasilan_ibu']) . '</td>';
    echo '<td style="mso-number-format:\@">' . htmlspecialchars($row['no_hp_wali']) . '</td>';
    echo '<td>' . $statusText . '</td>';
    echo '<td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body></html>';

$conn->close();
