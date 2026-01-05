<?php
/**
 * QR Code Page - Print/Download QR Card for Siswa
 * Design: ATM Card Style
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$pdo = getDB();
$siswaId = $_GET['id'] ?? null;

if (!$siswaId) {
    header('Location: siswa.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->execute([$siswaId]);
$siswa = $stmt->fetch();

if (!$siswa) {
    header('Location: siswa.php');
    exit;
}

$pageTitle = 'Kartu Santri - ' . $siswa['nama_lengkap'];

// QR Code data is the nomor_induk
$qrData = $siswa['nomor_induk'];
$qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);

// Get settings for school name
$settingStmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('school_name', 'app_name')");
$settings = [];
while ($row = $settingStmt->fetch()) {
    $settings[$row['key']] = $row['value'];
}
$schoolName = $settings['school_name'] ?? 'Pondok Pesantren Mambaul Huda';
$appName = $settings['app_name'] ?? APP_NAME;

$extraStyles = <<<'CSS'
<style>
    .card-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 150px);
    }

    .id-card {
        width: 340px;
        height: 215px;
        background: linear-gradient(135deg, #1e3a5f 0%, #3b82f6 50%, #60a5fa 100%);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(30, 58, 95, 0.4), 0 0 0 1px rgba(255,255,255,0.1);
        color: white;
        font-family: 'Poppins', sans-serif;
    }

    /* Decorative elements */
    .id-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .id-card::after {
        content: '';
        position: absolute;
        bottom: -80px;
        left: -80px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    .card-header {
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        z-index: 1;
    }

    .school-info {
        flex: 1;
    }

    .school-name {
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
        line-height: 1.3;
    }

    .card-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
        color: #fbbf24;
    }

    .card-logo {
        width: 35px;
        height: 35px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .card-body {
        display: flex;
        padding: 0 16px;
        gap: 14px;
        position: relative;
        z-index: 1;
    }

    .qr-section {
        background: white;
        padding: 6px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .qr-section img {
        display: block;
        width: 90px;
        height: 90px;
    }

    .info-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .student-name {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 4px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        line-height: 1.2;
    }

    .student-nis {
        font-size: 16px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
        margin-bottom: 6px;
        color: #fbbf24;
    }

    .student-class {
        font-size: 10px;
        opacity: 0.8;
    }

    .student-class span {
        background: rgba(255,255,255,0.2);
        padding: 3px 10px;
        border-radius: 12px;
        font-weight: 500;
    }

    .card-footer {
        position: absolute;
        bottom: 10px;
        left: 16px;
        right: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 7px;
        opacity: 0.7;
        z-index: 1;
    }

    .chip {
        width: 35px;
        height: 26px;
        background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
        border-radius: 4px;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .chip::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 25px;
        height: 18px;
        border: 1px solid rgba(0,0,0,0.2);
        border-radius: 2px;
    }

    .chip::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 4px;
        right: 4px;
        height: 1px;
        background: rgba(0,0,0,0.15);
    }

    /* Print styles */
    @media print {
        .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .sidebar, .navbar { display: none !important; }
        .card-container { min-height: auto; padding: 0; }
        .id-card { 
            box-shadow: none; 
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body { background: white !important; }
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 24px;
    }
</style>
CSS;
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<div class="main-content">
    <div class="mb-3 no-print">
        <a href="siswa.php" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="card-container">
        <div>
            <!-- ATM-style ID Card -->
            <div class="id-card">
                <div class="card-header">
                    <div class="school-info">
                        <div class="school-name"><?= e($schoolName) ?></div>
                        <div class="card-title">Kartu Santri</div>
                    </div>
                    <div class="card-logo">
                        <img src="logo-pondok.png" alt="Logo" style="width: 30px; height: 30px; object-fit: contain;">
                    </div>
                </div>

                <div class="card-body">
                    <div class="qr-section">
                        <img src="<?= $qrApiUrl ?>" alt="QR Code">
                    </div>
                    <div class="info-section">
                        <div class="student-name"><?= e($siswa['nama_lengkap']) ?></div>
                        <div class="student-nis"><?= e($siswa['nomor_induk']) ?></div>
                        <div class="student-class">
                            <span>Kelas <?= e($siswa['kelas']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="chip"></div>
                    <div>Scan QR untuk absensi</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-1"></i> Cetak Kartu
                </button>
                <button onclick="downloadCard()" class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Download Kartu
                </button>
            </div>
        </div>
    </div>

    <div class="text-center text-muted mt-4 small no-print">
        <i class="fas fa-info-circle me-1"></i>
        Ukuran kartu sesuai standar kartu ATM (85.6 × 54 mm)
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
    function downloadCard() {
        const card = document.querySelector('.id-card');
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;

        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyiapkan...';
        btn.disabled = true;

        html2canvas(card, {
            scale: 3, // Higher quality
            useCORS: true,
            allowTaint: true,
            backgroundColor: null

        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'Kartu_<?= e($siswa['nomor_induk']) ?>_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $siswa['nama_lengkap']) ?>.png';
            link.href = canvas.toDataURL('image/png');
            link.click();

            btn.innerHTML = originalText;
            btn.disabled = false;
        }).catch(err => {
            alert('Gagal menyimpan kartu. Silakan gunakan tombol Cetak.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>

<?php include __DIR__ . '/include/footer.php'; ?>