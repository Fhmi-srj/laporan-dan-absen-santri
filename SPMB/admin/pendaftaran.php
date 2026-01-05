<?php
require_once '../api/config.php';
requireLogin();

$conn = getConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf(); // CSRF protection

    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        // Get name for logging
        $stmt = $conn->prepare("SELECT nama, file_sertifikat FROM pendaftaran WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Delete file
            if ($row['file_sertifikat'] && file_exists('../uploads/sertifikat/' . $row['file_sertifikat'])) {
                unlink('../uploads/sertifikat/' . $row['file_sertifikat']);
            }

            $stmt = $conn->prepare("DELETE FROM pendaftaran WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            logActivity('DELETE', 'Menghapus pendaftaran: ' . $row['nama']);
        }
        header('Location: pendaftaran.php?msg=deleted');
        exit;
    }

    if ($action === 'update_status' && $id > 0) {
        $status = sanitize($conn, $_POST['status'] ?? '');

        // Get name for logging
        $stmt = $conn->prepare("SELECT nama FROM pendaftaran WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $nama = $stmt->get_result()->fetch_assoc()['nama'] ?? '';

        $stmt = $conn->prepare("UPDATE pendaftaran SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        logActivity('STATUS_UPDATE', "Ubah status pendaftaran '$nama' menjadi $status");
        header('Location: pendaftaran.php?msg=updated');
        exit;
    }

    // Update catatan admin (message to user)
    if ($action === 'update_catatan' && $id > 0) {
        $catatan = sanitize($conn, $_POST['catatan_admin'] ?? '');

        // Get name for logging
        $stmt = $conn->prepare("SELECT nama FROM pendaftaran WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $nama = $stmt->get_result()->fetch_assoc()['nama'] ?? '';

        $stmt = $conn->prepare("UPDATE pendaftaran SET catatan_admin = ?, catatan_updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $catatan, $id);
        $stmt->execute();

        logActivity('CATATAN_UPDATE', "Mengupdate catatan untuk: $nama");
        header('Location: pendaftaran.php?msg=updated');
        exit;
    }

    // Update all data
    if ($action === 'update_data' && $id > 0) {
        // Data Siswa
        $nama = sanitize($conn, $_POST['nama'] ?? '');
        $lembagaVal = sanitize($conn, $_POST['lembaga'] ?? '');
        $nisn = sanitize($conn, $_POST['nisn'] ?? '');
        $nik = sanitize($conn, $_POST['nik'] ?? '');
        $no_kk = sanitize($conn, $_POST['no_kk'] ?? '');
        $tempat_lahir = sanitize($conn, $_POST['tempat_lahir'] ?? '');
        $tanggal_lahir = sanitize($conn, $_POST['tanggal_lahir'] ?? '');
        $jenis_kelamin = sanitize($conn, $_POST['jenis_kelamin'] ?? '');
        $jumlah_saudara = intval($_POST['jumlah_saudara'] ?? 0);
        $provinsi = sanitize($conn, $_POST['provinsi'] ?? '');
        $kota_kab = sanitize($conn, $_POST['kota_kab'] ?? '');
        $kecamatan = sanitize($conn, $_POST['kecamatan'] ?? '');
        $kelurahan_desa = sanitize($conn, $_POST['kelurahan_desa'] ?? '');
        $alamat = sanitize($conn, $_POST['alamat'] ?? '');
        $asal_sekolah = sanitize($conn, $_POST['asal_sekolah'] ?? '');
        $status_mukim = sanitize($conn, $_POST['status_mukim'] ?? '');
        $pip_pkh = sanitize($conn, $_POST['pip_pkh'] ?? '');
        $sumber_info = sanitize($conn, $_POST['sumber_info'] ?? '');

        // Prestasi
        $prestasi = sanitize($conn, $_POST['prestasi'] ?? '');
        $tingkat_prestasi = !empty($_POST['tingkat_prestasi']) ? sanitize($conn, $_POST['tingkat_prestasi']) : null;
        $juara = !empty($_POST['juara']) ? sanitize($conn, $_POST['juara']) : null;

        // Data Ayah
        $nama_ayah = sanitize($conn, $_POST['nama_ayah'] ?? '');
        $nik_ayah = sanitize($conn, $_POST['nik_ayah'] ?? '');
        $tempat_lahir_ayah = sanitize($conn, $_POST['tempat_lahir_ayah'] ?? '');
        $tanggal_lahir_ayah = sanitize($conn, $_POST['tanggal_lahir_ayah'] ?? '');
        $pekerjaan_ayah = sanitize($conn, $_POST['pekerjaan_ayah'] ?? '');
        $penghasilan_ayah = sanitize($conn, $_POST['penghasilan_ayah'] ?? '');

        // Data Ibu
        $nama_ibu = sanitize($conn, $_POST['nama_ibu'] ?? '');
        $nik_ibu = sanitize($conn, $_POST['nik_ibu'] ?? '');
        $tempat_lahir_ibu = sanitize($conn, $_POST['tempat_lahir_ibu'] ?? '');
        $tanggal_lahir_ibu = sanitize($conn, $_POST['tanggal_lahir_ibu'] ?? '');
        $pekerjaan_ibu = sanitize($conn, $_POST['pekerjaan_ibu'] ?? '');
        $penghasilan_ibu = sanitize($conn, $_POST['penghasilan_ibu'] ?? '');

        // Kontak & Status
        $no_hp_wali = sanitize($conn, $_POST['no_hp_wali'] ?? '');
        $statusVal = sanitize($conn, $_POST['status'] ?? 'pending');

        // Catatan Admin
        $catatan_admin = sanitize($conn, $_POST['catatan_admin'] ?? '');

        $stmt = $conn->prepare("UPDATE pendaftaran SET 
            nama=?, lembaga=?, nisn=?, nik=?, no_kk=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?,
            jumlah_saudara=?, provinsi=?, kota_kab=?, kecamatan=?, kelurahan_desa=?, alamat=?, asal_sekolah=?, 
            status_mukim=?, pip_pkh=?, sumber_info=?,
            prestasi=?, tingkat_prestasi=?, juara=?,
            nama_ayah=?, nik_ayah=?, tempat_lahir_ayah=?, tanggal_lahir_ayah=?, pekerjaan_ayah=?, penghasilan_ayah=?,
            nama_ibu=?, nik_ibu=?, tempat_lahir_ibu=?, tanggal_lahir_ibu=?, pekerjaan_ibu=?, penghasilan_ibu=?,
            no_hp_wali=?, status=?, catatan_admin=?, catatan_updated_at=NOW()
            WHERE id=?");
        // 36 columns + 1 id = 37 params (jumlah_saudara=i at pos 9, id=i at pos 37)
        $stmt->bind_param(
            "ssssssssssssssssssssssssssssssssssssi",
            $nama,
            $lembagaVal,
            $nisn,
            $nik,
            $no_kk,
            $tempat_lahir,
            $tanggal_lahir,
            $jenis_kelamin,
            $jumlah_saudara,
            $provinsi,
            $kota_kab,
            $kecamatan,
            $kelurahan_desa,
            $alamat,
            $asal_sekolah,
            $status_mukim,
            $pip_pkh,
            $sumber_info,
            $prestasi,
            $tingkat_prestasi,
            $juara,
            $nama_ayah,
            $nik_ayah,
            $tempat_lahir_ayah,
            $tanggal_lahir_ayah,
            $pekerjaan_ayah,
            $penghasilan_ayah,
            $nama_ibu,
            $nik_ibu,
            $tempat_lahir_ibu,
            $tanggal_lahir_ibu,
            $pekerjaan_ibu,
            $penghasilan_ibu,
            $no_hp_wali,
            $statusVal,
            $catatan_admin,
            $id
        );
        $stmt->execute();

        // Reset password if provided
        $newPassword = $_POST['new_password'] ?? '';
        if (strlen($newPassword) >= 6) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $pwStmt = $conn->prepare("UPDATE pendaftaran SET password = ? WHERE id = ?");
            $pwStmt->bind_param("si", $hashedPassword, $id);
            $pwStmt->execute();
            logActivity('RESET_PASSWORD', "Reset password untuk: $nama");
        }

        // Handle file uploads - Dokumen
        $uploadDir = '../uploads/dokumen/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);

        $fileFields = ['file_kk', 'file_ktp_ortu', 'file_akta', 'file_ijazah'];
        foreach ($fileFields as $field) {
            if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                $allowedDocExt = ['pdf', 'jpg', 'jpeg', 'png'];
                if (in_array($ext, $allowedDocExt) && $_FILES[$field]['size'] <= 2 * 1024 * 1024) {
                    // Get old filename to delete
                    $oldStmt = $conn->prepare("SELECT $field FROM pendaftaran WHERE id = ?");
                    $oldStmt->bind_param("i", $id);
                    $oldStmt->execute();
                    $oldFile = $oldStmt->get_result()->fetch_assoc()[$field] ?? '';

                    // Delete old file
                    if ($oldFile && file_exists($uploadDir . $oldFile)) {
                        unlink($uploadDir . $oldFile);
                    }

                    // Save new file
                    $filename = $id . '_' . $field . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $filename)) {
                        $updateStmt = $conn->prepare("UPDATE pendaftaran SET $field = ? WHERE id = ?");
                        $updateStmt->bind_param("si", $filename, $id);
                        $updateStmt->execute();
                    }
                }
            }
        }

        // Handle file_sertifikat upload (different folder)
        $sertifikatDir = '../uploads/sertifikat/';
        if (!is_dir($sertifikatDir))
            mkdir($sertifikatDir, 0755, true);

        if (!empty($_FILES['file_sertifikat']['name']) && $_FILES['file_sertifikat']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
            if (in_array($ext, $allowedExt) && $_FILES['file_sertifikat']['size'] <= 2 * 1024 * 1024) {
                // Get old filename to delete
                $oldStmt = $conn->prepare("SELECT file_sertifikat FROM pendaftaran WHERE id = ?");
                $oldStmt->bind_param("i", $id);
                $oldStmt->execute();
                $oldFile = $oldStmt->get_result()->fetch_assoc()['file_sertifikat'] ?? '';

                // Delete old file
                if ($oldFile && file_exists($sertifikatDir . $oldFile)) {
                    unlink($sertifikatDir . $oldFile);
                }

                // Save new file
                $filename = $id . '_sertifikat_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $sertifikatDir . $filename)) {
                    $updateStmt = $conn->prepare("UPDATE pendaftaran SET file_sertifikat = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $filename, $id);
                    $updateStmt->execute();
                }
            }
        }

        logActivity('UPDATE', "Mengupdate data pendaftaran: $nama");
        header('Location: pendaftaran.php?msg=updated');
        exit;
    }

    // Reset password
    if ($action === 'reset_password' && $id > 0) {
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) >= 6) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("SELECT nama FROM pendaftaran WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $nama = $stmt->get_result()->fetch_assoc()['nama'] ?? '';

            $stmt = $conn->prepare("UPDATE pendaftaran SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $id);
            $stmt->execute();

            logActivity('RESET_PASSWORD', "Reset password untuk: $nama");
            header('Location: pendaftaran.php?msg=updated');
            exit;
        }
    }

    // =============================================
    // FITUR 2: Kirim WhatsApp Kekurangan Berkas
    // =============================================
    if ($action === 'notify_berkas' && $id > 0) {
        require_once '../api/whatsapp.php';

        // Get pendaftar data
        $stmt = $conn->prepare("SELECT nama, no_registrasi, no_hp_wali, file_kk, file_ktp_ortu, file_akta, file_ijazah FROM pendaftaran WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if ($data) {
            // Check missing files
            $berkasKurang = [];
            if (empty($data['file_kk']))
                $berkasKurang[] = 'Kartu Keluarga (KK)';
            if (empty($data['file_ktp_ortu']))
                $berkasKurang[] = 'KTP Orang Tua';
            if (empty($data['file_akta']))
                $berkasKurang[] = 'Akta Kelahiran';
            if (empty($data['file_ijazah']))
                $berkasKurang[] = 'Ijazah/SKL';

            if (!empty($berkasKurang)) {
                $waMessage = waTemplateKekuranganBerkas($data['nama'], $data['no_registrasi'], $berkasKurang);
                $result = sendWhatsApp($data['no_hp_wali'], $waMessage);

                logActivity('WA_BERKAS', "Kirim notifikasi kekurangan berkas ke: {$data['nama']}");
                header('Location: pendaftaran.php?msg=wa_sent');
            } else {
                header('Location: pendaftaran.php?msg=berkas_lengkap');
            }
        } else {
            header('Location: pendaftaran.php?msg=error');
        }
        exit;
    }
}

// Filters
$search = sanitize($conn, $_GET['search'] ?? '');
$lembaga = sanitize($conn, $_GET['lembaga'] ?? '');
$status = sanitize($conn, $_GET['status'] ?? '');

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(nama LIKE ? OR nisn LIKE ? OR asal_sekolah LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if ($lembaga) {
    $where[] = "lembaga = ?";
    $params[] = $lembaga;
    $types .= 's';
}

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countSql = "SELECT COUNT(*) as total FROM pendaftaran $whereClause";
$countStmt = $conn->prepare($countSql);
if ($params) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Get paginated data
$sql = "SELECT * FROM pendaftaran $whereClause ORDER BY no_registrasi ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$pendaftaran = [];
while ($row = $result->fetch_assoc()) {
    $pendaftaran[] = $row;
}

$conn->close();

// Page config
$pageTitle = 'Data Pendaftar - Admin SPMB';
$currentPage = 'pendaftaran';

// Build pagination query string
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>
<?php include 'includes/header.php'; ?>
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printArea,
        #printArea * {
            visibility: visible;
        }

        #printArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
        }

        .no-print {
            display: none !important;
        }
    }

    /* Autocomplete dropdown */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 100;
    }

    .autocomplete-dropdown .item {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.75rem;
    }

    .autocomplete-dropdown .item:hover {
        background-color: #fef3e2;
    }

    .autocomplete-dropdown .item:last-child {
        border-bottom: none;
    }

    .autocomplete-dropdown .no-result {
        padding: 0.5rem 0.75rem;
        color: #9ca3af;
        font-size: 0.75rem;
        text-align: center;
    }
</style>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Data Pendaftar</h2>
                <p class="text-gray-500 text-sm">Kelola data pendaftaran siswa baru</p>
            </div>
            <button onclick="exportData()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </button>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php
            $msgType = 'green';
            $msgIcon = 'check-circle';
            $msgText = 'Data berhasil diupdate!';

            switch ($_GET['msg']) {
                case 'deleted':
                    $msgText = 'Data berhasil dihapus!';
                    break;
                case 'updated':
                    $msgText = 'Data berhasil diupdate!';
                    break;
                case 'wa_sent':
                    $msgText = 'Notifikasi WhatsApp berhasil dikirim!';
                    break;
                case 'berkas_lengkap':
                    $msgType = 'yellow';
                    $msgIcon = 'exclamation-circle';
                    $msgText = 'Semua berkas sudah lengkap!';
                    break;
            }
            ?>
            <div
                class="bg-<?= $msgType ?>-100 border border-<?= $msgType ?>-400 text-<?= $msgType ?>-700 px-4 py-3 rounded-lg mb-4 text-sm">
                <i class="fas fa-<?= $msgIcon ?> mr-2"></i>
                <?= $msgText ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <form method="GET" class="space-y-3">
                <div class="w-full">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari nama, NISN, atau asal sekolah..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-sm">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select name="lembaga"
                        class="flex-1 min-w-[120px] px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-sm">
                        <option value="">Semua Lembaga</option>
                        <option value="SMP NU BP" <?= $lembaga === 'SMP NU BP' ? 'selected' : '' ?>>SMP NU BP</option>
                        <option value="MA ALHIKAM" <?= $lembaga === 'MA ALHIKAM' ? 'selected' : '' ?>>MA ALHIKAM</option>
                    </select>
                    <select name="status"
                        class="flex-1 min-w-[120px] px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-sm">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                    <button type="submit"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition text-sm flex items-center gap-2">
                        <i class="fas fa-search"></i><span class="hidden sm:inline">Cari</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <?php if (empty($pendaftaran)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>Tidak ada data pendaftaran</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>No.
                                        Reg</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(1, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Nama</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(2, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Lembaga</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(3, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>JK</span>
                                    <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(4, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>NISN</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(5, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>NIK</span>
                                    <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(6, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>TTL</span>
                                    <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(7, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Provinsi</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(8, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Kota/Kab</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(9, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Kecamatan</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(10, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Kelurahan</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(11, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>Asal
                                        Sekolah</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(12, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>Status
                                        Mukim</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(13, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>PIP/PKH</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(14, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>Nama
                                        Ayah</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(15, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>Kerja
                                        Ayah</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(16, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>Nama
                                        Ibu</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(17, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>Kerja
                                        Ibu</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(18, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase"><span>No
                                        HP</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(19, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Status</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(20, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    <span>Tanggal</span> <button class="filter-btn ml-1 text-gray-400 hover:text-primary"
                                        onclick="openFilter(21, this)"><i class="fas fa-filter text-[10px]"></i></button>
                                </th>
                                <th
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                    Aksi <button onclick="clearAllFilters()" class="ml-1 text-red-500 hover:text-red-700"
                                        title="Clear All Filters"><i class="fas fa-times text-[10px]"></i></button></th>
                            </tr>
                        </thead>

                        <!-- Excel-style Filter Popup -->
                        <div id="filterPopup"
                            class="hidden fixed bg-white border border-gray-300 rounded-lg shadow-xl z-[100]"
                            style="min-width:200px; max-width:260px;">
                            <div class="p-2 border-b bg-gray-50 flex justify-between items-center">
                                <span class="text-xs font-semibold text-gray-700">Filter Kolom</span>
                                <button onclick="closeFilterPopup()" class="text-gray-400 hover:text-gray-600"><i
                                        class="fas fa-times"></i></button>
                            </div>
                            <div class="p-2 border-b flex gap-1">
                                <button onclick="sortColumn('asc')"
                                    class="flex-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-2 py-1.5 rounded flex items-center justify-center gap-1">
                                    <i class="fas fa-sort-alpha-down"></i> A-Z
                                </button>
                                <button onclick="sortColumn('desc')"
                                    class="flex-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-2 py-1.5 rounded flex items-center justify-center gap-1">
                                    <i class="fas fa-sort-alpha-up"></i> Z-A
                                </button>
                            </div>
                            <div class="p-2 border-b">
                                <input type="text" id="filterSearch" placeholder="Cari..."
                                    class="w-full px-2 py-1 text-xs border rounded focus:outline-none focus:border-primary"
                                    oninput="searchFilterItems()">
                            </div>
                            <div class="p-2 border-b flex gap-2">
                                <button onclick="selectAllFilter()"
                                    class="flex-1 text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">Pilih
                                    Semua</button>
                                <button onclick="clearFilter()"
                                    class="flex-1 text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">Hapus</button>
                            </div>
                            <div id="filterItems" class="max-h-40 overflow-y-auto p-2 text-xs">
                                <!-- Checkboxes will be populated here -->
                            </div>
                            <div class="p-2 border-t bg-gray-50 flex gap-2">
                                <button onclick="applyFilter()"
                                    class="flex-1 text-xs bg-primary hover:bg-primary-dark text-white px-3 py-1.5 rounded font-medium">OK</button>
                                <button onclick="closeFilterPopup()"
                                    class="flex-1 text-xs bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded">Batal</button>
                            </div>
                        </div>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($pendaftaran as $i => $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= $offset + $i + 1 ?></td>
                                    <td class="px-3 py-2 text-sm font-mono font-bold text-primary">
                                        <?= htmlspecialchars($row['no_registrasi'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm font-medium text-gray-800">
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= $row['lembaga'] ?></td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= $row['jenis_kelamin'] === 'L' ? 'L' : 'P' ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($row['nisn']) ?></td>
                                    <td class="px-3 py-2 text-sm text-gray-600 font-mono text-xs">
                                        <?= htmlspecialchars($row['nik']) ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['tempat_lahir']) ?>,
                                        <?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($row['provinsi'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($row['kota_kab'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['kelurahan_desa'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['asal_sekolah'] ?: '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['status_mukim'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($row['pip_pkh'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['nama_ayah'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['pekerjaan_ayah'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($row['nama_ibu'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['pekerjaan_ibu'] ?? '-') ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($row['no_hp_wali']) ?></td>
                                    <td class="px-3 py-2">
                                        <?php
                                        $statusClass = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'verified' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ][$row['status']];
                                        $statusText = [
                                            'pending' => 'Menunggu',
                                            'verified' => 'Verif',
                                            'rejected' => 'Tolak'
                                        ][$row['status']];
                                        ?>
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="px-3 py-2 sticky right-0 bg-white shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.1)]">
                                        <div class="flex gap-1">
                                            <a href="../kartu-peserta.php?id=<?= $row['id'] ?>" target="_blank"
                                                class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                                                title="Cetak Kartu">
                                                <i class="fas fa-id-card text-xs"></i>
                                            </a>
                                            <button onclick='openEditModal(<?= json_encode($row) ?>)'
                                                class="p-1.5 text-primary hover:bg-orange-100 rounded-lg transition"
                                                title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button
                                                onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama']) ?>')"
                                                class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Hapus">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        Menampilkan <?= $offset + 1 ?>-<?= min($offset + $perPage, $totalRows) ?> dari <?= $totalRows ?>
                        data
                    </p>
                    <?php if ($totalPages > 1): ?>
                        <div class="flex gap-1">
                            <?php if ($page > 1): ?>
                                <a href="?<?= $queryString ?>&page=<?= $page - 1 ?>"
                                    class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            for ($p = $start; $p <= $end; $p++):
                                ?>
                                <a href="?<?= $queryString ?>&page=<?= $p ?>"
                                    class="px-3 py-1 border rounded-lg text-sm <?= $p === $page ? 'bg-primary text-white border-primary' : 'border-gray-300 hover:bg-gray-50' ?>">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?<?= $queryString ?>&page=<?= $page + 1 ?>"
                                    class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col my-8">
        <!-- Modal Header -->
        <div class="bg-primary text-white p-4 flex items-center justify-between flex-shrink-0 no-print">
            <div>
                <h3 class="font-bold" id="detailNama">Detail Pendaftar</h3>
                <p class="text-xs text-white/60" id="detailLembaga"></p>
            </div>
            <div class="flex items-center gap-3">
                <span id="detailStatus" class="px-3 py-1 rounded-full text-xs font-medium"></span>
                <button onclick="printDetail()" class="text-white/80 hover:text-white" title="Print">
                    <i class="fas fa-print"></i>
                </button>
                <button onclick="closeModal('detailModal')" class="text-white/80 hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body - Scrollable -->
        <div class="overflow-y-auto flex-1 p-4" id="printArea">
            <!-- Print Header (hidden on screen) -->
            <div class="hidden print:block mb-4 text-center border-b pb-4">
                <h2 class="text-xl font-bold" id="printTitle">Detail Pendaftaran</h2>
                <p class="text-gray-600" id="printSubtitle"></p>
            </div>

            <!-- Status Update -->
            <div class="bg-gray-50 rounded-lg p-3 mb-4 no-print">
                <p class="text-sm font-medium text-gray-700 mb-2">Update Status:</p>
                <form method="POST" class="flex flex-wrap gap-2" id="statusForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" id="statusId">
                    <button type="submit" name="status" value="pending"
                        class="status-btn px-3 py-1.5 rounded-lg text-xs font-medium transition bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                        <i class="fas fa-clock mr-1"></i>Menunggu
                    </button>
                    <button type="submit" name="status" value="verified"
                        class="status-btn px-3 py-1.5 rounded-lg text-xs font-medium transition bg-green-100 text-green-800 hover:bg-green-200">
                        <i class="fas fa-check mr-1"></i>Verifikasi
                    </button>
                    <button type="submit" name="status" value="rejected"
                        class="status-btn px-3 py-1.5 rounded-lg text-xs font-medium transition bg-red-100 text-red-800 hover:bg-red-200">
                        <i class="fas fa-times mr-1"></i>Tolak
                    </button>
                </form>
            </div>

            <!-- Catatan Admin (Message to User) -->
            <div class="bg-blue-50 rounded-lg p-3 mb-4 no-print border border-blue-100">
                <p class="text-sm font-medium text-blue-700 mb-2 flex items-center gap-2">
                    <i class="fas fa-comment-alt"></i>Pesan untuk Pendaftar:
                </p>
                <form method="POST" id="catatanForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_catatan">
                    <input type="hidden" name="id" id="catatanId">
                    <textarea name="catatan_admin" id="catatanAdmin" rows="3"
                        class="w-full px-3 py-2 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-300 outline-none resize-none"
                        placeholder="Tulis catatan/alasan untuk pendaftar (misal: dokumen belum lengkap, foto tidak jelas, dll)"></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-xs text-blue-500" id="catatanInfo"></p>
                        <button type="submit"
                            class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                            <i class="fas fa-save mr-1"></i>Simpan Catatan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Data Siswa -->
            <div class="border border-gray-200 rounded-lg mb-4">
                <div class="bg-primary/5 px-4 py-2 border-b border-gray-200">
                    <h4 class="font-semibold text-primary text-sm"><i class="fas fa-user mr-2"></i>Data Calon Siswa</h4>
                </div>
                <div class="p-4 text-sm">
                    <table class="w-full">
                        <tr>
                            <td class="text-gray-500 py-1.5 w-36">NISN</td>
                            <td class="py-1.5">: <span id="dNisn" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">NIK</td>
                            <td class="py-1.5">: <span id="dNik" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">TTL</td>
                            <td class="py-1.5">: <span id="dTtl" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Jenis Kelamin</td>
                            <td class="py-1.5">: <span id="dJk" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">No. KK</td>
                            <td class="py-1.5">: <span id="dNoKk" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Jumlah Saudara</td>
                            <td class="py-1.5">: <span id="dSaudara" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Provinsi</td>
                            <td class="py-1.5">: <span id="dProvinsi" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Kota/Kab</td>
                            <td class="py-1.5">: <span id="dKotaKab" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Kecamatan</td>
                            <td class="py-1.5">: <span id="dKecamatan" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Kelurahan/Desa</td>
                            <td class="py-1.5">: <span id="dKelurahan" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Detail Alamat</td>
                            <td class="py-1.5">: <span id="dAlamat" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Asal Sekolah</td>
                            <td class="py-1.5">: <span id="dAsalSekolah" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Status Mukim</td>
                            <td class="py-1.5">: <span id="dMukim" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">PIP/PKH</td>
                            <td class="py-1.5">: <span id="dPip" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Sumber Info</td>
                            <td class="py-1.5">: <span id="dSumber" class="font-medium"></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Prestasi -->
            <div id="prestasiSection" class="border border-yellow-200 rounded-lg mb-4 hidden">
                <div class="bg-yellow-50 px-4 py-2 border-b border-yellow-200">
                    <h4 class="font-semibold text-yellow-700 text-sm"><i class="fas fa-trophy mr-2"></i>Prestasi</h4>
                </div>
                <div class="p-4 text-sm">
                    <table class="w-full">
                        <tr>
                            <td class="text-gray-500 py-1.5 w-36">Prestasi</td>
                            <td class="py-1.5">: <span id="dPrestasi" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Tingkat</td>
                            <td class="py-1.5">: <span id="dTingkat" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Juara</td>
                            <td class="py-1.5">: <span id="dJuara" class="font-medium"></span></td>
                        </tr>
                    </table>
                </div>
                <div id="sertifikatLink" class="px-4 pb-4 hidden no-print">
                    <a href="" id="dSertifikat" target="_blank"
                        class="inline-flex items-center gap-2 bg-primary/10 text-primary px-3 py-1.5 rounded-lg text-xs hover:bg-primary/20 transition">
                        <i class="fas fa-file-download"></i>Lihat Sertifikat
                    </a>
                </div>
            </div>

            <!-- Data Ayah -->
            <div class="border border-blue-200 rounded-lg mb-4">
                <div class="bg-blue-50 px-4 py-2 border-b border-blue-200">
                    <h4 class="font-semibold text-blue-700 text-sm"><i class="fas fa-male mr-2"></i>Data Ayah</h4>
                </div>
                <div class="p-4 text-sm">
                    <table class="w-full">
                        <tr>
                            <td class="text-gray-500 py-1.5 w-36">Nama</td>
                            <td class="py-1.5">: <span id="dNamaAyah" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">NIK</td>
                            <td class="py-1.5">: <span id="dNikAyah" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">TTL</td>
                            <td class="py-1.5">: <span id="dTtlAyah" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Pekerjaan</td>
                            <td class="py-1.5">: <span id="dKerjaAyah" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Penghasilan</td>
                            <td class="py-1.5">: <span id="dGajiAyah" class="font-medium"></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Data Ibu -->
            <div class="border border-pink-200 rounded-lg mb-4">
                <div class="bg-pink-50 px-4 py-2 border-b border-pink-200">
                    <h4 class="font-semibold text-pink-700 text-sm"><i class="fas fa-female mr-2"></i>Data Ibu</h4>
                </div>
                <div class="p-4 text-sm">
                    <table class="w-full">
                        <tr>
                            <td class="text-gray-500 py-1.5 w-36">Nama</td>
                            <td class="py-1.5">: <span id="dNamaIbu" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">NIK</td>
                            <td class="py-1.5">: <span id="dNikIbu" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">TTL</td>
                            <td class="py-1.5">: <span id="dTtlIbu" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Pekerjaan</td>
                            <td class="py-1.5">: <span id="dKerjaIbu" class="font-medium"></span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 py-1.5">Penghasilan</td>
                            <td class="py-1.5">: <span id="dGajiIbu" class="font-medium"></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Kontak -->
            <div class="border border-green-200 rounded-lg mb-4">
                <div class="bg-green-50 px-4 py-2 border-b border-green-200">
                    <h4 class="font-semibold text-green-700 text-sm"><i class="fas fa-phone mr-2"></i>Kontak Wali</h4>
                </div>
                <div class="p-4 flex items-center gap-3">
                    <span id="dNoHp" class="font-medium"></span>
                    <a href="" id="dWaLink" target="_blank" class="text-green-600 hover:text-green-700 no-print">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </a>
                </div>
            </div>

            <!-- Dokumen -->
            <div id="dokumenSection" class="border border-purple-200 rounded-lg mb-4 hidden">
                <div class="bg-purple-50 px-4 py-2 border-b border-purple-200 flex items-center justify-between">
                    <h4 class="font-semibold text-purple-700 text-sm"><i class="fas fa-file-pdf mr-2"></i>Dokumen</h4>
                    <button type="button" onclick="notifyBerkasFromModal()"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition no-print">
                        <i class="fab fa-whatsapp mr-1"></i>WA Kekurangan Berkas
                    </button>
                </div>
                <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-2 text-sm no-print">
                    <a href="" id="dFileKk" target="_blank"
                        class="hidden bg-purple-50 hover:bg-purple-100 rounded-lg p-2 text-center text-purple-700 transition">
                        <i class="fas fa-file-pdf"></i><br><span class="text-xs">KK</span>
                    </a>
                    <a href="" id="dFileKtp" target="_blank"
                        class="hidden bg-purple-50 hover:bg-purple-100 rounded-lg p-2 text-center text-purple-700 transition">
                        <i class="fas fa-file-pdf"></i><br><span class="text-xs">KTP Ortu</span>
                    </a>
                    <a href="" id="dFileAkta" target="_blank"
                        class="hidden bg-purple-50 hover:bg-purple-100 rounded-lg p-2 text-center text-purple-700 transition">
                        <i class="fas fa-file-pdf"></i><br><span class="text-xs">Akta</span>
                    </a>
                    <a href="" id="dFileIjazah" target="_blank"
                        class="hidden bg-purple-50 hover:bg-purple-100 rounded-lg p-2 text-center text-purple-700 transition">
                        <i class="fas fa-file-pdf"></i><br><span class="text-xs">Ijazah</span>
                    </a>
                </div>
            </div>

            <!-- Meta -->
            <p class="text-center text-xs text-gray-400 mt-4" id="dCreatedAt"></p>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-sm w-full p-6">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash-alt text-red-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-lg text-gray-800">Hapus Data?</h3>
            <p class="text-gray-500 text-sm mt-2">Yakin ingin menghapus data <strong id="deleteName"></strong>?</p>
        </div>
        <form method="POST" class="flex gap-3">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            <button type="button" onclick="closeModal('deleteModal')"
                class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button type="submit"
                class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Hapus</button>
        </form>
    </div>
</div>

<!-- WA Kekurangan Berkas Confirmation Modal -->
<div id="waConfirmModal" class="fixed inset-0 bg-black/50 z-[60] hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-sm w-full p-6 animate-[fadeIn_0.2s_ease]">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fab fa-whatsapp text-green-600 text-3xl"></i>
            </div>
            <h3 class="font-bold text-lg text-gray-800">Kirim Notifikasi WA?</h3>
            <p class="text-gray-500 text-sm mt-2">Kirim notifikasi kekurangan berkas via WhatsApp ke:</p>
            <p class="font-semibold text-gray-800 mt-1" id="waConfirmName"></p>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="closeModal('waConfirmModal')"
                class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button type="button" onclick="confirmSendWaBerkas()"
                class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp"></i>Kirim
            </button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl max-w-4xl w-full my-4">
        <div class="bg-primary text-white p-4 rounded-t-xl flex justify-between items-center sticky top-0">
            <h3 class="font-bold text-lg"><i class="fas fa-edit mr-2"></i>Edit Data Pendaftar</h3>
            <button onclick="closeModal('editModal')" class="text-white/80 hover:text-white"><i
                    class="fas fa-times"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-4 space-y-4 max-h-[80vh] overflow-y-auto">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_data">
            <input type="hidden" name="id" id="editId">

            <!-- Data Siswa -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-3"><i class="fas fa-user text-primary mr-2"></i>Data Calon
                    Siswa</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama" id="editNama" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lembaga *</label>
                        <select name="lembaga" id="editLembaga" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                            <option value="SMP NU BP">SMP NU BP</option>
                            <option value="MA ALHIKAM">MA ALHIKAM</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">NISN</label>
                        <input type="text" name="nisn" id="editNisn"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">NIK</label>
                        <input type="text" name="nik" id="editNik"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. KK</label>
                        <input type="text" name="no_kk" id="editNoKk"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="editTempatLahir"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="editTglLahir"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" id="editJk" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Saudara</label>
                        <input type="number" name="jumlah_saudara" id="editJmlSaudara" min="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah" id="editAsalSekolah"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Provinsi</label>
                        <select name="provinsi" id="editProvinsi" onchange="onEditProvinsiChange(this)"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kota/Kabupaten</label>
                        <select name="kota_kab" id="editKotaKab" onchange="onEditKotaChange(this)"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                            <option value="">-- Pilih Kota/Kabupaten --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kecamatan</label>
                        <select name="kecamatan" id="editKecamatan" onchange="onEditKecamatanChange(this)"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none"
                            disabled>
                            <option value="">-- Pilih Provinsi & Kota dulu --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kelurahan/Desa</label>
                        <select name="kelurahan_desa" id="editKelurahan"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none"
                            disabled>
                            <option value="">-- Pilih Kecamatan dulu --</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Detail Alamat *</label>
                        <textarea name="alamat" id="editAlamat" rows="2" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status Mukim *</label>
                        <select name="status_mukim" id="editStatusMukim" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                            <option value="PONDOK PP MAMBAUL HUDA">Pondok PP Mambaul Huda</option>
                            <option value="PONDOK SELAIN PP MAMBAUL HUDA">Pondok Lain</option>
                            <option value="TIDAK PONDOK">Tidak Pondok</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">PIP/PKH</label>
                        <input type="text" name="pip_pkh" id="editPipPkh"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sumber Info</label>
                        <input type="text" name="sumber_info" id="editSumberInfo"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>
            </div>

            <!-- Prestasi -->
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-semibold text-yellow-700 mb-3"><i class="fas fa-trophy mr-2"></i>Prestasi</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Prestasi</label>
                        <input type="text" name="prestasi" id="editPrestasi"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tingkat</label>
                        <select name="tingkat_prestasi" id="editTingkatPrestasi"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                            <option value="">Pilih</option>
                            <option value="KABUPATEN">Kabupaten</option>
                            <option value="PROVINSI">Provinsi</option>
                            <option value="NASIONAL">Nasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Juara</label>
                        <select name="juara" id="editJuara"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary outline-none">
                            <option value="">Pilih</option>
                            <option value="1">Juara 1</option>
                            <option value="2">Juara 2</option>
                            <option value="3">Juara 3</option>
                        </select>
                    </div>
                </div>
                <!-- File Sertifikat -->
                <div class="mt-3 bg-white p-3 rounded-lg border">
                    <label class="block text-xs font-medium text-gray-700 mb-2">File Sertifikat</label>
                    <div id="editFileSertifikatInfo" class="mb-2 hidden">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                            <span id="editFileSertifikatName" class="text-gray-600 truncate flex-1"></span>
                            <button type="button" onclick="viewDocument('file_sertifikat')"
                                class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                <i class="fas fa-eye mr-1"></i>Lihat
                            </button>
                        </div>
                    </div>
                    <p id="editFileSertifikatEmpty" class="text-xs text-red-500 mb-2 hidden"><i
                            class="fas fa-times-circle mr-1"></i>Belum ada file yang diupload</p>
                    <input type="file" name="file_sertifikat" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs border border-gray-300 rounded bg-white file:mr-2 file:py-1 file:px-2 file:border-0 file:text-xs file:bg-yellow-100 file:text-yellow-700">
                    <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG (Max 2MB)</p>
                </div>
            </div>

            <!-- Data Ayah -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-700 mb-3"><i class="fas fa-male mr-2"></i>Data Ayah</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Ayah</label>
                        <input type="text" name="nama_ayah" id="editNamaAyah"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">NIK Ayah</label>
                        <input type="text" name="nik_ayah" id="editNikAyah"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir_ayah" id="editTempatLahirAyah"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir_ayah" id="editTglLahirAyah"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pekerjaan</label>
                        <input type="text" name="pekerjaan_ayah" id="editPekerjaanAyah"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Penghasilan</label>
                        <select name="penghasilan_ayah" id="editPenghasilanAyah"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            <option value="">Pilih</option>
                            <option value="< 1 Juta">
                                < 1 Juta</option>
                            <option value="1-3 Juta">1-3 Juta</option>
                            <option value="3-5 Juta">3-5 Juta</option>
                            <option value="> 5 Juta">> 5 Juta</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Data Ibu -->
            <div class="bg-pink-50 p-4 rounded-lg">
                <h4 class="font-semibold text-pink-700 mb-3"><i class="fas fa-female mr-2"></i>Data Ibu</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Ibu</label>
                        <input type="text" name="nama_ibu" id="editNamaIbu"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">NIK Ibu</label>
                        <input type="text" name="nik_ibu" id="editNikIbu"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir_ibu" id="editTempatLahirIbu"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir_ibu" id="editTglLahirIbu"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pekerjaan</label>
                        <input type="text" name="pekerjaan_ibu" id="editPekerjaanIbu"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Penghasilan</label>
                        <select name="penghasilan_ibu" id="editPenghasilanIbu"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            <option value="">Pilih</option>
                            <option value="< 1 Juta">
                                < 1 Juta</option>
                            <option value="1-3 Juta">1-3 Juta</option>
                            <option value="3-5 Juta">3-5 Juta</option>
                            <option value="> 5 Juta">> 5 Juta</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Kontak & Status -->
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-semibold text-green-700 mb-3"><i class="fas fa-phone mr-2"></i>Kontak & Status</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. HP Wali *</label>
                        <div class="flex gap-2">
                            <input type="text" name="no_hp_wali" id="editNoHp" required
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            <button type="button" onclick="openWhatsApp(document.getElementById('editNoHp').value)"
                                class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
                                title="Hubungi via WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status Pendaftaran</label>
                        <select name="status" id="editStatus" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            <option value="pending">Pending</option>
                            <option value="verified">Terverifikasi</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Reset Password</label>
                        <input type="text" name="new_password" placeholder="Kosongkan jika tidak reset"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Upload Dokumen -->
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-purple-700"><i class="fas fa-file-pdf mr-2"></i>Dokumen (PDF/JPG/PNG,
                        Max 2MB)</h4>
                    <button type="button" onclick="notifyBerkasFromEdit()"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">
                        <i class="fab fa-whatsapp mr-1"></i>WA Kekurangan Berkas
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- KK -->
                    <div class="bg-white p-3 rounded-lg border">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Kartu Keluarga</label>
                        <div id="editFileKkInfo" class="mb-2 hidden">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                                <span id="editFileKkName" class="text-gray-600 truncate flex-1"></span>
                                <button type="button" onclick="viewDocument('file_kk')"
                                    class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </button>
                            </div>
                        </div>
                        <p id="editFileKkEmpty" class="text-xs text-red-500 mb-2 hidden"><i
                                class="fas fa-times-circle mr-1"></i>Belum ada file yang diupload</p>
                        <input type="file" name="file_kk" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs border border-gray-300 rounded bg-white file:mr-2 file:py-1 file:px-2 file:border-0 file:text-xs file:bg-purple-100 file:text-purple-700">
                    </div>
                    <!-- KTP -->
                    <div class="bg-white p-3 rounded-lg border">
                        <label class="block text-xs font-medium text-gray-700 mb-2">KTP Orang Tua</label>
                        <div id="editFileKtpInfo" class="mb-2 hidden">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                                <span id="editFileKtpName" class="text-gray-600 truncate flex-1"></span>
                                <button type="button" onclick="viewDocument('file_ktp_ortu')"
                                    class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </button>
                            </div>
                        </div>
                        <p id="editFileKtpEmpty" class="text-xs text-red-500 mb-2 hidden"><i
                                class="fas fa-times-circle mr-1"></i>Belum ada file yang diupload</p>
                        <input type="file" name="file_ktp_ortu" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs border border-gray-300 rounded bg-white file:mr-2 file:py-1 file:px-2 file:border-0 file:text-xs file:bg-purple-100 file:text-purple-700">
                    </div>
                    <!-- Akta -->
                    <div class="bg-white p-3 rounded-lg border">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Akta Kelahiran</label>
                        <div id="editFileAktaInfo" class="mb-2 hidden">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                                <span id="editFileAktaName" class="text-gray-600 truncate flex-1"></span>
                                <button type="button" onclick="viewDocument('file_akta')"
                                    class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </button>
                            </div>
                        </div>
                        <p id="editFileAktaEmpty" class="text-xs text-red-500 mb-2 hidden"><i
                                class="fas fa-times-circle mr-1"></i>Belum ada file yang diupload</p>
                        <input type="file" name="file_akta" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs border border-gray-300 rounded bg-white file:mr-2 file:py-1 file:px-2 file:border-0 file:text-xs file:bg-purple-100 file:text-purple-700">
                    </div>
                    <!-- Ijazah -->
                    <div class="bg-white p-3 rounded-lg border">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Ijazah (Opsional)</label>
                        <div id="editFileIjazahInfo" class="mb-2 hidden">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                                <span id="editFileIjazahName" class="text-gray-600 truncate flex-1"></span>
                                <button type="button" onclick="viewDocument('file_ijazah')"
                                    class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </button>
                            </div>
                        </div>
                        <p id="editFileIjazahEmpty" class="text-xs text-red-500 mb-2 hidden"><i
                                class="fas fa-times-circle mr-1"></i>Belum ada file yang diupload</p>
                        <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs border border-gray-300 rounded bg-white file:mr-2 file:py-1 file:px-2 file:border-0 file:text-xs file:bg-purple-100 file:text-purple-700">
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3"><i class="fas fa-info-circle mr-1"></i>Upload file baru akan
                    mengganti file lama. Kosongkan jika tidak ingin mengubah.</p>
            </div>

            <!-- Catatan Admin (Pesan untuk Pendaftar) -->
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <h4 class="text-sm font-semibold text-blue-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-comment-alt"></i>Pesan untuk Pendaftar
                </h4>
                <textarea name="catatan_admin" id="editCatatanAdmin" rows="3"
                    class="w-full px-3 py-2 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-300 outline-none resize-none"
                    placeholder="Tulis catatan/alasan untuk pendaftar (misal: dokumen belum lengkap, foto tidak jelas, dll). Pesan ini akan muncul di dashboard pendaftar."></textarea>
                <p class="text-xs text-blue-500 mt-2"><i class="fas fa-info-circle mr-1"></i>Pesan ini akan tampil di
                    dashboard user jika status pending/ditolak</p>
            </div>

            <div class="flex gap-3 pt-4 border-t sticky bottom-0 bg-white">
                <button type="button" onclick="closeModal('editModal')"
                    class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button type="submit"
                    class="flex-1 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPwModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-sm w-full p-6">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-key text-yellow-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-lg text-gray-800">Reset Password</h3>
            <p class="text-gray-500 text-sm mt-2">Reset password untuk <strong id="resetPwName"></strong></p>
        </div>
        <form method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="id" id="resetPwId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input type="text" name="new_password" required minlength="6" placeholder="Minimal 6 karakter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('resetPwModal')"
                    class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button type="submit"
                    class="flex-1 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                    <i class="fas fa-save mr-2"></i>Reset
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Open WhatsApp with dynamic phone number
    function openWhatsApp(phone) {
        // Clean phone number - remove anything that's not a digit
        let cleaned = phone.replace(/[^0-9]/g, '');
        // If starts with 0, replace with 62 (Indonesia country code)
        if (cleaned.startsWith('0')) {
            cleaned = '62' + cleaned.substring(1);
        }
        // If doesn't start with country code, add 62
        if (!cleaned.startsWith('62')) {
            cleaned = '62' + cleaned;
        }
        window.open('https://wa.me/' + cleaned, '_blank');
    }

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    function confirmDelete(id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteName').textContent = name;
        openModal('deleteModal');
    }

    function openEditModal(data) {
        document.getElementById('editId').value = data.id;
        // Data Siswa
        document.getElementById('editNama').value = data.nama || '';
        document.getElementById('editLembaga').value = data.lembaga || '';
        document.getElementById('editNisn').value = data.nisn || '';
        document.getElementById('editNik').value = data.nik || '';
        document.getElementById('editNoKk').value = data.no_kk || '';
        document.getElementById('editTempatLahir').value = data.tempat_lahir || '';
        document.getElementById('editTglLahir').value = data.tanggal_lahir || '';
        document.getElementById('editJk').value = data.jenis_kelamin || 'L';
        document.getElementById('editJmlSaudara').value = data.jumlah_saudara || 0;
        document.getElementById('editAsalSekolah').value = data.asal_sekolah || '';
        document.getElementById('editAlamat').value = data.alamat || '';
        document.getElementById('editStatusMukim').value = data.status_mukim || '';
        document.getElementById('editPipPkh').value = data.pip_pkh || '';
        document.getElementById('editSumberInfo').value = data.sumber_info || '';
        // Prestasi
        document.getElementById('editPrestasi').value = data.prestasi || '';
        document.getElementById('editTingkatPrestasi').value = data.tingkat_prestasi || '';
        document.getElementById('editJuara').value = data.juara || '';
        // Data Ayah
        document.getElementById('editNamaAyah').value = data.nama_ayah || '';
        document.getElementById('editNikAyah').value = data.nik_ayah || '';
        document.getElementById('editTempatLahirAyah').value = data.tempat_lahir_ayah || '';
        document.getElementById('editTglLahirAyah').value = data.tanggal_lahir_ayah || '';
        document.getElementById('editPekerjaanAyah').value = data.pekerjaan_ayah || '';
        document.getElementById('editPenghasilanAyah').value = data.penghasilan_ayah || '';
        // Data Ibu
        document.getElementById('editNamaIbu').value = data.nama_ibu || '';
        document.getElementById('editNikIbu').value = data.nik_ibu || '';
        document.getElementById('editTempatLahirIbu').value = data.tempat_lahir_ibu || '';
        document.getElementById('editTglLahirIbu').value = data.tanggal_lahir_ibu || '';
        document.getElementById('editPekerjaanIbu').value = data.pekerjaan_ibu || '';
        document.getElementById('editPenghasilanIbu').value = data.penghasilan_ibu || '';
        // Kontak & Status
        document.getElementById('editNoHp').value = data.no_hp_wali || '';
        document.getElementById('editStatus').value = data.status || 'pending';
        // Catatan Admin
        document.getElementById('editCatatanAdmin').value = data.catatan_admin || '';

        // Store current files for viewing
        window.currentEditFiles = {
            file_kk: data.file_kk || '',
            file_ktp_ortu: data.file_ktp_ortu || '',
            file_akta: data.file_akta || '',
            file_ijazah: data.file_ijazah || '',
            file_sertifikat: data.file_sertifikat || ''
        };

        // Set alamat dropdowns with proper loading
        setEditModalAlamat(data.provinsi, data.kota_kab, data.kecamatan, data.kelurahan_desa);

        // File status - Sertifikat
        if (data.file_sertifikat) {
            document.getElementById('editFileSertifikatInfo').classList.remove('hidden');
            document.getElementById('editFileSertifikatEmpty').classList.add('hidden');
            document.getElementById('editFileSertifikatName').textContent = data.file_sertifikat;
        } else {
            document.getElementById('editFileSertifikatInfo').classList.add('hidden');
            document.getElementById('editFileSertifikatEmpty').classList.remove('hidden');
        }

        // File status - KK
        if (data.file_kk) {
            document.getElementById('editFileKkInfo').classList.remove('hidden');
            document.getElementById('editFileKkEmpty').classList.add('hidden');
            document.getElementById('editFileKkName').textContent = data.file_kk;
        } else {
            document.getElementById('editFileKkInfo').classList.add('hidden');
            document.getElementById('editFileKkEmpty').classList.remove('hidden');
        }
        // File status - KTP
        if (data.file_ktp_ortu) {
            document.getElementById('editFileKtpInfo').classList.remove('hidden');
            document.getElementById('editFileKtpEmpty').classList.add('hidden');
            document.getElementById('editFileKtpName').textContent = data.file_ktp_ortu;
        } else {
            document.getElementById('editFileKtpInfo').classList.add('hidden');
            document.getElementById('editFileKtpEmpty').classList.remove('hidden');
        }
        // File status - Akta
        if (data.file_akta) {
            document.getElementById('editFileAktaInfo').classList.remove('hidden');
            document.getElementById('editFileAktaEmpty').classList.add('hidden');
            document.getElementById('editFileAktaName').textContent = data.file_akta;
        } else {
            document.getElementById('editFileAktaInfo').classList.add('hidden');
            document.getElementById('editFileAktaEmpty').classList.remove('hidden');
        }
        // File status - Ijazah
        if (data.file_ijazah) {
            document.getElementById('editFileIjazahInfo').classList.remove('hidden');
            document.getElementById('editFileIjazahEmpty').classList.add('hidden');
            document.getElementById('editFileIjazahName').textContent = data.file_ijazah;
        } else {
            document.getElementById('editFileIjazahInfo').classList.add('hidden');
            document.getElementById('editFileIjazahEmpty').classList.remove('hidden');
        }

        openModal('editModal');
    }

    function viewDocument(field) {
        const filename = window.currentEditFiles[field];
        if (filename) {
            // Sertifikat uses different folder
            const folder = field === 'file_sertifikat' ? 'sertifikat' : 'dokumen';
            window.open('../uploads/' + folder + '/' + filename, '_blank');
        } else {
            alert('Belum ada file yang diupload untuk dokumen ini.');
        }
    }

    function openResetPwModal(id, name) {
        document.getElementById('resetPwId').value = id;
        document.getElementById('resetPwName').textContent = name;
        openModal('resetPwModal');
    }

    function printDetail() {
        window.print();
    }

    function showDetail(data) {
        // Header
        document.getElementById('detailNama').textContent = data.nama;
        document.getElementById('detailLembaga').textContent = data.lembaga;
        document.getElementById('statusId').value = data.id;
        document.getElementById('catatanId').value = data.id;
        document.getElementById('printTitle').textContent = data.nama;
        document.getElementById('printSubtitle').textContent = data.lembaga + ' - ' + data.created_at;

        // Catatan admin
        document.getElementById('catatanAdmin').value = data.catatan_admin || '';
        const catatanInfo = document.getElementById('catatanInfo');
        if (data.catatan_updated_at) {
            catatanInfo.textContent = 'Terakhir diupdate: ' + new Date(data.catatan_updated_at).toLocaleString('id-ID');
        } else {
            catatanInfo.textContent = '';
        }

        // Status badge
        const statusColors = {
            'pending': 'bg-yellow-500',
            'verified': 'bg-green-500',
            'rejected': 'bg-red-500'
        };
        const statusTexts = {
            'pending': 'Menunggu',
            'verified': 'Terverifikasi',
            'rejected': 'Ditolak'
        };
        const statusEl = document.getElementById('detailStatus');
        statusEl.className = 'px-3 py-1 rounded-full text-xs font-medium text-white ' + statusColors[data.status];
        statusEl.textContent = statusTexts[data.status];

        // Helper function
        const setText = (id, value) => document.getElementById(id).textContent = value || '-';
        const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

        // Data Siswa
        setText('dNisn', data.nisn);
        setText('dNik', data.nik);
        setText('dTtl', (data.tempat_lahir || '-') + ', ' + formatDate(data.tanggal_lahir));
        setText('dJk', data.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');
        setText('dNoKk', data.no_kk);
        setText('dSaudara', data.jumlah_saudara);
        setText('dProvinsi', data.provinsi);
        setText('dKotaKab', data.kota_kab);
        setText('dKecamatan', data.kecamatan);
        setText('dKelurahan', data.kelurahan_desa);
        setText('dAlamat', data.alamat);
        setText('dAsalSekolah', data.asal_sekolah);
        setText('dMukim', data.status_mukim);
        setText('dPip', data.pip_pkh);
        setText('dSumber', data.sumber_info);

        // Prestasi
        const prestasiSection = document.getElementById('prestasiSection');
        if (data.prestasi) {
            prestasiSection.classList.remove('hidden');
            setText('dPrestasi', data.prestasi);
            setText('dTingkat', data.tingkat_prestasi);
            setText('dJuara', data.juara ? 'Juara ' + data.juara : '-');

            const sertifikatLink = document.getElementById('sertifikatLink');
            if (data.file_sertifikat) {
                sertifikatLink.classList.remove('hidden');
                document.getElementById('dSertifikat').href = '../uploads/sertifikat/' + data.file_sertifikat;
            } else {
                sertifikatLink.classList.add('hidden');
            }
        } else {
            prestasiSection.classList.add('hidden');
        }

        // Data Ayah
        setText('dNamaAyah', data.nama_ayah);
        setText('dNikAyah', data.nik_ayah);
        setText('dTtlAyah', (data.tempat_lahir_ayah || '-') + ', ' + formatDate(data.tanggal_lahir_ayah));
        setText('dKerjaAyah', data.pekerjaan_ayah);
        setText('dGajiAyah', data.penghasilan_ayah);

        // Data Ibu
        setText('dNamaIbu', data.nama_ibu);
        setText('dNikIbu', data.nik_ibu);
        setText('dTtlIbu', (data.tempat_lahir_ibu || '-') + ', ' + formatDate(data.tanggal_lahir_ibu));
        setText('dKerjaIbu', data.pekerjaan_ibu);
        setText('dGajiIbu', data.penghasilan_ibu);

        // Kontak
        setText('dNoHp', data.no_hp_wali);
        const waNum = data.no_hp_wali.replace(/[^0-9]/g, '');
        document.getElementById('dWaLink').href = 'https://wa.me/' + waNum;

        // Dokumen
        const dokumenSection = document.getElementById('dokumenSection');
        const docs = [
            { id: 'dFileKk', field: 'file_kk' },
            { id: 'dFileKtp', field: 'file_ktp_ortu' },
            { id: 'dFileAkta', field: 'file_akta' },
            { id: 'dFileIjazah', field: 'file_ijazah' }
        ];
        let hasDoc = false;
        docs.forEach(doc => {
            const el = document.getElementById(doc.id);
            if (data[doc.field]) {
                el.href = '../uploads/dokumen/' + data[doc.field];
                el.classList.remove('hidden');
                hasDoc = true;
            } else {
                el.classList.add('hidden');
            }
        });
        dokumenSection.classList.toggle('hidden', !hasDoc);

        // Meta
        document.getElementById('dCreatedAt').textContent = 'Didaftarkan pada: ' + new Date(data.created_at).toLocaleString('id-ID');

        openModal('detailModal');
    }

    function exportData() {
        window.location.href = 'export-pendaftaran.php?' + new URLSearchParams(window.location.search).toString();
    }

    // Excel-style Filter System
    let currentFilterCol = null;
    let columnFilters = {}; // {col: Set of selected values}
    const filterPopup = document.getElementById('filterPopup');
    const filterItems = document.getElementById('filterItems');
    const filterSearch = document.getElementById('filterSearch');

    function openFilter(col, btn) {
        currentFilterCol = col;
        filterSearch.value = '';

        // Position popup near button
        const rect = btn.getBoundingClientRect();
        filterPopup.style.top = (rect.bottom + window.scrollY + 5) + 'px';
        filterPopup.style.left = Math.min(rect.left, window.innerWidth - 270) + 'px';

        // Get unique values from column
        const values = new Set();
        document.querySelectorAll('tbody tr').forEach(row => {
            const cell = row.cells[col];
            if (cell) {
                const text = cell.textContent.trim();
                if (text && text !== '-') values.add(text);
            }
        });

        // Render checkboxes
        const sorted = Array.from(values).sort();
        const selected = columnFilters[col] || new Set(sorted);

        filterItems.innerHTML = sorted.map(val => `
            <label class="flex items-center gap-2 py-1 hover:bg-gray-50 cursor-pointer filter-item">
                <input type="checkbox" value="${escapeHtml(val)}" ${selected.has(val) ? 'checked' : ''} class="filter-checkbox">
                <span class="truncate">${escapeHtml(val)}</span>
            </label>
        `).join('');

        // Update button appearance if filter is active
        updateFilterButtons();

        filterPopup.classList.remove('hidden');
    }

    function closeFilterPopup() {
        filterPopup.classList.add('hidden');
        currentFilterCol = null;
    }

    function sortColumn(direction) {
        if (currentFilterCol === null) return;

        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const cellA = a.cells[currentFilterCol]?.textContent.trim().toLowerCase() || '';
            const cellB = b.cells[currentFilterCol]?.textContent.trim().toLowerCase() || '';

            // Try numeric comparison first
            const numA = parseFloat(cellA.replace(/[^\d.-]/g, ''));
            const numB = parseFloat(cellB.replace(/[^\d.-]/g, ''));

            if (!isNaN(numA) && !isNaN(numB)) {
                return direction === 'asc' ? numA - numB : numB - numA;
            }

            // String comparison
            if (direction === 'asc') {
                return cellA.localeCompare(cellB, 'id');
            } else {
                return cellB.localeCompare(cellA, 'id');
            }
        });

        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));

        closeFilterPopup();
    }

    function searchFilterItems() {
        const query = filterSearch.value.toLowerCase();
        document.querySelectorAll('.filter-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    }

    function selectAllFilter() {
        document.querySelectorAll('.filter-checkbox').forEach(cb => {
            if (cb.closest('.filter-item').style.display !== 'none') {
                cb.checked = true;
            }
        });
    }

    function clearFilter() {
        document.querySelectorAll('.filter-checkbox').forEach(cb => {
            cb.checked = false;
        });
    }

    function applyFilter() {
        const selected = new Set();
        document.querySelectorAll('.filter-checkbox:checked').forEach(cb => {
            selected.add(cb.value);
        });

        if (selected.size === document.querySelectorAll('.filter-checkbox').length) {
            delete columnFilters[currentFilterCol]; // All selected = no filter
        } else {
            columnFilters[currentFilterCol] = selected;
        }

        applyAllFilters();
        closeFilterPopup();
    }

    function applyAllFilters() {
        document.querySelectorAll('tbody tr').forEach(row => {
            let show = true;
            for (const [col, allowedValues] of Object.entries(columnFilters)) {
                const cell = row.cells[parseInt(col)];
                if (cell) {
                    const text = cell.textContent.trim();
                    if (!allowedValues.has(text)) {
                        show = false;
                        break;
                    }
                }
            }
            row.style.display = show ? '' : 'none';
        });
        updateFilterButtons();
    }

    function updateFilterButtons() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            const col = btn.getAttribute('onclick').match(/openFilter\((\d+)/)?.[1];
            if (col && columnFilters[col]) {
                btn.classList.add('text-primary');
                btn.classList.remove('text-gray-400');
            } else {
                btn.classList.remove('text-primary');
                btn.classList.add('text-gray-400');
            }
        });
    }

    function clearAllFilters() {
        columnFilters = {};
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = '';
        });
        updateFilterButtons();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close popup when clicking outside
    document.addEventListener('click', (e) => {
        if (!filterPopup.contains(e.target) && !e.target.closest('.filter-btn')) {
            closeFilterPopup();
        }
    });
</script>

<!-- Address Dropdown for Edit Modal -->
<script>
    let editAllProvinsi = [];
    let editAllKota = [];
    let editKecamatanData = [];
    let editKelurahanData = [];

    async function loadEditWilayah() {
        try {
            const provResponse = await fetch('../api/wilayah.php?type=provinsi');
            editAllProvinsi = await provResponse.json();

            // Populate provinsi dropdown
            const provSelect = document.getElementById('editProvinsi');
            provSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
            editAllProvinsi.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.dataset.id = p.id;
                opt.textContent = p.name;
                provSelect.appendChild(opt);
            });

            // Fetch all cities in PARALLEL
            const kotaPromises = editAllProvinsi.map(prov =>
                fetch('../api/wilayah.php?type=kota&id=' + prov.id)
                    .then(r => r.json())
                    .then(kotaList => {
                        if (Array.isArray(kotaList)) {
                            kotaList.forEach(kota => {
                                kota.provinsi_id = prov.id;
                                kota.provinsi_name = prov.name;
                                editAllKota.push(kota);
                            });
                        }
                    })
            );
            await Promise.all(kotaPromises);

            // Populate kota dropdown
            populateEditKotaDropdown();

            console.log('Admin: Loaded', editAllProvinsi.length, 'provinces and', editAllKota.length, 'cities');
        } catch (e) {
            console.error('Failed to load wilayah:', e);
        }
    }

    function populateEditKotaDropdown(provinsiName = null) {
        const kotaSelect = document.getElementById('editKotaKab');
        const currentKota = kotaSelect.value;
        kotaSelect.innerHTML = '<option value="">-- Pilih Kota/Kabupaten --</option>';

        let kotaList = editAllKota;
        if (provinsiName) {
            kotaList = editAllKota.filter(k => k.provinsi_name === provinsiName);
        }

        kotaList.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.name;
            opt.dataset.id = k.id;
            opt.dataset.provinsiName = k.provinsi_name;
            opt.textContent = provinsiName ? k.name : `${k.name} (${k.provinsi_name})`;
            if (k.name === currentKota) opt.selected = true;
            kotaSelect.appendChild(opt);
        });
    }

    function onEditProvinsiChange(select) {
        const provinsiName = select.value;
        populateEditKotaDropdown(provinsiName || null);
        resetEditKecamatan();
        resetEditKelurahan();
    }

    async function onEditKotaChange(select) {
        const selectedOption = select.options[select.selectedIndex];
        const kotaId = selectedOption?.dataset?.id;
        const provinsiName = selectedOption?.dataset?.provinsiName;

        if (kotaId) {
            const provSelect = document.getElementById('editProvinsi');
            if (!provSelect.value && provinsiName) {
                provSelect.value = provinsiName;
            }
            await loadEditKecamatan(kotaId);
        } else {
            resetEditKecamatan();
            resetEditKelurahan();
        }
    }

    async function onEditKecamatanChange(select) {
        const selectedOption = select.options[select.selectedIndex];
        const kecId = selectedOption?.dataset?.id;

        if (kecId) {
            await loadEditKelurahan(kecId);
        } else {
            resetEditKelurahan();
        }
    }

    async function loadEditKecamatan(kotaId, keepSelection = false) {
        try {
            const response = await fetch('../api/wilayah.php?type=kecamatan&id=' + kotaId);
            editKecamatanData = await response.json();

            const kecSelect = document.getElementById('editKecamatan');
            const currentKec = keepSelection ? kecSelect.value : '';
            kecSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
            editKecamatanData.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.name;
                opt.dataset.id = k.id;
                opt.textContent = k.name;
                if (k.name === currentKec) opt.selected = true;
                kecSelect.appendChild(opt);
            });
            kecSelect.disabled = false;

            if (!keepSelection) resetEditKelurahan();
        } catch (e) { console.error(e); }
    }

    async function loadEditKelurahan(kecamatanId, keepSelection = false) {
        try {
            const response = await fetch('../api/wilayah.php?type=kelurahan&id=' + kecamatanId);
            editKelurahanData = await response.json();

            const kelSelect = document.getElementById('editKelurahan');
            const currentKel = keepSelection ? kelSelect.value : '';
            kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';
            editKelurahanData.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.name;
                opt.dataset.id = k.id;
                opt.textContent = k.name;
                if (k.name === currentKel) opt.selected = true;
                kelSelect.appendChild(opt);
            });
            kelSelect.disabled = false;
        } catch (e) { console.error(e); }
    }

    function resetEditKecamatan() {
        const kec = document.getElementById('editKecamatan');
        kec.innerHTML = '<option value="">-- Pilih Provinsi & Kota dulu --</option>';
        kec.disabled = true;
        editKecamatanData = [];
    }

    function resetEditKelurahan() {
        const kel = document.getElementById('editKelurahan');
        kel.innerHTML = '<option value="">-- Pilih Kecamatan dulu --</option>';
        kel.disabled = true;
        editKelurahanData = [];
    }

    // Set dropdown values when opening edit modal
    async function setEditModalAlamat(provinsi, kotaKab, kecamatan, kelurahan) {
        // Set provinsi
        const provSelect = document.getElementById('editProvinsi');
        provSelect.value = provinsi || '';

        // Filter and set kota
        populateEditKotaDropdown(provinsi || null);
        const kotaSelect = document.getElementById('editKotaKab');
        kotaSelect.value = kotaKab || '';

        // Load and set kecamatan if kota exists
        if (kotaKab) {
            const matchingKota = editAllKota.find(k => k.name === kotaKab);
            if (matchingKota) {
                await loadEditKecamatan(matchingKota.id, true);
                const kecSelect = document.getElementById('editKecamatan');
                kecSelect.value = kecamatan || '';

                // Load and set kelurahan if kecamatan exists
                if (kecamatan) {
                    const matchingKec = editKecamatanData.find(k => k.name === kecamatan);
                    if (matchingKec) {
                        await loadEditKelurahan(matchingKec.id, true);
                        const kelSelect = document.getElementById('editKelurahan');
                        kelSelect.value = kelurahan || '';
                    }
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadEditWilayah();
    });
</script>

<!-- Notify Berkas via WhatsApp -->
<script>
    // Current modal data storage
    let currentPendaftarId = null;
    let currentPendaftarNama = null;
    let pendingWaId = null;

    // Called when opening detail modal - store the ID and nama
    function setCurrentPendaftar(id, nama) {
        currentPendaftarId = id;
        currentPendaftarNama = nama;
    }

    function showWaConfirmModal(id, nama) {
        pendingWaId = id;
        document.getElementById('waConfirmName').textContent = nama;
        openModal('waConfirmModal');
    }

    function confirmSendWaBerkas() {
        if (pendingWaId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <?= csrfField() ?>
                <input type="hidden" name="action" value="notify_berkas">
                <input type="hidden" name="id" value="${pendingWaId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        closeModal('waConfirmModal');
    }

    function notifyBerkasFromModal() {
        if (currentPendaftarId && currentPendaftarNama) {
            showWaConfirmModal(currentPendaftarId, currentPendaftarNama);
        } else {
            alert('Data pendaftar tidak ditemukan');
        }
    }

    function notifyBerkasFromEdit() {
        const editId = document.getElementById('editId').value;
        const editNama = document.getElementById('editNama').value;
        if (editId && editNama) {
            showWaConfirmModal(editId, editNama);
        } else {
            alert('Data pendaftar tidak ditemukan');
        }
    }
</script>

<!-- PDF Auto Compression -->
<script src="../js/pdf-compress.js"></script>
</body>

</html>