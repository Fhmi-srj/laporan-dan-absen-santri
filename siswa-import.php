<?php
/**
 * Siswa Import Page
 * Import siswa from Excel/CSV
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireAdmin();

$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Import Siswa';
$errors = [];
$successCount = 0;

// Handle Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_siswa'])) {
    $file = $_FILES['file_siswa'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading file';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['csv', 'txt'])) {
            $errors[] = 'Format file harus CSV';
        } else {
            $handle = fopen($file['tmp_name'], 'r');
            $header = fgetcsv($handle); // Skip header row

            $pdo->beginTransaction();
            try {
                $row = 1;
                while (($data = fgetcsv($handle)) !== false) {
                    $row++;

                    // Expected columns: nama_lengkap, nomor_induk, kelas, alamat, no_wa, no_wa_wali
                    if (count($data) < 3) {
                        $errors[] = "Baris $row: Data tidak lengkap";
                        continue;
                    }

                    $namaLengkap = trim($data[0] ?? '');
                    $nomorInduk = trim($data[1] ?? '');
                    $kelas = trim($data[2] ?? '');
                    $alamat = trim($data[3] ?? '');
                    $noWa = trim($data[4] ?? '');
                    $noWaWali = trim($data[5] ?? '');

                    if (empty($namaLengkap) || empty($nomorInduk) || empty($kelas)) {
                        $errors[] = "Baris $row: Nama, Nomor Induk, atau Kelas kosong";
                        continue;
                    }

                    // Check if nomor_induk exists
                    $check = $pdo->prepare("SELECT id FROM siswa WHERE nomor_induk = ?");
                    $check->execute([$nomorInduk]);

                    if ($check->fetch()) {
                        // Update existing
                        $stmt = $pdo->prepare("UPDATE siswa SET nama_lengkap=?, kelas=?, alamat=?, no_wa=?, no_wa_wali=?, updated_at=NOW() WHERE nomor_induk=?");
                        $stmt->execute([$namaLengkap, $kelas, $alamat, $noWa, $noWaWali, $nomorInduk]);
                    } else {
                        // Insert new
                        $stmt = $pdo->prepare("INSERT INTO siswa (nama_lengkap, nomor_induk, kelas, alamat, no_wa, no_wa_wali, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                        $stmt->execute([$namaLengkap, $nomorInduk, $kelas, $alamat, $noWa, $noWaWali]);
                    }
                    $successCount++;
                }

                $pdo->commit();
                fclose($handle);

                if ($successCount > 0) {
                    redirectWith('siswa-import.php', 'success', "$successCount data siswa berhasil diimport!");
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4"><i class="fas fa-file-import me-2"></i>Import Siswa</h4>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4">Upload File CSV</h5>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $err): ?>
                                <li>
                                    <?= e($err) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label">File CSV</label>
                        <input type="file" name="file_siswa" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted">Format: CSV dengan pemisah koma</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import Data
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4">Format File</h5>
                <p class="text-muted">File CSV harus memiliki kolom berikut (sesuai urutan):</p>
                <ol class="text-muted">
                    <li><strong>nama_lengkap</strong> - Nama lengkap siswa (wajib)</li>
                    <li><strong>nomor_induk</strong> - NIS/Nomor Induk (wajib, unik)</li>
                    <li><strong>kelas</strong> - Kelas siswa (wajib)</li>
                    <li><strong>alamat</strong> - Alamat (opsional)</li>
                    <li><strong>no_wa</strong> - No. WA Siswa (opsional)</li>
                    <li><strong>no_wa_wali</strong> - No. WA Wali (opsional)</li>
                </ol>

                <hr>

                <h6 class="fw-bold">Contoh Isi File:</h6>
                <pre class="bg-light p-3 rounded small">nama_lengkap,nomor_induk,kelas,alamat,no_wa,no_wa_wali
Ahmad Fauzi,2024001,X-A,Jl. Contoh 1,081234567890,081234567891
Budi Santoso,2024002,X-A,Jl. Contoh 2,081234567892,081234567893</pre>

                <a href="data:text/csv;charset=utf-8,nama_lengkap,nomor_induk,kelas,alamat,no_wa,no_wa_wali%0AAhmad%20Fauzi,2024001,X-A,Jl.%20Contoh%201,081234567890,081234567891%0ABudi%20Santoso,2024002,X-A,Jl.%20Contoh%202,081234567892,081234567893"
                    download="template_siswa.csv" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="fas fa-download me-1"></i> Download Template
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>