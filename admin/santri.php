<?php
/**
 * Admin: Data Induk Santri Management
 * Full CRUD with sortable columns and horizontal scroll
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Data Induk Santri';

// Handle document upload
function uploadDokumen($file, $folder, $oldFile = null)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK)
        return null;
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);
    if ($oldFile && file_exists(__DIR__ . '/../uploads/' . $oldFile))
        unlink(__DIR__ . '/../uploads/' . $oldFile);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf']))
        return null;
    $filename = uniqid() . '_' . time() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename))
        return $folder . '/' . $filename;
    return null;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $oldStmt = $pdo->prepare("SELECT * FROM data_induk WHERE id = ?");
        $oldStmt->execute([$id]);
        $oldData = $oldStmt->fetch();

        $fotoSantri = uploadDokumen($_FILES['foto_santri'] ?? null, 'foto_santri', $oldData['foto_santri'] ?? null) ?? ($oldData['foto_santri'] ?? null);
        $dokumenKK = uploadDokumen($_FILES['dokumen_kk'] ?? null, 'dokumen', $oldData['dokumen_kk'] ?? null) ?? ($oldData['dokumen_kk'] ?? null);
        $dokumenAkte = uploadDokumen($_FILES['dokumen_akte'] ?? null, 'dokumen', $oldData['dokumen_akte'] ?? null) ?? ($oldData['dokumen_akte'] ?? null);
        $dokumenKTP = uploadDokumen($_FILES['dokumen_ktp'] ?? null, 'dokumen', $oldData['dokumen_ktp'] ?? null) ?? ($oldData['dokumen_ktp'] ?? null);
        $dokumenIjazah = uploadDokumen($_FILES['dokumen_ijazah'] ?? null, 'dokumen', $oldData['dokumen_ijazah'] ?? null) ?? ($oldData['dokumen_ijazah'] ?? null);
        $dokumenSertifikat = uploadDokumen($_FILES['dokumen_sertifikat'] ?? null, 'dokumen', $oldData['dokumen_sertifikat'] ?? null) ?? ($oldData['dokumen_sertifikat'] ?? null);

        $stmt = $pdo->prepare("UPDATE data_induk SET nama_lengkap=?, kelas=?, quran=?, kategori=?, lembaga_sekolah=?, status=?, nisn=?, nik=?, nomor_kk=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, jumlah_saudara=?, asal_sekolah=?, status_mukim=?, kecamatan=?, kabupaten=?, alamat=?, nama_ayah=?, tempat_lahir_ayah=?, tanggal_lahir_ayah=?, nik_ayah=?, pekerjaan_ayah=?, penghasilan_ayah=?, nama_ibu=?, tempat_lahir_ibu=?, tanggal_lahir_ibu=?, nik_ibu=?, pekerjaan_ibu=?, penghasilan_ibu=?, no_wa_wali=?, nomor_rfid=?, nomor_pip=?, sumber_info=?, prestasi=?, tingkat_prestasi=?, juara_prestasi=?, foto_santri=?, dokumen_kk=?, dokumen_akte=?, dokumen_ktp=?, dokumen_ijazah=?, dokumen_sertifikat=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([
            $_POST['nama_lengkap'],
            $_POST['kelas'] ?: null,
            $_POST['quran'] ?: null,
            $_POST['kategori'] ?: null,
            $_POST['lembaga_sekolah'] ?: null,
            $_POST['status'] ?: 'AKTIF',
            $_POST['nisn'] ?: null,
            $_POST['nik'] ?: null,
            $_POST['nomor_kk'] ?: null,
            $_POST['tempat_lahir'] ?: null,
            $_POST['tanggal_lahir'] ?: null,
            $_POST['jenis_kelamin'] ?: null,
            $_POST['jumlah_saudara'] ?: 0,
            $_POST['asal_sekolah'] ?: null,
            $_POST['status_mukim'] ?: null,
            $_POST['kecamatan'] ?: null,
            $_POST['kabupaten'] ?: null,
            $_POST['alamat'] ?: null,
            $_POST['nama_ayah'] ?: null,
            $_POST['tempat_lahir_ayah'] ?: null,
            $_POST['tanggal_lahir_ayah'] ?: null,
            $_POST['nik_ayah'] ?: null,
            $_POST['pekerjaan_ayah'] ?: null,
            $_POST['penghasilan_ayah'] ?: null,
            $_POST['nama_ibu'] ?: null,
            $_POST['tempat_lahir_ibu'] ?: null,
            $_POST['tanggal_lahir_ibu'] ?: null,
            $_POST['nik_ibu'] ?: null,
            $_POST['pekerjaan_ibu'] ?: null,
            $_POST['penghasilan_ibu'] ?: null,
            $_POST['no_wa_wali'] ?: null,
            $_POST['nomor_rfid'] ?: null,
            $_POST['nomor_pip'] ?: null,
            $_POST['sumber_info'] ?: null,
            $_POST['prestasi'] ?: null,
            $_POST['tingkat_prestasi'] ?: null,
            $_POST['juara_prestasi'] ?: null,
            $fotoSantri,
            $dokumenKK,
            $dokumenAkte,
            $dokumenKTP,
            $dokumenIjazah,
            $dokumenSertifikat,
            $id
        ]);
        redirectWith('santri.php', 'success', 'Data santri berhasil diperbarui!');
    }
    if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM catatan_aktivitas WHERE siswa_id = ? AND deleted_at IS NULL");
        $checkStmt->execute([$_POST['id']]);
        if ($checkStmt->fetchColumn() > 0) {
            redirectWith('santri.php', 'error', 'Tidak dapat menghapus santri yang memiliki catatan aktivitas!');
        }
        // Soft delete - move to trash
        $stmt = $pdo->prepare("UPDATE data_induk SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        $stmt->execute([$user['id'], $_POST['id']]);
        logActivity('DELETE', 'data_induk', $_POST['id'], null, null, null, 'Hapus data santri ke trash');
        redirectWith('santri.php', 'success', 'Data santri dipindahkan ke trash!');
    }

    // CREATE new santri
    if ($_POST['action'] === 'create') {
        $fotoSantri = uploadDokumen($_FILES['foto_santri'] ?? null, 'foto_santri');
        $dokumenKK = uploadDokumen($_FILES['dokumen_kk'] ?? null, 'dokumen');
        $dokumenAkte = uploadDokumen($_FILES['dokumen_akte'] ?? null, 'dokumen');
        $dokumenKTP = uploadDokumen($_FILES['dokumen_ktp'] ?? null, 'dokumen');
        $dokumenIjazah = uploadDokumen($_FILES['dokumen_ijazah'] ?? null, 'dokumen');
        $dokumenSertifikat = uploadDokumen($_FILES['dokumen_sertifikat'] ?? null, 'dokumen');

        $stmt = $pdo->prepare("INSERT INTO data_induk (nama_lengkap, kelas, quran, kategori, lembaga_sekolah, status, nisn, nik, nomor_kk, tempat_lahir, tanggal_lahir, jenis_kelamin, jumlah_saudara, asal_sekolah, status_mukim, kecamatan, kabupaten, alamat, nama_ayah, tempat_lahir_ayah, tanggal_lahir_ayah, nik_ayah, pekerjaan_ayah, penghasilan_ayah, nama_ibu, tempat_lahir_ibu, tanggal_lahir_ibu, nik_ibu, pekerjaan_ibu, penghasilan_ibu, no_wa_wali, nomor_rfid, nomor_pip, sumber_info, prestasi, tingkat_prestasi, juara_prestasi, foto_santri, dokumen_kk, dokumen_akte, dokumen_ktp, dokumen_ijazah, dokumen_sertifikat, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $_POST['nama_lengkap'],
            $_POST['kelas'] ?: null,
            $_POST['quran'] ?: null,
            $_POST['kategori'] ?: null,
            $_POST['lembaga_sekolah'] ?: null,
            $_POST['status'] ?: 'AKTIF',
            $_POST['nisn'] ?: null,
            $_POST['nik'] ?: null,
            $_POST['nomor_kk'] ?: null,
            $_POST['tempat_lahir'] ?: null,
            $_POST['tanggal_lahir'] ?: null,
            $_POST['jenis_kelamin'] ?: null,
            $_POST['jumlah_saudara'] ?: 0,
            $_POST['asal_sekolah'] ?: null,
            $_POST['status_mukim'] ?: null,
            $_POST['kecamatan'] ?: null,
            $_POST['kabupaten'] ?: null,
            $_POST['alamat'] ?: null,
            $_POST['nama_ayah'] ?: null,
            $_POST['tempat_lahir_ayah'] ?: null,
            $_POST['tanggal_lahir_ayah'] ?: null,
            $_POST['nik_ayah'] ?: null,
            $_POST['pekerjaan_ayah'] ?: null,
            $_POST['penghasilan_ayah'] ?: null,
            $_POST['nama_ibu'] ?: null,
            $_POST['tempat_lahir_ibu'] ?: null,
            $_POST['tanggal_lahir_ibu'] ?: null,
            $_POST['nik_ibu'] ?: null,
            $_POST['pekerjaan_ibu'] ?: null,
            $_POST['penghasilan_ibu'] ?: null,
            $_POST['no_wa_wali'] ?: null,
            $_POST['nomor_rfid'] ?: null,
            $_POST['nomor_pip'] ?: null,
            $_POST['sumber_info'] ?: null,
            $_POST['prestasi'] ?: null,
            $_POST['tingkat_prestasi'] ?: null,
            $_POST['juara_prestasi'] ?: null,
            $fotoSantri,
            $dokumenKK,
            $dokumenAkte,
            $dokumenKTP,
            $dokumenIjazah,
            $dokumenSertifikat
        ]);

        $newId = $pdo->lastInsertId();
        logActivity('CREATE', 'data_induk', $newId, $_POST['nama_lengkap'], null, null, 'Tambah santri baru: ' . $_POST['nama_lengkap']);
        redirectWith('santri.php', 'success', 'Santri baru berhasil ditambahkan!');
    }
}

// Sorting
$sortCol = $_GET['sort'] ?? 'nama_lengkap';
$sortDir = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
$allowedCols = ['nama_lengkap', 'jenis_kelamin', 'kelas', 'quran', 'kategori', 'nisn', 'nik', 'nomor_kk', 'tempat_lahir', 'tanggal_lahir', 'lembaga_sekolah', 'asal_sekolah', 'status_mukim', 'nama_ayah', 'nama_ibu', 'no_wa_wali', 'nomor_rfid', 'status', 'alamat', 'kecamatan', 'kabupaten', 'nik_ayah', 'pekerjaan_ayah', 'nik_ibu', 'pekerjaan_ibu', 'jumlah_saudara', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'penghasilan_ayah', 'penghasilan_ibu'];
if (!in_array($sortCol, $allowedCols))
    $sortCol = 'nama_lengkap';

// Pagination & Search
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterKelas = $_GET['kelas'] ?? '';

$whereClause = 'deleted_at IS NULL';  // Only show non-deleted records
$params = [];
if ($search) {
    $whereClause .= " AND (nama_lengkap LIKE ? OR nisn LIKE ? OR nik LIKE ? OR no_wa_wali LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($filterStatus) {
    $whereClause .= " AND status = ?";
    $params[] = $filterStatus;
}
if ($filterKelas) {
    $whereClause .= " AND kelas = ?";
    $params[] = $filterKelas;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM data_induk WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT * FROM data_induk WHERE $whereClause ORDER BY $sortCol $sortDir LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$santriList = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT DISTINCT kelas FROM data_induk WHERE deleted_at IS NULL AND kelas IS NOT NULL ORDER BY kelas")->fetchAll(PDO::FETCH_COLUMN);

function sortLink($col, $label, $currentCol, $currentDir)
{
    $isActive = ($col === $currentCol);
    $newDir = ($isActive && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $params = $_GET;
    $params['sort'] = $col;
    $params['dir'] = $newDir;
    $activeClass = $isActive ? ' active' : '';
    $dirClass = $isActive ? ($currentDir === 'ASC' ? ' asc' : ' desc') : '';
    return '<a href="?' . http_build_query($params) . '" class="sort-header' . $activeClass . $dirClass . '">' . $label . ' <span class="sort-icons"><i class="fas fa-caret-up"></i><i class="fas fa-caret-down"></i></span></a>';
}
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<style>
    .table-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        max-width: 100%;
        max-height: 520px;
        /* Shows approximately 10 rows */
    }

    .table-santri thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-santri {
        font-size: 0.72rem;
        white-space: nowrap;
        min-width: 3500px;
        border-collapse: collapse;
    }

    .table-santri thead tr {
        background: #3b5998 !important;
    }

    .table-santri thead th {
        color: white !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.3px;
        padding: 0.6rem 0.5rem;
        border: 1px solid #2d4373 !important;
        background: #3b5998 !important;
    }

    .table-santri tbody td {
        padding: 0.45rem 0.5rem;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        background: white;
    }

    .table-santri tbody tr:hover td {
        background: #f1f5f9;
    }

    .sort-header {
        color: white !important;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
    }

    .sort-header:hover {
        color: #bfdbfe !important;
    }

    .sort-icons {
        display: inline-flex;
        flex-direction: column;
        font-size: 0.5rem;
        line-height: 0.45;
        opacity: 0.6;
    }

    .sort-header.active .sort-icons {
        opacity: 1;
    }

    .sort-header.asc .sort-icons .fa-caret-up {
        color: #fcd34d;
    }

    .sort-header.desc .sort-icons .fa-caret-down {
        color: #fcd34d;
    }

    .badge-doc {
        font-size: 0.6rem;
        padding: 0.15rem 0.35rem;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 5;
    }

    .sticky-col-end {
        position: sticky;
        right: 0;
        z-index: 5;
    }

    thead .sticky-col,
    thead .sticky-col-end {
        z-index: 15;
    }

    /* Modal Styles */
    #editModal .modal-dialog {
        max-height: 90vh;
        margin: 1.75rem auto;
    }

    #editModal .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    #editModal .modal-header {
        flex-shrink: 0;
        padding: 1rem 1.5rem;
    }

    #editModal .modal-body {
        overflow-y: hidden;
        flex: 1;
        padding: 0;
        max-height: calc(90vh - 130px);
        display: flex;
        flex-direction: column;
    }

    #editModal .modal-footer {
        flex-shrink: 0;
        padding: 1rem 1.5rem;
        border-top: 1px solid #dee2e6;
    }

    .nav-pills-custom {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        padding: 1rem 1.5rem 0 1.5rem;
        margin: 0 !important;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .nav-pills-custom .nav-link {
        border-radius: 0;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        padding: 0.75rem 1.25rem;
        background: none;
    }

    .nav-pills-custom .nav-link.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        font-weight: 600;
    }

    .tab-content {
        overflow-y: auto;
        padding: 1.5rem;
        flex: 1;
    }

    .form-section {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .form-section h6 {
        color: #3b82f6;
        margin-bottom: 1rem;
        font-weight: 600;
    }
</style>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1"><i class="fas fa-user-graduate me-2"></i>Data Induk Santri</h5>
            <small class="text-muted">Total: <?= $total ?> santri</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSantri">
            <i class="fas fa-plus me-1"></i>Tambah Santri
        </button>
    </div>

    <div class="card-custom p-3 mb-3">
        <form class="row g-2 align-items-end">
            <input type="hidden" name="sort" value="<?= e($sortCol) ?>">
            <input type="hidden" name="dir" value="<?= e($sortDir) ?>">
            <div class="col-md-4">
                <label class="form-label small text-muted">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama/NISN/NIK/WA..."
                    value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="AKTIF" <?= $filterStatus === 'AKTIF' ? 'selected' : '' ?>>Aktif</option>
                    <option value="NON-AKTIF" <?= $filterStatus === 'NON-AKTIF' ? 'selected' : '' ?>>Non-Aktif</option>
                    <option value="LULUS" <?= $filterStatus === 'LULUS' ? 'selected' : '' ?>>Lulus</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Kelas</label>
                <select name="kelas" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= e($k) ?>" <?= $filterKelas === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>

    <div class="card-custom">
        <div class="table-wrapper">
            <table class="table table-santri mb-0">
                <thead>
                    <tr>
                        <th class="sticky-col">NO</th>
                        <th><?= sortLink('nama_lengkap', 'NAMA CALON SISWA', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('kelas', 'KELAS', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('quran', 'QURAN', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('kategori', 'KATEGORI', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('nisn', 'NISN', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('lembaga_sekolah', 'LEMBAGA SEKOLAH', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('status', 'STATUS', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('tempat_lahir', 'TEMPAT LAHIR', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('tanggal_lahir', 'TANGGAL LAHIR', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('jenis_kelamin', 'JENIS KELAMIN', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('jumlah_saudara', 'JUMLAH SAUDARA KANDUNG', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('nomor_kk', 'NOMOR KK', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('nik', 'NOMOR NIK (SESUAI KK)', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('kecamatan', 'KECAMATAN', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('kabupaten', 'KABUPATEN', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('alamat', 'ALAMAT', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('asal_sekolah', 'ASAL SEKOLAH', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('status_mukim', 'STATUS MUKIM', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('nama_ayah', 'NAMA AYAH', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('tempat_lahir_ayah', 'KOTA KELAHIRAN AYAH', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('tanggal_lahir_ayah', 'TANGGAL KELAHIRAN AYAH', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('nik_ayah', 'NIK AYAH', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('pekerjaan_ayah', 'PEKERJAAN AYAH', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('penghasilan_ayah', 'PENGHASILAN PERBULAN', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('nama_ibu', 'NAMA IBU', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('tempat_lahir_ibu', 'KOTA KELAHIRAN IBU', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('tanggal_lahir_ibu', 'TANGGAL KELAHIRAN IBU', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('nik_ibu', 'NIK IBU', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('pekerjaan_ibu', 'PEKERJAAN IBU', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('penghasilan_ibu', 'PENGHASILAN PERBULAN', $sortCol, $sortDir) ?></th>
                        <th><?= sortLink('no_wa_wali', 'NOMER HP WALI YANG AKTIF (WHATSAPP)', $sortCol, $sortDir) ?>
                        </th>
                        <th><?= sortLink('nomor_rfid', 'RFID', $sortCol, $sortDir) ?></th>
                        <th>FOTO</th>
                        <th>KK</th>
                        <th>AKTE</th>
                        <th>KTP</th>
                        <th>IJAZAH</th>
                        <th>SERTIFIKAT</th>
                        <th class="sticky-col-end">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($santriList as $i => $s): ?>
                        <tr>
                            <td class="sticky-col"><?= $offset + $i + 1 ?></td>
                            <td><strong><?= e($s['nama_lengkap']) ?></strong></td>
                            <td><?= e($s['kelas'] ?? '-') ?></td>
                            <td><?= e($s['quran'] ?? '-') ?></td>
                            <td><?= e($s['kategori'] ?? '-') ?></td>
                            <td><code style="color:#dc3545"><?= e($s['nisn'] ?? '-') ?></code></td>
                            <td><?= e($s['lembaga_sekolah'] ?? '-') ?></td>
                            <td>
                                <?php if ($s['status'] === 'AKTIF'): ?><span class="badge bg-success badge-doc">Aktif</span>
                                <?php elseif ($s['status'] === 'LULUS'): ?><span
                                        class="badge bg-info badge-doc">Lulus</span>
                                <?php else: ?><span
                                        class="badge bg-secondary badge-doc"><?= e($s['status'] ?? '-') ?></span><?php endif; ?>
                            </td>
                            <td><?= e($s['tempat_lahir'] ?? '-') ?></td>
                            <td><?= $s['tanggal_lahir'] ? date('d/m/Y', strtotime($s['tanggal_lahir'])) : '-' ?></td>
                            <td><?= $s['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($s['jenis_kelamin'] == 'P' ? 'Perempuan' : '-') ?>
                            </td>
                            <td><?= e($s['jumlah_saudara'] ?? '-') ?></td>
                            <td><?= e($s['nomor_kk'] ?? '-') ?></td>
                            <td><?= e($s['nik'] ?? '-') ?></td>
                            <td><?= e($s['kecamatan'] ?? '-') ?></td>
                            <td><?= e($s['kabupaten'] ?? '-') ?></td>
                            <td title="<?= e($s['alamat'] ?? '') ?>">
                                <?= e(mb_substr($s['alamat'] ?? '-', 0, 30)) ?>
                                <?= mb_strlen($s['alamat'] ?? '') > 30 ? '...' : '' ?>
                            </td>
                            <td><?= e($s['asal_sekolah'] ?? '-') ?></td>
                            <td><?= e($s['status_mukim'] ?? '-') ?></td>
                            <td><?= e($s['nama_ayah'] ?? '-') ?></td>
                            <td><?= e($s['tempat_lahir_ayah'] ?? '-') ?></td>
                            <td><?= $s['tanggal_lahir_ayah'] ? date('d/m/Y', strtotime($s['tanggal_lahir_ayah'])) : '-' ?>
                            </td>
                            <td><?= e($s['nik_ayah'] ?? '-') ?></td>
                            <td><?= e($s['pekerjaan_ayah'] ?? '-') ?></td>
                            <td><?= e($s['penghasilan_ayah'] ?? '-') ?></td>
                            <td><?= e($s['nama_ibu'] ?? '-') ?></td>
                            <td><?= e($s['tempat_lahir_ibu'] ?? '-') ?></td>
                            <td><?= $s['tanggal_lahir_ibu'] ? date('d/m/Y', strtotime($s['tanggal_lahir_ibu'])) : '-' ?>
                            </td>
                            <td><?= e($s['nik_ibu'] ?? '-') ?></td>
                            <td><?= e($s['pekerjaan_ibu'] ?? '-') ?></td>
                            <td><?= e($s['penghasilan_ibu'] ?? '-') ?></td>
                            <td><?= e($s['no_wa_wali'] ?? '-') ?></td>
                            <td><?= $s['nomor_rfid'] ? '<span class="badge bg-success badge-doc">Ada</span>' : '-' ?></td>
                            <td><?= $s['foto_santri'] ? '<span class="badge bg-success badge-doc">Ada</span>' : '-' ?></td>
                            <td><?= $s['dokumen_kk'] ? '<span class="badge bg-success badge-doc">Ada</span>' : '-' ?></td>
                            <td><?= $s['dokumen_akte'] ? '<span class="badge bg-success badge-doc">Ada</span>' : '-' ?></td>
                            <td><?= $s['dokumen_ktp'] ? '<span class="badge bg-success badge-doc">Ada</span>' : '-' ?></td>
                            <td><?= $s['dokumen_ijazah'] ? '<span class="badge bg-success badge-doc">Ada</span>' : '-' ?>
                            </td>
                            <td><?= $s['dokumen_sertifikat'] ? '<span class="badge bg-success badge-doc">Ada</span>' : '-' ?>
                            </td>
                            <td class="sticky-col-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-primary btn-sm" onclick="editSantri(<?= $s['id'] ?>)"
                                        title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="hapusSantri(<?= $s['id'] ?>, '<?= e(addslashes($s['nama_lengkap'])) ?>')"
                                        title="Hapus"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($santriList)): ?>
                        <tr>
                            <td colspan="40" class="text-center py-4 text-muted">Tidak ada data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">
                        <?php for ($p = max(1, $page - 3); $p <= min($totalPages, $page + 3); $p++): ?>
                            <?php $pParams = $_GET;
                            $pParams['page'] = $p; ?>
                            <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query($pParams) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Data Santri</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-pills-custom mb-4" id="editTabs">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#tab-santri"><i
                                    class="fas fa-user me-1"></i> Data Santri</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-ortu"><i
                                    class="fas fa-users me-1"></i> Data Orang Tua</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-dokumen"><i
                                    class="fas fa-file me-1"></i> Dokumen</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-santri">
                            <div class="form-section">
                                <h6><i class="fas fa-id-card me-2"></i>Identitas</h6>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Nama Lengkap <span
                                                class="text-danger">*</span></label><input type="text"
                                            name="nama_lengkap" id="edit_nama_lengkap" class="form-control" required>
                                    </div>
                                    <div class="col-md-3"><label class="form-label">NISN</label><input type="text"
                                            name="nisn" id="edit_nisn" class="form-control" placeholder="Angka saja">
                                    </div>
                                    <div class="col-md-3"><label class="form-label">NIK (Sesuai KK)</label><input
                                            type="text" name="nik" id="edit_nik" class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Nomor KK</label><input type="text"
                                            name="nomor_kk" id="edit_nomor_kk" class="form-control"
                                            placeholder="Angka saja"></div>
                                    <div class="col-md-4"><label class="form-label">Tempat Lahir</label><input
                                            type="text" name="tempat_lahir" id="edit_tempat_lahir" class="form-control">
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Tanggal Lahir</label><input
                                            type="date" name="tanggal_lahir" id="edit_tanggal_lahir"
                                            class="form-control"></div>
                                    <div class="col-md-3"><label class="form-label">Jenis Kelamin <span
                                                class="text-danger">*</span></label>
                                        <select name="jenis_kelamin" id="edit_jenis_kelamin" class="form-select"
                                            required>
                                            <option value="">- Pilih -</option>
                                            <option value="L">L (Laki-laki)</option>
                                            <option value="P">P (Perempuan)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3"><label class="form-label">Jumlah Saudara Kandung</label><input
                                            type="number" name="jumlah_saudara" id="edit_jumlah_saudara"
                                            class="form-control" min="0"></div>
                                </div>
                            </div>
                            <div class="form-section">
                                <h6><i class="fas fa-school me-2"></i>Pendidikan</h6>
                                <div class="row g-3">
                                    <div class="col-md-3"><label class="form-label">Lembaga Sekolah <span
                                                class="text-danger">*</span></label>
                                        <select name="lembaga_sekolah" id="edit_lembaga_sekolah" class="form-select"
                                            required>
                                            <option value="">- Pilih -</option>
                                            <option value="SMP NU BP">SMP NU BP</option>
                                            <option value="MA ALHIKAM">MA ALHIKAM</option>
                                            <option value="ITS">ITS</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3"><label class="form-label">Kelas</label><input type="text"
                                            name="kelas" id="edit_kelas" class="form-control"></div>
                                    <div class="col-md-3"><label class="form-label">Quran</label><input type="text"
                                            name="quran" id="edit_quran" class="form-control"></div>
                                    <div class="col-md-3"><label class="form-label">Kategori</label><input type="text"
                                            name="kategori" id="edit_kategori" class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Status</label>
                                        <select name="status" id="edit_status" class="form-select">
                                            <option value="AKTIF">AKTIF</option>
                                            <option value="NON-AKTIF">NON-AKTIF</option>
                                            <option value="LULUS">LULUS</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Asal Sekolah</label><input
                                            type="text" name="asal_sekolah" id="edit_asal_sekolah" class="form-control"
                                            placeholder="Contoh: SD N 1 Kedungwuni"></div>
                                    <div class="col-md-4"><label class="form-label">Status Mukim <span
                                                class="text-danger">*</span></label>
                                        <select name="status_mukim" id="edit_status_mukim" class="form-select" required>
                                            <option value="">- Pilih -</option>
                                            <option value="PONDOK PP MAMBAUL HUDA">PONDOK PP MAMBAUL HUDA</option>
                                            <option value="PONDOK SELAIN PP MAMBAUL HUDA">PONDOK SELAIN PP MAMBAUL HUDA
                                            </option>
                                            <option value="TIDAK PONDOK">TIDAK PONDOK</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <h6><i class="fas fa-map-marker-alt me-2"></i>Alamat & Kontak</h6>
                                <div class="row g-3">
                                    <div class="col-12"><label class="form-label">Alamat Lengkap <span
                                                class="text-danger">*</span></label><textarea name="alamat"
                                            id="edit_alamat" class="form-control" rows="2" required
                                            placeholder="Isi dengan alamat lengkap; jalan, dukuh, desa, RT/RW"></textarea>
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Kecamatan</label><input type="text"
                                            name="kecamatan" id="edit_kecamatan" class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Kabupaten</label><input type="text"
                                            name="kabupaten" id="edit_kabupaten" class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">No WA Wali</label><input type="text"
                                            name="no_wa_wali" id="edit_no_wa_wali" class="form-control"
                                            placeholder="Contoh: 08123456789"></div>
                                    <div class="col-md-4"><label class="form-label">Nomor RFID</label><input type="text"
                                            name="nomor_rfid" id="edit_nomor_rfid" class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">No. PIP/PKH</label><input
                                            type="text" name="nomor_pip" id="edit_nomor_pip" class="form-control"
                                            placeholder="Isi jika ada"></div>
                                    <div class="col-md-4"><label class="form-label">Sumber Info</label>
                                        <select name="sumber_info" id="edit_sumber_info" class="form-select">
                                            <option value="">- Pilih -</option>
                                            <option value="TETANGGA/SAUDARA">TETANGGA/SAUDARA</option>
                                            <option value="SPANDUK">SPANDUK</option>
                                            <option value="SOSMED ( FB, IG, TIKTOK )">SOSMED (FB, IG, TIKTOK)</option>
                                            <option value="RADIO">RADIO</option>
                                            <option value="LAINNYA">LAINNYA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-ortu">
                            <div class="form-section">
                                <h6><i class="fas fa-male me-2"></i>Data Ayah</h6>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Nama Ayah</label><input type="text"
                                            name="nama_ayah" id="edit_nama_ayah" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">NIK Ayah</label><input type="text"
                                            name="nik_ayah" id="edit_nik_ayah" class="form-control"
                                            placeholder="Sesuai KK"></div>
                                    <div class="col-md-4"><label class="form-label">Kota Kelahiran Ayah</label><input
                                            type="text" name="tempat_lahir_ayah" id="edit_tempat_lahir_ayah"
                                            class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Tanggal Kelahiran Ayah</label><input
                                            type="date" name="tanggal_lahir_ayah" id="edit_tanggal_lahir_ayah"
                                            class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Pekerjaan Ayah</label><input
                                            type="text" name="pekerjaan_ayah" id="edit_pekerjaan_ayah"
                                            class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Penghasilan Ayah Perbulan</label>
                                        <select name="penghasilan_ayah" id="edit_penghasilan_ayah" class="form-select">
                                            <option value="">- Pilih -</option>
                                            <option value="Di bawah Rp. 1.000.000">Di bawah Rp. 1.000.000</option>
                                            <option value="Di bawah Rp. 2.500.000">Di bawah Rp. 2.500.000</option>
                                            <option value="Di bawah Rp. 4.000.000">Di bawah Rp. 4.000.000</option>
                                            <option value="Di atas Rp. 4.000.000">Di atas Rp. 4.000.000</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <h6><i class="fas fa-female me-2"></i>Data Ibu</h6>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Nama Ibu</label><input type="text"
                                            name="nama_ibu" id="edit_nama_ibu" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label">NIK Ibu</label><input type="text"
                                            name="nik_ibu" id="edit_nik_ibu" class="form-control"
                                            placeholder="Sesuai KK"></div>
                                    <div class="col-md-4"><label class="form-label">Kota Kelahiran Ibu</label><input
                                            type="text" name="tempat_lahir_ibu" id="edit_tempat_lahir_ibu"
                                            class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Tanggal Kelahiran Ibu</label><input
                                            type="date" name="tanggal_lahir_ibu" id="edit_tanggal_lahir_ibu"
                                            class="form-control"></div>
                                    <div class="col-md-4"><label class="form-label">Pekerjaan Ibu</label><input
                                            type="text" name="pekerjaan_ibu" id="edit_pekerjaan_ibu"
                                            class="form-control" placeholder="Ibu rumah tangga jika tidak bekerja">
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Penghasilan Ibu Perbulan</label>
                                        <select name="penghasilan_ibu" id="edit_penghasilan_ibu" class="form-select">
                                            <option value="">- Pilih -</option>
                                            <option value="Di bawah Rp. 1.000.000">Di bawah Rp. 1.000.000</option>
                                            <option value="Di bawah Rp. 2.500.000">Di bawah Rp. 2.500.000</option>
                                            <option value="Di bawah Rp. 4.000.000">Di bawah Rp. 4.000.000</option>
                                            <option value="Di atas Rp. 4.000.000">Di atas Rp. 4.000.000</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-dokumen">
                            <div class="form-section">
                                <h6><i class="fas fa-camera me-2"></i>Foto & Dokumen</h6>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Foto Santri</label>
                                        <style>
                                            .photo-upload-wrapper-santri {
                                                border: 2px dashed #e2e8f0;
                                                border-radius: 8px;
                                                padding: 15px;
                                                text-align: center;
                                                background: #f8fafc;
                                                transition: all 0.3s;
                                            }

                                            .photo-upload-wrapper-santri:hover {
                                                border-color: #3b82f6;
                                            }

                                            .photo-upload-wrapper-santri.has-preview {
                                                border-style: solid;
                                                border-color: #10b981;
                                                background: #ecfdf5;
                                            }

                                            .photo-upload-btns-santri {
                                                display: flex;
                                                gap: 8px;
                                                justify-content: center;
                                                flex-wrap: wrap;
                                            }

                                            .btn-photo-santri {
                                                display: inline-flex;
                                                align-items: center;
                                                gap: 6px;
                                                padding: 8px 16px;
                                                border-radius: 8px;
                                                font-weight: 600;
                                                font-size: 0.75rem;
                                                cursor: pointer;
                                                transition: all 0.2s;
                                                border: none;
                                            }

                                            .btn-camera-santri {
                                                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                                                color: white;
                                            }

                                            .btn-file-santri {
                                                background: white;
                                                color: #475569;
                                                border: 1px solid #e2e8f0 !important;
                                            }

                                            .photo-preview-santri {
                                                position: relative;
                                                display: inline-block;
                                                margin-top: 10px;
                                            }

                                            .photo-preview-santri img {
                                                max-width: 100%;
                                                max-height: 100px;
                                                border-radius: 8px;
                                            }

                                            .btn-remove-santri {
                                                position: absolute;
                                                top: -6px;
                                                right: -6px;
                                                width: 22px;
                                                height: 22px;
                                                border-radius: 50%;
                                                background: #ef4444;
                                                color: white;
                                                border: 2px solid white;
                                                cursor: pointer;
                                                font-size: 0.65rem;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                            }

                                            @media (max-width: 768px) {
                                                .photo-upload-btns-santri {
                                                    flex-direction: column;
                                                }

                                                .btn-photo-santri {
                                                    width: 100%;
                                                    justify-content: center;
                                                }
                                            }
                                        </style>
                                        <div class="photo-upload-wrapper-santri" id="wrapper_foto_santri">
                                            <input type="file" name="foto_santri" id="input_foto_santri" class="d-none"
                                                accept="image/*">
                                            <input type="file" id="camera_foto_santri" class="d-none" accept="image/*"
                                                capture="environment">
                                            <div class="photo-upload-btns-santri" id="buttons_foto_santri">
                                                <button type="button" class="btn-photo-santri btn-camera-santri"
                                                    onclick="document.getElementById('camera_foto_santri').click()">
                                                    <i class="fas fa-camera"></i> Ambil Foto
                                                </button>
                                                <button type="button" class="btn-photo-santri btn-file-santri"
                                                    onclick="document.getElementById('input_foto_santri').click()">
                                                    <i class="fas fa-folder-open"></i> Pilih File
                                                </button>
                                            </div>
                                            <div class="photo-preview-santri d-none" id="container_new_foto_santri">
                                                <img id="preview_new_foto_santri" alt="Preview">
                                                <button type="button" class="btn-remove-santri"
                                                    onclick="removeFotoSantri()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="preview_foto_santri" class="mt-1"></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Dokumen KK</label><input type="file"
                                            name="dokumen_kk" class="form-control form-control-sm"
                                            accept="image/*,.pdf">
                                        <div id="preview_dokumen_kk" class="mt-1"></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Dokumen Akta</label><input
                                            type="file" name="dokumen_akte" class="form-control form-control-sm"
                                            accept="image/*,.pdf">
                                        <div id="preview_dokumen_akte" class="mt-1"></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Dokumen KTP Wali</label><input
                                            type="file" name="dokumen_ktp" class="form-control form-control-sm"
                                            accept="image/*,.pdf">
                                        <div id="preview_dokumen_ktp" class="mt-1"></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Dokumen Ijazah</label><input
                                            type="file" name="dokumen_ijazah" class="form-control form-control-sm"
                                            accept="image/*,.pdf">
                                        <div id="preview_dokumen_ijazah" class="mt-1"></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Sertifikat/Piagam
                                            Prestasi</label><input type="file" name="dokumen_sertifikat"
                                            class="form-control form-control-sm" accept="image/*,.pdf">
                                        <div id="preview_dokumen_sertifikat" class="mt-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <h6><i class="fas fa-trophy me-2"></i>Data Prestasi (Opsional)</h6>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Nama Prestasi</label><input
                                            type="text" name="prestasi" id="edit_prestasi" class="form-control"
                                            placeholder="Contoh: Juara MTQ"></div>
                                    <div class="col-md-3"><label class="form-label">Tingkat</label>
                                        <select name="tingkat_prestasi" id="edit_tingkat_prestasi" class="form-select">
                                            <option value="">- Pilih -</option>
                                            <option value="KABUPATEN">KABUPATEN</option>
                                            <option value="PROVINSI">PROVINSI</option>
                                            <option value="NASIONAL">NASIONAL</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3"><label class="form-label">Juara Ke</label>
                                        <select name="juara_prestasi" id="edit_juara_prestasi" class="form-select">
                                            <option value="">- Pilih -</option>
                                            <option value="1 ( Satu )">1 (Satu)</option>
                                            <option value="2 ( Dua )">2 (Dua)</option>
                                            <option value="3 ( Tiga )">3 (Tiga)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
    let editModal;
    document.addEventListener('DOMContentLoaded', () => { editModal = new bootstrap.Modal(document.getElementById('editModal')); });

    function editSantri(id) {
        fetch('../api/ambil-santri.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) { populateForm(data.data); editModal.show(); }
                else Swal.fire('Error', data.message || 'Gagal memuat data', 'error');
            })
            .catch(() => Swal.fire('Error', 'Gagal memuat data santri', 'error'));
    }

    function populateForm(d) {
        // Text/Date/Number fields
        const textFields = ['id', 'nama_lengkap', 'nisn', 'nik', 'nomor_kk', 'tempat_lahir', 'tanggal_lahir', 'jumlah_saudara', 'kelas', 'quran', 'kategori', 'asal_sekolah', 'alamat', 'kecamatan', 'kabupaten', 'no_wa_wali', 'nomor_rfid', 'nomor_pip', 'nama_ayah', 'nik_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'pekerjaan_ayah', 'nama_ibu', 'nik_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'pekerjaan_ibu', 'prestasi'];
        textFields.forEach(f => { const el = document.getElementById('edit_' + f); if (el) el.value = d[f] || ''; });

        // Dropdown/Select fields - set selected value
        const selectFields = ['jenis_kelamin', 'status', 'lembaga_sekolah', 'status_mukim', 'sumber_info', 'penghasilan_ayah', 'penghasilan_ibu', 'tingkat_prestasi', 'juara_prestasi'];
        selectFields.forEach(f => {
            const el = document.getElementById('edit_' + f);
            if (el) el.value = d[f] || '';
        });

        // Document previews
        ['foto_santri', 'dokumen_kk', 'dokumen_akte', 'dokumen_ktp', 'dokumen_ijazah', 'dokumen_sertifikat'].forEach(f => {
            const c = document.getElementById('preview_' + f);
            if (c && d[f]) {
                const ext = d[f].split('.').pop().toLowerCase(), url = '../uploads/' + d[f];
                c.innerHTML = ['jpg', 'jpeg', 'png', 'gif'].includes(ext)
                    ? `<img src="${url}" style="max-width:60px;max-height:40px;border-radius:4px;cursor:pointer" onclick="window.open('${url}')">`
                    : `<a href="${url}" target="_blank" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-file"></i></a>`;
        });
        // Reset foto santri upload when modal loads
        resetFotoSantriUpload();
        new bootstrap.Tab(document.querySelector('#editTabs .nav-link')).show();
    }

    // Foto Santri handlers
    document.getElementById('input_foto_santri').addEventListener('change', function() {
        handleFotoSantri(this);
    });
    document.getElementById('camera_foto_santri').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            let mainInput = document.getElementById('input_foto_santri');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(this.files[0]);
            mainInput.files = dataTransfer.files;
            handleFotoSantri(mainInput);
        }
    });
    
    function handleFotoSantri(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_new_foto_santri').src = e.target.result;
                document.getElementById('container_new_foto_santri').classList.remove('d-none');
                document.getElementById('buttons_foto_santri').classList.add('d-none');
                document.getElementById('wrapper_foto_santri').classList.add('has-preview');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function removeFotoSantri() {
        document.getElementById('input_foto_santri').value = '';
        document.getElementById('camera_foto_santri').value = '';
        document.getElementById('preview_new_foto_santri').src = '';
        document.getElementById('container_new_foto_santri').classList.add('d-none');
        document.getElementById('buttons_foto_santri').classList.remove('d-none');
        document.getElementById('wrapper_foto_santri').classList.remove('has-preview');
    }
    
    function resetFotoSantriUpload() {
        removeFotoSantri();
    }

    function hapusSantri(id, nama) {
        Swal.fire({
            title: 'Hapus Santri?',
            html: `Hapus <strong>${nama}</strong>?<br><small class="text-danger">Data tidak dapat dikembalikan!</small>`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
        }).then(r => { if (r.isConfirmed) { document.getElementById('delete_id').value = id; document.getElementById('deleteForm').submit(); } });
    }
</script>

<!-- Modal Tambah Santri -->
<div class="modal fade" id="modalTambahSantri" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Santri Baru</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="form-section">
                        <h6><i class="fas fa-user me-2"></i>Data Pribadi</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label><input type="text" name="nama_lengkap"
                                    class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">NISN</label><input type="text" name="nisn"
                                    class="form-control" maxlength="10"></div>
                            <div class="col-md-4"><label class="form-label">NIK</label><input type="text" name="nik"
                                    class="form-control" maxlength="16"></div>
                            <div class="col-md-4"><label class="form-label">Tempat Lahir</label><input type="text"
                                    name="tempat_lahir" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Tanggal Lahir</label><input type="date"
                                    name="tanggal_lahir" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Jenis Kelamin <span
                                        class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="">- Pilih -</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Lembaga Sekolah <span
                                        class="text-danger">*</span></label>
                                <select name="lembaga_sekolah" class="form-select" required>
                                    <option value="">- Pilih -</option>
                                    <option value="SMP NU BP">SMP NU BP</option>
                                    <option value="MA ALHIKAM">MA ALHIKAM</option>
                                    <option value="ITS">ITS</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Status Mukim <span
                                        class="text-danger">*</span></label>
                                <select name="status_mukim" class="form-select" required>
                                    <option value="">- Pilih -</option>
                                    <option value="PONDOK PP MAMBAUL HUDA">PONDOK PP MAMBAUL HUDA</option>
                                    <option value="PONDOK SELAIN PP MAMBAUL HUDA">PONDOK SELAIN PP MAMBAUL HUDA</option>
                                    <option value="TIDAK PONDOK">TIDAK PONDOK</option>
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label">Kelas</label><input type="text" name="kelas"
                                    class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Quran</label><input type="text" name="quran"
                                    class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Kategori</label><input type="text"
                                    name="kategori" class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="AKTIF">AKTIF</option>
                                    <option value="NON-AKTIF">NON-AKTIF</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6><i class="fas fa-map-marker-alt me-2"></i>Alamat & Kontak</h6>
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label">Alamat</label><textarea name="alamat"
                                    class="form-control" rows="2"></textarea></div>
                            <div class="col-md-4"><label class="form-label">Kecamatan</label><input type="text"
                                    name="kecamatan" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Kabupaten</label><input type="text"
                                    name="kabupaten" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">No. WA Wali</label><input type="text"
                                    name="no_wa_wali" class="form-control" placeholder="628xxxxxxxxxx"></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6><i class="fas fa-users me-2"></i>Data Orang Tua</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Nama Ayah</label><input type="text"
                                    name="nama_ayah" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Nama Ibu</label><input type="text"
                                    name="nama_ibu" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Pekerjaan Ayah</label><input type="text"
                                    name="pekerjaan_ayah" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Pekerjaan Ibu</label><input type="text"
                                    name="pekerjaan_ibu" class="form-control"></div>
                        </div>
                    </div>
                    <!-- Hidden optional fields -->
                    <input type="hidden" name="nomor_kk" value="">
                    <input type="hidden" name="jumlah_saudara" value="0">
                    <input type="hidden" name="asal_sekolah" value="">
                    <input type="hidden" name="tempat_lahir_ayah" value="">
                    <input type="hidden" name="tanggal_lahir_ayah" value="">
                    <input type="hidden" name="nik_ayah" value="">
                    <input type="hidden" name="penghasilan_ayah" value="">
                    <input type="hidden" name="tempat_lahir_ibu" value="">
                    <input type="hidden" name="tanggal_lahir_ibu" value="">
                    <input type="hidden" name="nik_ibu" value="">
                    <input type="hidden" name="penghasilan_ibu" value="">
                    <input type="hidden" name="nomor_rfid" value="">
                    <input type="hidden" name="nomor_pip" value="">
                    <input type="hidden" name="sumber_info" value="">
                    <input type="hidden" name="prestasi" value="">
                    <input type="hidden" name="tingkat_prestasi" value="">
                    <input type="hidden" name="juara_prestasi" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>