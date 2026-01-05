<?php
require_once 'api/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$conn = getConnection();

// Get settings
$settings = [];
$result = $conn->query("SELECT kunci, nilai FROM pengaturan");
while ($row = $result->fetch_assoc()) {
    $settings[$row['kunci']] = $row['nilai'];
}

// AJAX handler for live search
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    $search = isset($_GET['q']) ? trim($_GET['q']) : '';

    header('Content-Type: application/json');

    if (strlen($search) < 2) {
        echo json_encode(['success' => false, 'message' => 'Masukkan minimal 2 karakter']);
        exit;
    }

    $stmt = $conn->prepare("SELECT nama, lembaga, status, created_at FROM pendaftaran WHERE nama LIKE ? ORDER BY created_at DESC LIMIT 20");
    $searchParam = "%{$search}%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data, 'count' => count($data)]);
    exit;
}

$tahunAjaran = $settings['tahun_ajaran'] ?? '2026/2027';
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran - SPMB <?= htmlspecialchars($tahunAjaran) ?></title>
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

        /* Hide scrollbar */
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

        .search-input {
            transition: all 0.3s ease;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.2);
        }

        .result-card {
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -8px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .loading-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Sticky Topbar -->
    <header class="topbar">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div>
                    <h1 class="font-bold text-lg">Cek Status Pendaftaran</h1>
                    <p class="text-sm text-white/70 hidden sm:block">SPMB <?= htmlspecialchars($tahunAjaran) ?></p>
                </div>
            </div>
            <a href="index.php"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition flex items-center gap-2">
                <i class="fas fa-home"></i><span class="hidden sm:inline">Home</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-4xl mx-auto px-4 py-8 w-full">
        <!-- Search Section -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-2xl text-primary"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Cari Status Pendaftaran</h2>
                <p class="text-gray-500 text-sm">Masukkan nama lengkap untuk melihat status pendaftaran</p>
            </div>

            <div class="relative">
                <input type="text" id="searchInput" placeholder="Ketik nama pendaftar..."
                    class="search-input w-full px-5 py-4 pr-12 border-2 border-gray-200 rounded-xl text-base focus:border-primary focus:outline-none"
                    autocomplete="off">
                <div id="loadingIcon" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                    <i class="fas fa-spinner loading-spinner text-primary"></i>
                </div>
                <div id="searchIcon" class="absolute right-4 top-1/2 -translate-y-1/2">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>

            <p class="text-xs text-gray-400 mt-2 text-center">Minimal 2 karakter untuk mencari</p>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Hasil Pencarian</h3>
                <span id="resultCount" class="text-sm text-gray-500"></span>
            </div>
            <div id="resultsContainer" class="space-y-3">
                <!-- Results will be inserted here -->
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-times text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ditemukan</h3>
            <p class="text-gray-500 text-sm">Tidak ada pendaftar dengan nama tersebut</p>
        </div>

        <!-- Initial State -->
        <div id="initialState" class="text-center py-12">
            <div class="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-clipboard-list text-3xl text-primary"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Cek Status Pendaftar</h3>
            <p class="text-gray-500 text-sm">Ketik nama di kolom pencarian untuk melihat status</p>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-4 mt-auto">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <p class="text-sm text-gray-500">&copy; <?= date('Y') ?> SPMB Terpadu - Yayasan Almukarromah Pajomblangan
            </p>
        </div>
    </footer>

    <script>
        const searchInput = document.getElementById('searchInput');
        const resultsSection = document.getElementById('resultsSection');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultCount = document.getElementById('resultCount');
        const emptyState = document.getElementById('emptyState');
        const initialState = document.getElementById('initialState');
        const loadingIcon = document.getElementById('loadingIcon');
        const searchIcon = document.getElementById('searchIcon');

        let searchTimeout;

        searchInput.addEventListener('input', function () {
            const query = this.value.trim();

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                hideAll();
                initialState.classList.remove('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        async function performSearch(query) {
            // Show loading
            loadingIcon.classList.remove('hidden');
            searchIcon.classList.add('hidden');

            try {
                const response = await fetch(`cek-status.php?ajax=search&q=${encodeURIComponent(query)}`);
                const data = await response.json();

                hideAll();

                if (data.success && data.data.length > 0) {
                    resultsSection.classList.remove('hidden');
                    resultCount.textContent = `${data.count} hasil`;
                    renderResults(data.data);
                } else {
                    emptyState.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Search error:', error);
                emptyState.classList.remove('hidden');
            } finally {
                loadingIcon.classList.add('hidden');
                searchIcon.classList.remove('hidden');
            }
        }

        function hideAll() {
            resultsSection.classList.add('hidden');
            emptyState.classList.add('hidden');
            initialState.classList.add('hidden');
        }

        function renderResults(results) {
            resultsContainer.innerHTML = results.map((item, index) => {
                const statusClass = {
                    'pending': 'status-pending',
                    'verified': 'status-verified',
                    'rejected': 'status-rejected'
                }[item.status] || 'status-pending';

                const statusText = {
                    'pending': 'Menunggu Verifikasi',
                    'verified': 'Terverifikasi',
                    'rejected': 'Ditolak'
                }[item.status] || 'Menunggu';

                const statusIcon = {
                    'pending': 'fa-clock',
                    'verified': 'fa-check-circle',
                    'rejected': 'fa-times-circle'
                }[item.status] || 'fa-clock';

                const date = new Date(item.created_at);
                const formattedDate = date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });

                return `
                    <div class="result-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm" style="animation-delay: ${index * 0.05}s">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">${escapeHtml(item.nama)}</h4>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <span>${escapeHtml(item.lembaga)}</span>
                                        <span>•</span>
                                        <span>${formattedDate}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="${statusClass} px-3 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5">
                                <i class="fas ${statusIcon}"></i>
                                <span>${statusText}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>