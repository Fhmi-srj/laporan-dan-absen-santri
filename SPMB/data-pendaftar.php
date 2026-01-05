<?php
require_once 'api/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = getConnection();

// Get settings
$settings = [];
$result = $conn->query("SELECT kunci, nilai FROM pengaturan");
while ($row = $result->fetch_assoc()) {
    $settings[$row['kunci']] = $row['nilai'];
}

$tahunAjaran = $settings['tahun_ajaran'] ?? '2026/2027';

// Get all pendaftar
$pendaftarList = [];
$result = $conn->query("SELECT id, nama, lembaga, jenis_kelamin, asal_sekolah, status, created_at FROM pendaftaran ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $pendaftarList[] = $row;
}

$totalPendaftar = count($pendaftarList);
$totalSMP = count(array_filter($pendaftarList, fn($p) => $p['lembaga'] === 'SMP NU BP'));
$totalMA = count(array_filter($pendaftarList, fn($p) => $p['lembaga'] === 'MA ALHIKAM'));

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftar - SPMB <?= htmlspecialchars($tahunAjaran) ?></title>
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

        html,
        body {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            display: none;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: linear-gradient(135deg, #E67E22 0%, #F39C12 100%);
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-verified {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .table-row:hover {
            background: #f9fafb;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Topbar -->
    <header class="topbar">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="font-bold text-lg">Data Pendaftar</h1>
                <p class="text-sm text-white/70 hidden sm:block">SPMB <?= htmlspecialchars($tahunAjaran) ?></p>
            </div>
            <a href="index.php"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition flex items-center gap-2">
                <i class="fas fa-home"></i><span class="hidden sm:inline">Home</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl mx-auto px-4 py-6 w-full">
        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $totalPendaftar ?></p>
                        <p class="text-xs text-gray-500">Total Pendaftar</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-school text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $totalSMP ?></p>
                        <p class="text-xs text-gray-500">SMP NU BP</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= $totalMA ?></p>
                        <p class="text-xs text-gray-500">MA Alhikam</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="Cari nama pendaftar..."
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-primary focus:outline-none text-sm">
                </div>
                <select id="filterLembaga"
                    class="px-4 py-2 border border-gray-200 rounded-lg focus:border-primary focus:outline-none text-sm">
                    <option value="">Semua Lembaga</option>
                    <option value="SMP NU BP">SMP NU BP</option>
                    <option value="MA ALHIKAM">MA Alhikam</option>
                </select>
                <select id="filterStatus"
                    class="px-4 py-2 border border-gray-200 rounded-lg focus:border-primary focus:outline-none text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="verified">Terverifikasi</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lembaga</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">L/P</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Asal Sekolah
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (empty($pendaftarList)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                    <p>Belum ada data pendaftar</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendaftarList as $i => $p): ?>
                                <tr class="table-row border-b border-gray-50 data-row"
                                    data-nama="<?= strtolower(htmlspecialchars($p['nama'])) ?>"
                                    data-lembaga="<?= htmlspecialchars($p['lembaga']) ?>"
                                    data-status="<?= htmlspecialchars($p['status']) ?>">
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= $i + 1 ?></td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($p['nama']) ?></p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-1 rounded-full <?= $p['lembaga'] === 'SMP NU BP' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                                            <?= htmlspecialchars($p['lembaga']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= $p['jenis_kelamin'] ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?= htmlspecialchars($p['asal_sekolah'] ?? '-') ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php
                                        $statusClass = [
                                            'pending' => 'status-pending',
                                            'verified' => 'status-verified',
                                            'rejected' => 'status-rejected'
                                        ][$p['status']] ?? 'status-pending';
                                        $statusText = [
                                            'pending' => 'Menunggu',
                                            'verified' => 'Terverifikasi',
                                            'rejected' => 'Ditolak'
                                        ][$p['status']] ?? 'Menunggu';
                                        ?>
                                        <span class="text-xs px-2 py-1 rounded-full font-medium <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        <?= date('d M Y', strtotime($p['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            <i class="fas fa-lock mr-1"></i> Data ini bersifat read-only. Untuk mengedit, silakan login ke dashboard.
        </p>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-4 mt-auto">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">SPMB Terpadu</h3>
                <p class="text-sm text-gray-500">Yayasan Almukarromah Pajomblangan</p>
            </div>
        </div>
    </footer>

    <script>
        const searchInput = document.getElementById('searchInput');
        const filterLembaga = document.getElementById('filterLembaga');
        const filterStatus = document.getElementById('filterStatus');
        const rows = document.querySelectorAll('.data-row');

        function filterTable() {
            const search = searchInput.value.toLowerCase();
            const lembaga = filterLembaga.value;
            const status = filterStatus.value;

            rows.forEach(row => {
                const nama = row.dataset.nama;
                const rowLembaga = row.dataset.lembaga;
                const rowStatus = row.dataset.status;

                const matchSearch = nama.includes(search);
                const matchLembaga = !lembaga || rowLembaga === lembaga;
                const matchStatus = !status || rowStatus === status;

                row.style.display = matchSearch && matchLembaga && matchStatus ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTable);
        filterLembaga.addEventListener('change', filterTable);
        filterStatus.addEventListener('change', filterTable);
    </script>
</body>

</html>