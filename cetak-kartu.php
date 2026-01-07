<?php
/**
 * Bulk Print Student Cards
 * ATM Card Style - Multiple Cards
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$pdo = getDB();
$ids = $_GET['ids'] ?? '';

if (empty($ids)) {
    echo "Tidak ada siswa yang dipilih";
    exit;
}

$idArray = explode(',', $ids);
$placeholders = str_repeat('?,', count($idArray) - 1) . '?';

$stmt = $pdo->prepare("SELECT * FROM data_induk WHERE id IN ($placeholders) ORDER BY nama_lengkap");
$stmt->execute($idArray);
$siswaList = $stmt->fetchAll();

if (empty($siswaList)) {
    echo "Data siswa tidak ditemukan";
    exit;
}

// Get settings for school name
$settingStmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('school_name', 'app_name')");
$settings = [];
while ($row = $settingStmt->fetch()) {
    $settings[$row['key']] = $row['value'];
}
$schoolName = $settings['school_name'] ?? 'Pondok Pesantren Mambaul Huda';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Santri (
        <?= count($siswaList) ?> Kartu)
    </title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            padding: 20px;
        }

        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .print-header h4 {
            color: #1e3a5f;
            margin-bottom: 10px;
        }

        .btn-print {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-print:hover {
            background: #2563eb;
        }

        .btn-close-page {
            background: #64748b;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
            justify-items: center;
        }

        .id-card {
            width: 340px;
            height: 215px;
            background: linear-gradient(135deg, #1e3a5f 0%, #3b82f6 50%, #60a5fa 100%);
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(30, 58, 95, 0.3);
            color: white;
            page-break-inside: avoid;
        }

        .id-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-logo img {
            width: 30px;
            height: 30px;
            object-fit: contain;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
            background: rgba(255, 255, 255, 0.2);
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
        }

        .chip::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 25px;
            height: 18px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 2px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 10mm;
            }

            .print-header {
                display: none;
            }

            .cards-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10mm;
            }

            .id-card {
                box-shadow: none;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>

<body>
    <div class="print-header">
        <h4><i class="fas fa-id-card"></i> Cetak
            <?= count($siswaList) ?> Kartu Santri
        </h4>
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak Sekarang
        </button>
        <button class="btn-close-page" onclick="window.close()">
            ✕ Tutup
        </button>
    </div>

    <div class="cards-container">
        <?php foreach ($siswaList as $siswa):
            $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($siswa['nisn'] ?? $siswa['id']);
        ?>
            <div class="id-card">
                <div class="card-header">
                    <div class="school-info">
                        <div class="school-name">
                            <?= e($schoolName) ?>
                        </div>
                        <div class="card-title">Kartu Santri</div>
                    </div>
                    <div class="card-logo">
                        <img src="logo-pondok.png" alt="Logo">
                    </div>
                </div>

                <div class="card-body">
                    <div class="qr-section">
                        <img src="<?= $qrApiUrl ?>" alt="QR Code">
                    </div>
                    <div class="info-section">
                        <div class="student-name">
                            <?= e($siswa['nama_lengkap']) ?>
                        </div>
                        <div class="student-nis">
                            <?= e($siswa['nisn'] ?? '-') ?>
                        </div>
                        <div class="student-class">
                            <span>Kelas
                                <?= e($siswa['kelas']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="chip"></div>
                    <div>Scan QR untuk absensi</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>