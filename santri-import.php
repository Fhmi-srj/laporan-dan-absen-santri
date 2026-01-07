<?php
/**
 * Santri Import Page
 * Import santri from Excel (.xlsx) - All columns from data_induk
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/vendor/autoload.php';
requireAdmin();

use PhpOffice\PhpSpreadsheet\IOFactory;

$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Import Santri';
$errors = [];
$successCount = 0;

// Define all columns in order - matching data_induk table
$columnDefinitions = [
    ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'required' => true],
    ['name' => 'kelas', 'label' => 'Kelas', 'required' => true],
    ['name' => 'quran', 'label' => 'Quran', 'required' => false],
    ['name' => 'kategori', 'label' => 'Kategori', 'required' => false],
    ['name' => 'nisn', 'label' => 'NISN', 'required' => false],
    ['name' => 'lembaga_sekolah', 'label' => 'Lembaga Sekolah', 'required' => false],
    ['name' => 'status', 'label' => 'Status', 'required' => false, 'default' => 'AKTIF'],
    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'required' => false],
    ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'required' => false, 'note' => 'Format: YYYY-MM-DD'],
    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'required' => false, 'note' => 'L / P'],
    ['name' => 'jumlah_saudara', 'label' => 'Jumlah Saudara', 'required' => false],
    ['name' => 'nomor_kk', 'label' => 'Nomor KK', 'required' => false],
    ['name' => 'nik', 'label' => 'NIK', 'required' => false],
    ['name' => 'kecamatan', 'label' => 'Kecamatan', 'required' => false],
    ['name' => 'kabupaten', 'label' => 'Kabupaten', 'required' => false],
    ['name' => 'alamat', 'label' => 'Alamat', 'required' => false],
    ['name' => 'asal_sekolah', 'label' => 'Asal Sekolah', 'required' => false],
    ['name' => 'status_mukim', 'label' => 'Status Mukim', 'required' => false],
    ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'required' => false],
    ['name' => 'tempat_lahir_ayah', 'label' => 'Tempat Lahir Ayah', 'required' => false],
    ['name' => 'tanggal_lahir_ayah', 'label' => 'Tanggal Lahir Ayah', 'required' => false, 'note' => 'Format: YYYY-MM-DD'],
    ['name' => 'nik_ayah', 'label' => 'NIK Ayah', 'required' => false],
    ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'required' => false],
    ['name' => 'penghasilan_ayah', 'label' => 'Penghasilan Ayah', 'required' => false],
    ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'required' => false],
    ['name' => 'tempat_lahir_ibu', 'label' => 'Tempat Lahir Ibu', 'required' => false],
    ['name' => 'tanggal_lahir_ibu', 'label' => 'Tanggal Lahir Ibu', 'required' => false, 'note' => 'Format: YYYY-MM-DD'],
    ['name' => 'nik_ibu', 'label' => 'NIK Ibu', 'required' => false],
    ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'required' => false],
    ['name' => 'penghasilan_ibu', 'label' => 'Penghasilan Ibu', 'required' => false],
    ['name' => 'no_wa_wali', 'label' => 'No. WA Wali', 'required' => false],
    ['name' => 'nomor_rfid', 'label' => 'Nomor RFID', 'required' => false],
    ['name' => 'nomor_pip', 'label' => 'Nomor PIP', 'required' => false],
    ['name' => 'sumber_info', 'label' => 'Sumber Info', 'required' => false],
    ['name' => 'prestasi', 'label' => 'Prestasi', 'required' => false],
    ['name' => 'tingkat_prestasi', 'label' => 'Tingkat Prestasi', 'required' => false],
    ['name' => 'juara_prestasi', 'label' => 'Juara Prestasi', 'required' => false],
];

// Handle Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_santri'])) {
    $file = $_FILES['file_santri'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading file';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'])) {
            $errors[] = 'Format file harus Excel (.xlsx atau .xls)';
        } else {
            try {
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                // Skip header row
                $header = array_shift($rows);

                $pdo->beginTransaction();
                $rowNum = 1;

                foreach ($rows as $data) {
                    $rowNum++;

                    // Skip empty rows
                    if (empty(array_filter($data))) {
                        continue;
                    }

                    // Map data to column names
                    $rowData = [];
                    foreach ($columnDefinitions as $index => $col) {
                        $value = isset($data[$index]) ? trim($data[$index]) : '';

                        // Apply default if empty and has default
                        if ($value === '' && isset($col['default'])) {
                            $value = $col['default'];
                        }

                        // Validate jenis_kelamin
                        if ($col['name'] === 'jenis_kelamin' && $value !== '' && !in_array(strtoupper($value), ['L', 'P'])) {
                            $value = '';
                        } elseif ($col['name'] === 'jenis_kelamin' && $value !== '') {
                            $value = strtoupper($value);
                        }

                        $rowData[$col['name']] = $value !== '' ? $value : null;
                    }

                    // Validate required fields
                    $requiredMissing = [];
                    foreach ($columnDefinitions as $col) {
                        if ($col['required'] && empty($rowData[$col['name']])) {
                            $requiredMissing[] = $col['label'];
                        }
                    }

                    if (!empty($requiredMissing)) {
                        $errors[] = "Baris $rowNum: " . implode(', ', $requiredMissing) . " wajib diisi";
                        continue;
                    }

                    // Build insert/update query
                    $columns = array_keys($rowData);
                    $placeholders = array_fill(0, count($columns), '?');
                    $values = array_values($rowData);

                    // Check if exists by nama_lengkap + kelas (or nisn if available)
                    if (!empty($rowData['nisn'])) {
                        $check = $pdo->prepare("SELECT id FROM data_induk WHERE nisn = ?");
                        $check->execute([$rowData['nisn']]);
                    } else {
                        $check = $pdo->prepare("SELECT id FROM data_induk WHERE nama_lengkap = ? AND kelas = ?");
                        $check->execute([$rowData['nama_lengkap'], $rowData['kelas']]);
                    }

                    if ($existing = $check->fetch()) {
                        // Update existing
                        $updateParts = [];
                        foreach ($columns as $col) {
                            $updateParts[] = "$col = ?";
                        }
                        $updateParts[] = "updated_at = NOW()";
                        $sql = "UPDATE data_induk SET " . implode(', ', $updateParts) . " WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        $values[] = $existing['id'];
                        $stmt->execute($values);
                    } else {
                        // Insert new
                        $columns[] = 'created_at';
                        $columns[] = 'updated_at';
                        $placeholders[] = 'NOW()';
                        $placeholders[] = 'NOW()';
                        $sql = "INSERT INTO data_induk (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($values);
                    }
                    $successCount++;
                }

                $pdo->commit();

                if ($successCount > 0) {
                    redirectWith('santri-import.php', 'success', "$successCount data santri berhasil diimport!");
                } elseif (empty($errors)) {
                    $errors[] = "Tidak ada data yang valid untuk diimport";
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<style>
    .column-table {
        font-size: 0.8rem;
    }

    .column-table th,
    .column-table td {
        padding: 0.4rem 0.6rem;
    }

    .badge-wajib {
        background: #dc3545;
        color: white;
        font-size: 0.65rem;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
    }

    .badge-opsional {
        background: #6c757d;
        color: white;
        font-size: 0.65rem;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
    }

    .note-text {
        font-size: 0.7rem;
        color: #6c757d;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .main-content h4 {
            font-size: 1.2rem;
        }

        .card-custom {
            padding: 1rem !important;
        }

        .card-custom h5 {
            font-size: 1rem;
        }

        .btn-lg {
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
        }

        .form-control-lg {
            font-size: 0.95rem;
            padding: 0.5rem 0.75rem;
        }
    }

    @media (max-width: 767.98px) {
        .main-content {
            padding: 0.75rem !important;
        }

        .main-content h4 {
            font-size: 1.1rem;
            margin-bottom: 1rem !important;
        }

        .card-custom {
            padding: 0.875rem !important;
            margin-bottom: 0.75rem;
        }

        .card-custom h5 {
            font-size: 0.95rem;
            margin-bottom: 0.75rem !important;
        }

        .card-custom h5 i {
            font-size: 0.9rem;
        }

        .table-responsive {
            max-height: 350px !important;
        }

        .column-table {
            font-size: 0.7rem;
        }

        .column-table th,
        .column-table td {
            padding: 0.3rem 0.4rem;
        }

        .column-table th:first-child,
        .column-table td:first-child {
            width: 35px !important;
        }

        .column-table th:last-child,
        .column-table td:last-child {
            width: 60px !important;
        }

        .badge-wajib,
        .badge-opsional {
            font-size: 0.55rem;
            padding: 0.1rem 0.3rem;
        }

        .note-text {
            font-size: 0.6rem;
        }

        .alert {
            font-size: 0.8rem;
            padding: 0.6rem;
        }

        .alert h6 {
            font-size: 0.85rem;
        }

        .alert ul {
            padding-left: 1.2rem !important;
            font-size: 0.75rem;
        }

        .btn-lg {
            padding: 0.5rem 0.875rem;
            font-size: 0.9rem;
        }

        .form-control-lg {
            font-size: 0.9rem;
            padding: 0.45rem 0.65rem;
        }

        .form-label {
            font-size: 0.85rem;
        }

        small.text-muted {
            font-size: 0.7rem;
        }

        hr {
            margin: 0.75rem 0 !important;
        }
    }

    @media (max-width: 575.98px) {
        .main-content {
            padding: 0.5rem !important;
        }

        .main-content h4 {
            font-size: 1rem;
        }

        .row.g-4 {
            --bs-gutter-y: 0.75rem;
        }

        .card-custom {
            padding: 0.75rem !important;
            border-radius: 8px;
        }

        .card-custom h5 {
            font-size: 0.9rem;
        }

        .column-table {
            font-size: 0.65rem;
        }

        .column-table th,
        .column-table td {
            padding: 0.25rem 0.3rem;
        }

        /* Hide keterangan column on very small screens */
        .column-table th:nth-child(3),
        .column-table td:nth-child(3) {
            display: none;
        }

        .table-responsive {
            max-height: 300px !important;
        }
    }
</style>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4"><i class="fas fa-file-import me-2"></i>Import Santri</h4>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-upload me-2 text-primary"></i>Upload File Excel</h5>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong><i class="fas fa-exclamation-triangle me-1"></i>Error:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach (array_slice($errors, 0, 10) as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                            <?php if (count($errors) > 10): ?>
                                <li><em>...dan <?= count($errors) - 10 ?> error lainnya</em></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label fw-bold">File Excel (.xlsx)</label>
                        <input type="file" name="file_santri" class="form-control form-control-lg" accept=".xlsx,.xls"
                            required>
                        <small class="text-muted">Format: Microsoft Excel (.xlsx atau .xls)</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-upload me-2"></i> Import Data Santri
                    </button>
                </form>

                <hr class="my-4">

                <div class="alert alert-info small mb-0">
                    <h6 class="fw-bold"><i class="fas fa-info-circle me-1"></i>Petunjuk:</h6>
                    <ul class="mb-0 ps-3">
                        <li>Baris pertama = Header (nama kolom)</li>
                        <li>Data dimulai dari baris kedua</li>
                        <li>Kolom <span class="badge badge-wajib">WAJIB</span> harus diisi</li>
                        <li>Kolom <span class="badge badge-opsional">Opsional</span> boleh dikosongkan</li>
                        <li>Data dengan NISN sama akan di-update</li>
                        <li>Data baru tanpa NISN dicek dari Nama + Kelas</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-table me-2 text-success"></i>Format Kolom Excel</h5>
                <p class="text-muted small mb-3">Semua kolom harus ada di file Excel (sesuai urutan). Isi data sesuai
                    kebutuhan.</p>

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-hover column-table mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th class="text-center" style="width: 50px;">Kolom</th>
                                <th>Nama Field</th>
                                <th>Keterangan</th>
                                <th class="text-center" style="width: 80px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $colLetters = range('A', 'Z');
                            $colIndex = 0;
                            foreach ($columnDefinitions as $col):
                                $letter = $colIndex < 26 ? $colLetters[$colIndex] : 'A' . $colLetters[$colIndex - 26];
                                $colIndex++;
                                ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $letter ?></td>
                                    <td>
                                        <strong><?= e($col['name']) ?></strong>
                                        <?php if (isset($col['note'])): ?>
                                            <br><span class="note-text"><?= e($col['note']) ?></span>
                                        <?php endif; ?>
                                        <?php if (isset($col['default'])): ?>
                                            <br><span class="note-text">Default: <?= e($col['default']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($col['label']) ?></td>
                                    <td class="text-center">
                                        <?php if ($col['required']): ?>
                                            <span class="badge badge-wajib">WAJIB</span>
                                        <?php else: ?>
                                            <span class="badge badge-opsional">Opsional</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-muted small">
                    <i class="fas fa-columns me-1"></i> Total: <strong><?= count($columnDefinitions) ?> kolom</strong>
                    (A - <?= $colIndex < 26 ? $colLetters[$colIndex - 1] : 'A' . $colLetters[$colIndex - 27] ?>)
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>