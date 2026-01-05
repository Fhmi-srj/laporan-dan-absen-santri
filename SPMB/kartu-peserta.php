<?php
require_once 'api/config.php';

// Get registration ID from query parameter
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Check if user is logged in and matches the ID, or if admin
$isAdmin = isset($_SESSION['admin_id']);
$isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id;

if (!$isAdmin && !$isOwner) {
    header('Location: index.php');
    exit;
}

// Get registration data
$stmt = $conn->prepare("SELECT * FROM pendaftaran WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header('Location: index.php');
    exit;
}

// Get settings
$tahunAjaran = getSetting($conn, 'tahun_ajaran') ?? '2026/2027';

$conn->close();

// Document status
$documents = [
    ['field' => 'file_kk', 'name' => 'Kartu Keluarga (KK)', 'required' => true],
    ['field' => 'file_ktp_ortu', 'name' => 'KTP Orang Tua', 'required' => true],
    ['field' => 'file_akta', 'name' => 'Akta Kelahiran', 'required' => true],
    ['field' => 'file_ijazah', 'name' => 'Ijazah', 'required' => false],
    ['field' => 'file_sertifikat', 'name' => 'Sertifikat Prestasi', 'required' => false],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta - <?= htmlspecialchars($data['nama']) ?></title>
    <link href="images/logo-pondok.png" rel="icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E67E22',
                        'primary-dark': '#D35400',
                        'primary-light': '#F39C12',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                margin: 0;
                padding: 0;
            }

            .kartu-peserta {
                box-shadow: none !important;
                border: 2px solid #E67E22 !important;
            }
        }

        .kartu-peserta {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .kartu-header {
            background: linear-gradient(135deg, #E67E22 0%, #F39C12 100%);
            padding: 1.5rem;
            text-align: center;
            color: white;
        }

        .kartu-body {
            padding: 1.5rem;
        }

        .reg-number {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .doc-status {
            font-size: 0.75rem;
        }

        .doc-status .completed {
            color: #10B981;
        }

        .doc-status .pending {
            color: #EF4444;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen py-8">
    <!-- Print Button -->
    <div class="no-print fixed top-4 right-4 flex gap-2">
        <button onclick="window.print()"
            class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 transition">
            <i class="fas fa-print"></i> Cetak
        </button>
        <button onclick="window.close()"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 transition">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <!-- Card Container -->
    <div class="print-container flex justify-center items-start px-4">
        <div class="kartu-peserta">
            <!-- Header -->
            <div class="kartu-header">
                <div class="flex justify-center items-center gap-3 mb-3">
                    <img src="images/logo-pondok.png" alt="Logo" class="w-12 h-12 rounded-lg bg-white/20 p-1">
                </div>
                <h1 class="text-lg font-bold">KARTU PESERTA PPDB</h1>
                <p class="text-sm text-white/80">Tahun Ajaran <?= htmlspecialchars($tahunAjaran) ?></p>
            </div>

            <!-- Body -->
            <div class="kartu-body">
                <!-- Registration Number -->
                <div class="text-center mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">NOMOR REGISTRASI</p>
                    <p class="reg-number text-primary"><?= htmlspecialchars($data['no_registrasi'] ?? '-') ?></p>
                </div>

                <!-- Personal Info -->
                <div class="space-y-3 mb-4 pb-4 border-b border-gray-200">
                    <div>
                        <p class="text-xs text-gray-500">Nama Lengkap</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($data['nama']) ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Lembaga Tujuan</p>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($data['lembaga']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium text-gray-800">
                                <?= $data['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Asal Sekolah</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($data['asal_sekolah'] ?: '-') ?></p>
                    </div>
                </div>

                <!-- Document Status -->
                <div class="doc-status">
                    <p class="text-xs text-gray-500 mb-2 font-medium">STATUS KELENGKAPAN DOKUMEN</p>
                    <div class="grid grid-cols-2 gap-2">
                        <?php foreach ($documents as $doc):
                            $hasFile = !empty($data[$doc['field']]);
                            $iconClass = $hasFile ? 'fa-check-circle completed' : 'fa-times-circle pending';
                            ?>
                            <div class="flex items-center gap-1.5">
                                <i class="fas <?= $iconClass ?>"></i>
                                <span class="<?= $hasFile ? 'text-gray-700' : 'text-gray-400' ?>"><?= $doc['name'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <?php
                    $statusConfig = [
                        'pending' => ['Menunggu Verifikasi', 'bg-yellow-100 text-yellow-800', 'fa-clock'],
                        'verified' => ['Terverifikasi', 'bg-green-100 text-green-800', 'fa-check-circle'],
                        'rejected' => ['Ditolak', 'bg-red-100 text-red-800', 'fa-times-circle']
                    ];
                    $status = $statusConfig[$data['status']] ?? $statusConfig['pending'];
                    ?>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Status Pendaftaran</span>
                        <span
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium <?= $status[1] ?>">
                            <i class="fas <?= $status[2] ?>"></i> <?= $status[0] ?>
                        </span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-4 pt-4 border-t border-dashed border-gray-300 text-center">
                    <p class="text-xs text-gray-400">PP Mambaul Huda Pajomblangan</p>
                    <p class="text-xs text-gray-400">Kedungwuni, Pekalongan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto print on load (optional) -->
    <script>
        // Uncomment to auto-print when page loads
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>