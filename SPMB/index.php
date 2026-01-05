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

// Get biaya
$biayaList = [];
$result = $conn->query("SELECT * FROM biaya ORDER BY kategori DESC, urutan ASC");
while ($row = $result->fetch_assoc()) {
    $biayaList[] = $row;
}

// Get beasiswa grouped by jenis
$beasiswaList = [];
$result = $conn->query("SELECT * FROM beasiswa ORDER BY urutan ASC");
while ($row = $result->fetch_assoc()) {
    $beasiswaList[$row['jenis']][] = $row;
}

// Get kontak
$kontakList = [];
$result = $conn->query("SELECT * FROM kontak ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $kontakList[] = $row;
}

// Calculate totals
$totals = ['pondok' => 0, 'smp' => 0, 'ma' => 0];
foreach ($biayaList as $b) {
    $totals['pondok'] += $b['biaya_pondok'];
    $totals['smp'] += $b['biaya_smp'];
    $totals['ma'] += $b['biaya_ma'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="Informasi Pendaftaran MA Alhikam, SMP NU, dan Ponpes Mambaul Huda Pajomblangan."
        name="description" />
    <meta content="https://spmb.mambaulhuda.ponpes.id/images/brosur.jpeg" property="og:image" />
    <link href="images/logo-pondok.png" rel="icon" />
    <title>SPMB Terpadu 2026/2027</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
            font-family: "Inter", sans-serif;
        }

        /* Hide scrollbar but keep scroll functionality */
        html {
            scroll-behavior: smooth;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE/Edge */
        }

        html::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        body {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

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

        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .card-hover:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 20px 40px -12px rgba(230, 126, 34, 0.25);
        }

        .btn-animated {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-animated::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-animated:hover::before {
            left: 100%;
        }

        .btn-animated:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px -8px rgba(230, 126, 34, 0.5);
        }

        .btn-animated:active {
            transform: translateY(-1px);
        }

        .logo-item {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logo-item:hover {
            transform: scale(1.1) rotate(2deg);
            filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.15));
        }

        .social-icon {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .social-icon:hover {
            transform: translateY(-4px) scale(1.1);
            box-shadow: 0 8px 16px -4px rgba(230, 126, 34, 0.4);
        }

        /* Page load animation */
        .page-loaded {
            animation: pageLoad 0.6s ease-out;
        }

        @keyframes pageLoad {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Stats counter animation */
        .stat-number {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .stat-item:hover .stat-number {
            transform: scale(1.1);
            color: #D35400;
        }

        .stat-item {
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-3px);
        }

        /* Pulse animation for CTA */
        .pulse-glow {
            animation: pulseGlow 2s infinite;
        }

        @keyframes pulseGlow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(230, 126, 34, 0.4);
            }

            50% {
                box-shadow: 0 0 20px 10px rgba(230, 126, 34, 0);
            }
        }

        /* Float animation */
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-delay-1 {
            animation-delay: 0.1s;
        }

        .animate-delay-2 {
            animation-delay: 0.2s;
        }

        .animate-delay-3 {
            animation-delay: 0.3s;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 100;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            max-width: 650px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            animation: modalIn 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #E67E22 0%, #F39C12 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            flex-shrink: 0;
        }

        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .info-card {
            background: #f8fafa;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
        }

        .info-card-title {
            font-weight: 600;
            color: #E67E22;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.8125rem;
            color: #374151;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .info-list li:last-child {
            border-bottom: none;
        }

        .info-list li i {
            color: #E67E22;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .price-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
        }

        .price-table th {
            background: #E67E22;
            color: white;
            padding: 0.625rem;
            text-align: left;
            font-weight: 600;
        }

        .price-table td {
            padding: 0.5rem 0.625rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .price-table tr:nth-child(even) {
            background: #f9fafb;
        }

        .price-table .section-header {
            background: #e0f2f1;
            font-weight: 600;
            color: #E67E22;
        }

        .price-table .total-row {
            background: #E67E22;
            color: white;
            font-weight: 700;
        }

        .note-box {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.75rem;
            color: #92400e;
        }

        .note-box i {
            color: #f59e0b;
        }

        .beasiswa-card {
            background: linear-gradient(135deg, #f0fdfa 0%, #e0f7f4 100%);
            border: 1px solid #99f6e4;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
        }

        .beasiswa-title {
            font-weight: 700;
            color: #E67E22;
            font-size: 0.9375rem;
            margin-bottom: 0.25rem;
        }

        .beasiswa-subtitle {
            font-size: 0.75rem;
            color: #0f766e;
            font-weight: 500;
        }

        .download-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .download-item:hover {
            border-color: #E67E22;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.1);
        }

        .download-item:last-child {
            margin-bottom: 0;
        }

        .download-icon {
            width: 48px;
            height: 48px;
            background: rgba(230, 126, 34, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .download-icon i {
            font-size: 1.25rem;
            color: #E67E22;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Sticky Topbar -->
    <div class="topbar">
        <div class="max-w-6xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="text-base sm:text-lg font-bold">SPMB Terpadu</h1>
                        <p class="text-xs opacity-80 hidden sm:block">Yayasan Almukarromah Pajomblangan</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($isLoggedIn): ?>
                        <a href="user/dashboard.php"
                            class="btn-animated bg-white text-primary text-xs sm:text-sm font-semibold py-2 px-3 sm:px-4 rounded-lg shadow flex items-center gap-2">
                            <i class="fas fa-user"></i><span>Profil</span>
                        </a>
                    <?php else: ?>
                        <a href="user/"
                            class="btn-animated bg-white text-primary text-xs sm:text-sm font-semibold py-2 px-3 sm:px-4 rounded-lg shadow flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i><span>Masuk</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-6">
        <!-- Hero Section - Full Width with Background -->
        <section class="relative mb-8" data-aos="fade-up">
            <div
                class="bg-gradient-to-br from-orange-50 via-white to-yellow-50 rounded-3xl p-6 sm:p-10 border border-orange-100 shadow-sm">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <!-- Left: Text Content -->
                    <div class="text-center lg:text-left" data-aos="fade-right" data-aos-delay="100">
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800 mb-4 leading-tight">
                            Sekolah Sambil <span class="text-primary">Mondok</span>? <br>
                            <span class="text-primary">Bisa!</span>
                        </h2>
                        <p class="text-gray-600 text-sm sm:text-base mb-6 leading-relaxed">
                            Pendidikan formal dari MI hingga MA yang terpadu dengan asrama pesantren dalam satu komplek
                            lingkungan bernuansa Islami.
                        </p>
                        <?php if (($settings['status_pendaftaran'] ?? '0') === '1'): ?>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                                <a href="pendaftaran.php"
                                    class="btn-animated inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-xl shadow-lg text-sm sm:text-base">
                                    <i class="fas fa-file-alt"></i> Daftar Sekarang
                                </a>
                                <a href="cek-status.php"
                                    class="btn-animated inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-6 rounded-xl border border-gray-200 text-sm sm:text-base">
                                    <i class="fas fa-search"></i> Cek Status
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                                <div
                                    class="inline-flex items-center gap-2 bg-red-100 text-red-700 font-semibold py-3 px-6 rounded-xl">
                                    <i class="fas fa-times-circle"></i> Pendaftaran Ditutup
                                </div>
                                <a href="cek-status.php"
                                    class="btn-animated inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-6 rounded-xl border border-gray-200 text-sm sm:text-base">
                                    <i class="fas fa-search"></i> Cek Status
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Logo Grid -->
                    <div class="flex justify-center lg:justify-end" data-aos="fade-left" data-aos-delay="200">
                        <div class="grid grid-cols-3 gap-4 sm:gap-6">
                            <div
                                class="logo-item flex flex-col items-center p-4 bg-white rounded-2xl shadow-md hover:shadow-lg transition-all">
                                <img alt="Logo MA Alhikam"
                                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover mb-2"
                                    src="images/logo-ma.png" />
                                <p class="text-xs font-semibold text-gray-700">MA Alhikam</p>
                            </div>
                            <div
                                class="logo-item flex flex-col items-center p-4 bg-white rounded-2xl shadow-md hover:shadow-lg transition-all ring-2 ring-primary/20">
                                <img alt="Logo Ponpes" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover mb-2"
                                    src="images/logo-pondok.png" />
                                <p class="text-xs font-semibold text-gray-700">Ponpes</p>
                            </div>
                            <div
                                class="logo-item flex flex-col items-center p-4 bg-white rounded-2xl shadow-md hover:shadow-lg transition-all">
                                <img alt="Logo SMP NU" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover mb-2"
                                    src="images/logo-smp.png" />
                                <p class="text-xs font-semibold text-gray-700">SMP NU</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Counter Section -->
        <section class="mb-8 stats-section" data-aos="fade-up" data-aos-delay="300">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div
                    class="stat-item bg-white rounded-2xl p-5 text-center shadow-sm border border-gray-100 hover:shadow-md transition-all">
                    <div class="text-2xl sm:text-3xl font-bold text-primary mb-1 stat-number"><span class="counter"
                            data-target="3">0</span></div>
                    <div class="text-xs text-gray-500">Jenjang Pendidikan</div>
                </div>
                <div
                    class="stat-item bg-white rounded-2xl p-5 text-center shadow-sm border border-gray-100 hover:shadow-md transition-all">
                    <div class="text-2xl sm:text-3xl font-bold text-primary mb-1 stat-number"><span class="counter"
                            data-target="30">0</span></div>
                    <div class="text-xs text-gray-500">Juz Target Hafalan</div>
                </div>
                <div
                    class="stat-item bg-white rounded-2xl p-5 text-center shadow-sm border border-gray-100 hover:shadow-md transition-all">
                    <div class="text-2xl sm:text-3xl font-bold text-primary mb-1 stat-number"><span class="counter"
                            data-target="6">0</span></div>
                    <div class="text-xs text-gray-500">Tahun Program</div>
                </div>
                <div
                    class="stat-item bg-white rounded-2xl p-5 text-center shadow-sm border border-gray-100 hover:shadow-md transition-all">
                    <div class="text-2xl sm:text-3xl font-bold text-primary mb-1 stat-number"><span class="counter"
                            data-target="100">0</span>%</div>
                    <div class="text-xs text-gray-500">Kurikulum Pesantren</div>
                </div>
            </div>
        </section>

        <!-- Two Column Layout for Desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Left Column: Download Berkas -->
            <section data-aos="fade-right" data-aos-delay="100">
                <h3 class="text-gray-800 font-semibold text-base mb-4 flex items-center gap-2">
                    <span class="w-8 h-1 bg-primary rounded-full"></span>Informasi Pendaftaran
                </h3>
                <div class="space-y-3">
                    <button onclick="openModal('biayaModal')" class="download-item w-full text-left">
                        <div class="download-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-primary font-semibold text-sm">Biaya Pendaftaran</h5>
                            <p class="text-gray-500 text-xs">Informasi biaya pendaftaran dengan detail dan rinci</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    </button>

                    <button onclick="openModal('brosurModal')" class="download-item w-full text-left">
                        <div class="download-icon"><i class="fas fa-file-invoice"></i></div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-primary font-semibold text-sm">Brosur Utama</h5>
                            <p class="text-gray-500 text-xs">Informasi lengkap pendaftaran dalam satu brosur</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    </button>

                    <button onclick="openModal('syaratModal')" class="download-item w-full text-left">
                        <div class="download-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-primary font-semibold text-sm">Syarat & Berkas</h5>
                            <p class="text-gray-500 text-xs">Rincian syarat dan berkas wajib untuk pendaftaran</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    </button>

                    <button onclick="openModal('beasiswaModal')" class="download-item w-full text-left">
                        <div class="download-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-primary font-semibold text-sm">Beasiswa</h5>
                            <p class="text-gray-500 text-xs">Informasi beasiswa pendidikan secara lengkap</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    </button>
                </div>
            </section>

            <!-- Right Column: Program Unggulan -->
            <section class="animate-fade-in animate-delay-2">
                <h3 class="text-gray-800 font-semibold text-base mb-4 flex items-center gap-2">
                    <span class="w-8 h-1 bg-primary rounded-full"></span>Program Unggulan
                </h3>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-xl">
                            <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-quran text-white"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800 text-sm">Program Tahfidz</h5>
                                <p class="text-xs text-gray-500">Target 30 Juz dalam 6 tahun dengan metode modern</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-xl">
                            <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-book-open text-white"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800 text-sm">Kurikulum Terpadu</h5>
                                <p class="text-xs text-gray-500">Perpaduan kurikulum nasional dan pesantren salaf</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-xl">
                            <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-home text-white"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800 text-sm">Asrama Terpadu</h5>
                                <p class="text-xs text-gray-500">Sekolah dan asrama dalam satu kompleks pesantren</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-xl">
                            <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-award text-white"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800 text-sm">Beasiswa Prestasi</h5>
                                <p class="text-xs text-gray-500">Tersedia beasiswa untuk siswa berprestasi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Lokasi Section -->
        <section class="mb-6 animate-fade-in animate-delay-2">
            <h3 class="text-gray-800 font-semibold text-sm mb-3 flex items-center gap-2">
                <span class="w-6 h-0.5 bg-primary rounded-full"></span>Lokasi Kami
            </h3>
            <p class="text-gray-500 text-xs mb-3">PP Mambaul Huda Pajomblangan, Kedungwuni, Pekalongan</p>
            <div class="w-full aspect-video rounded-xl overflow-hidden shadow-lg">
                <iframe allowfullscreen="" class="w-full h-full" loading="lazy"
                    src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d506919.5207899324!2d109.666159!3d-6.972853!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7022290d0c3a51%3A0x3fe69d4f394b9c58!2sPP%20Mamba'ul%20Huda%20Pajomblangan!5e0!3m2!1sen!2sid!4v1745034814265!5m2!1sen!2sid"
                    style="border:0;"></iframe>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200">
        <!-- Main Footer -->
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left: Logo & Info -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">SPMB Terpadu</h3>
                            <p class="text-sm text-gray-500">Yayasan Almukarromah Pajomblangan</p>
                        </div>
                    </div>

                    <!-- Contact Buttons -->
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Hubungi Kami</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($kontakList as $kontak): ?>
                            <a href="<?= htmlspecialchars($kontak['link_wa'] ?: 'https://wa.me/' . preg_replace('/[^0-9]/', '', $kontak['no_whatsapp'])) ?>"
                                target="_blank"
                                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-dark text-white text-xs font-medium py-2 px-3 rounded-lg transition-all">
                                <i class="fab fa-whatsapp"></i>
                                <span class="hidden sm:inline"><?= htmlspecialchars($kontak['lembaga']) ?></span>
                                <span class="font-semibold"><?= htmlspecialchars($kontak['nama']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right: Address & Social -->
                <div class="md:text-right">
                    <h4 class="text-sm font-bold text-gray-700 mb-2">YAYASAN AL MUKARROMAH</h4>
                    <p class="text-sm text-gray-500 mb-4">
                        Jl. Pajomblangan Timur, Ds. Pajomblangan, Kec.<br>
                        Kedungwuni, Kab. Pekalongan, Jawa Tengah
                    </p>

                    <!-- Social Media -->
                    <div class="flex gap-2 md:justify-end">
                        <a href="https://www.facebook.com/share/14Vs1VguYb1/" target="_blank"
                            class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center transition-all">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="https://www.instagram.com/ppmambaulhuda/" target="_blank"
                            class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center transition-all">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                        <a href="https://www.tiktok.com/@ppmambaulhuda" target="_blank"
                            class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center transition-all">
                            <i class="fab fa-tiktok text-sm"></i>
                        </a>
                        <a href="https://youtube.com/@ppmambaulhuda" target="_blank"
                            class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center transition-all">
                            <i class="fab fa-youtube text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="bg-primary text-white py-3">
            <div class="max-w-6xl mx-auto px-4 text-center">
                <p class="text-xs">© Copyright 2025 Yayasan Al Mukarromah</p>
            </div>
        </div>
    </footer>

    <!-- Modal Biaya - Dynamic -->
    <div id="biayaModal" class="modal-overlay" onclick="closeModalOnOverlay(event, 'biayaModal')">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                    <div>
                        <h4 class="font-bold text-lg">Biaya Pendaftaran</h4>
                        <p class="text-xs opacity-80">PPDB
                            <?= htmlspecialchars($settings['tahun_ajaran'] ?? '2026/2027') ?>
                        </p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeModal('biayaModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <table class="price-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pembayaran</th>
                            <th>Pondok</th>
                            <th>SMP</th>
                            <th>MA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currentKategori = '';
                        $no = 1;
                        foreach ($biayaList as $biaya):
                            if ($currentKategori !== $biaya['kategori']):
                                $currentKategori = $biaya['kategori'];
                                ?>
                                <tr class="section-header">
                                    <td colspan="5">
                                        <?= $biaya['kategori'] === 'PENDAFTARAN' ? 'A. PENDAFTARAN' : 'B. DAFTAR ULANG' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($biaya['nama_item']) ?></td>
                                <td><?= $biaya['biaya_pondok'] > 0 ? 'Rp' . number_format($biaya['biaya_pondok'], 0, ',', '.') : '-' ?>
                                </td>
                                <td><?= $biaya['biaya_smp'] > 0 ? 'Rp' . number_format($biaya['biaya_smp'], 0, ',', '.') : '-' ?>
                                </td>
                                <td><?= $biaya['biaya_ma'] > 0 ? 'Rp' . number_format($biaya['biaya_ma'], 0, ',', '.') : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="2">TOTAL</td>
                            <td>Rp<?= number_format($totals['pondok'], 0, ',', '.') ?></td>
                            <td>Rp<?= number_format($totals['smp'], 0, ',', '.') ?></td>
                            <td>Rp<?= number_format($totals['ma'], 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="note-box mt-4">
                    <p class="font-semibold mb-2"><i class="fas fa-info-circle mr-1"></i> Keterangan:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Infaq Bulanan Pondok Rp600.000/Bulan (Makan 3x, Asrama, Madrasah Diniyah, Laundry)</li>
                    </ul>
                    <p class="font-bold mt-3">Biaya Pondok + SMP:
                        Rp<?= number_format($totals['pondok'] + $totals['smp'], 0, ',', '.') ?></p>
                    <p class="font-bold">Biaya Pondok + MA:
                        Rp<?= number_format($totals['pondok'] + $totals['ma'], 0, ',', '.') ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?= htmlspecialchars($settings['link_pdf_biaya'] ?? '#') ?>" target="_blank"
                    class="btn-animated w-full bg-primary hover:bg-primary-dark text-white text-sm font-semibold py-3 px-4 rounded-xl shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-download"></i><span>Download PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Brosur -->
    <div id="brosurModal" class="modal-overlay" onclick="closeModalOnOverlay(event, 'brosurModal')">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-invoice text-xl"></i>
                    <div>
                        <h4 class="font-bold text-lg">Brosur Utama</h4>
                        <p class="text-xs opacity-80">Informasi Lengkap PPDB</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeModal('brosurModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="info-card">
                    <div class="info-card-title"><i class="fas fa-book-open"></i> Tentang Kami</div>
                    <p class="text-xs text-gray-600">Lembaga pendidikan yang memadukan Pondok Pesantren dengan Formal
                        dari SMP sampai SMA dalam satu komplek.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-title"><i class="fas fa-star"></i> Program Unggulan</div>
                    <p class="text-xs text-gray-600">Tahfidz Qur'an & Qiroatul Kutub. Target 6 tahun khatam 30 Juz /
                        menguasai kitab kuning.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-title"><i class="fas fa-calendar-alt"></i> Waktu Pendaftaran</div>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div class="bg-primary/10 rounded-lg p-3 text-center">
                            <p class="text-xs font-semibold text-primary">Gelombang 1</p>
                            <p class="text-xs text-gray-600">
                                <?php
                                $g1Start = $settings['gelombang_1_start'] ?? '';
                                $g1End = $settings['gelombang_1_end'] ?? '';
                                if ($g1Start && $g1End) {
                                    echo date('d M', strtotime($g1Start)) . ' - ' . date('d M Y', strtotime($g1End));
                                } else {
                                    echo 'Segera';
                                }
                                ?>
                            </p>
                        </div>
                        <div class="bg-primary/10 rounded-lg p-3 text-center">
                            <p class="text-xs font-semibold text-primary">Gelombang 2</p>
                            <p class="text-xs text-gray-600">
                                <?php
                                $g2Start = $settings['gelombang_2_start'] ?? '';
                                $g2End = $settings['gelombang_2_end'] ?? '';
                                if ($g2Start && $g2End) {
                                    echo date('d M', strtotime($g2Start)) . ' - ' . date('d M Y', strtotime($g2End));
                                } else {
                                    echo 'Segera';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="note-box mt-3">
                    <p class="font-semibold text-green-700"><i class="fas fa-gift mr-1"></i> GRATIS SERAGAM BATIK UNTUK
                        GELOMBANG 1</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?= htmlspecialchars($settings['link_pdf_brosur'] ?? '#') ?>" target="_blank"
                    class="btn-animated w-full bg-primary hover:bg-primary-dark text-white text-sm font-semibold py-3 px-4 rounded-xl shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-download"></i><span>Download Brosur PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Syarat -->
    <div id="syaratModal" class="modal-overlay" onclick="closeModalOnOverlay(event, 'syaratModal')">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-alt text-xl"></i>
                    <div>
                        <h4 class="font-bold text-lg">Syarat & Berkas</h4>
                        <p class="text-xs opacity-80">Berkas Pendaftaran</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeModal('syaratModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="info-card">
                    <div class="info-card-title"><i class="fas fa-folder-open"></i> Berkas Wajib</div>
                    <ul class="info-list">
                        <li><i class="fas fa-check-circle"></i>FC Akta Kelahiran</li>
                        <li><i class="fas fa-check-circle"></i>FC Kartu Keluarga</li>
                        <li><i class="fas fa-check-circle"></i>FC KTP Orang Tua</li>
                        <li><i class="fas fa-check-circle"></i>FC Ijazah / SKHUN</li>
                        <li><i class="fas fa-check-circle"></i>Foto 3x4 Background Merah (4 Lembar)</li>
                        <li><i class="fas fa-check-circle"></i>Nomor NISN</li>
                    </ul>
                </div>
                <div class="note-box">
                    <p class="font-semibold mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Penting:</p>
                    <p>Berkas masuk ke <strong>STOPMAP</strong>: Hijau (SMP), Merah (MA)</p>
                </div>
                <?php $kontakMA = array_filter($kontakList, fn($k) => $k['lembaga'] === 'MA');
                $kontakMA = reset($kontakMA); ?>
                <?php if ($kontakMA): ?>
                    <div class="info-card mt-3">
                        <div class="info-card-title"><i class="fas fa-phone-alt"></i> Hubungi Kami</div>
                        <p class="text-xs text-gray-600 mb-3">
                            <strong><?= htmlspecialchars($kontakMA['no_whatsapp']) ?></strong>
                            (<?= htmlspecialchars($kontakMA['nama']) ?>)
                        </p>
                        <a href="<?= htmlspecialchars($kontakMA['link_wa'] ?: 'https://wa.me/' . preg_replace('/[^0-9]/', '', $kontakMA['no_whatsapp'])) ?>"
                            target="_blank"
                            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-2 px-4 rounded-lg">
                            <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="<?= htmlspecialchars($settings['link_pdf_syarat'] ?? '#') ?>" target="_blank"
                    class="btn-animated w-full bg-primary hover:bg-primary-dark text-white text-sm font-semibold py-3 px-4 rounded-xl shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-download"></i><span>Download PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Beasiswa - Dynamic -->
    <div id="beasiswaModal" class="modal-overlay" onclick="closeModalOnOverlay(event, 'beasiswaModal')">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <i class="fas fa-graduation-cap text-xl"></i>
                    <div>
                        <h4 class="font-bold text-lg">Beasiswa MA Alhikam</h4>
                        <p class="text-xs opacity-80">Program Beasiswa</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeModal('beasiswaModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <?php
                $icons = ['Tahfidz' => 'fa-quran', 'Akademik' => 'fa-award', 'Yatim/Piatu' => 'fa-hand-holding-heart'];
                foreach ($beasiswaList as $jenis => $items):
                    ?>
                    <div class="beasiswa-card">
                        <div class="beasiswa-title"><i class="fas <?= $icons[$jenis] ?? 'fa-star' ?> mr-2"></i> Beasiswa
                            <?= htmlspecialchars($jenis) ?>
                        </div>
                        <div class="beasiswa-subtitle mb-2"><?= htmlspecialchars($items[0]['kategori'] ?? '') ?></div>
                        <div class="space-y-2 text-xs">
                            <?php foreach ($items as $item): ?>
                                <div class="flex justify-between bg-white/60 rounded-lg p-2">
                                    <span><?= htmlspecialchars($item['syarat']) ?></span>
                                    <span class="font-bold text-primary"><?= htmlspecialchars($item['benefit']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="note-box mt-3">
                    <p class="font-semibold"><i class="fas fa-info-circle mr-1"></i> Beasiswa tidak dapat digabungkan.
                        Pilih satu jenis beasiswa saja.</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?= htmlspecialchars($settings['link_beasiswa'] ?? '#') ?>" target="_blank"
                    class="btn-animated w-full bg-primary hover:bg-primary-dark text-white text-sm font-semibold py-3 px-4 rounded-xl shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-external-link-alt"></i><span>Info Beasiswa Lengkap</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.add('active'); document.body.style.overflow = 'hidden'; }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); document.body.style.overflow = ''; }
        function closeModalOnOverlay(e, id) { if (e.target.classList.contains('modal-overlay')) closeModal(id); }
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { document.querySelectorAll('.modal-overlay.active').forEach(m => { m.classList.remove('active'); document.body.style.overflow = ''; }); } });
    </script>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });

        // Counter Animation
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start);
                }
            }, 16);
        }

        // Observe stats for counter animation
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.counter');
                    counters.forEach(counter => {
                        const target = parseInt(counter.dataset.target);
                        animateCounter(counter, target);
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) statsObserver.observe(statsSection);

        // Add page loaded class for smooth entrance
        document.body.classList.add('page-loaded');
    </script>
</body>

</html>