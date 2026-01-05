<?php
require_once 'api/config.php';
$conn = getConnection();

// Check if registration is open
$statusPendaftaran = getSetting($conn, 'status_pendaftaran');
if ($statusPendaftaran !== '1') {
    header('Location: index.php');
    exit;
}

$tahunAjaran = getSetting($conn, 'tahun_ajaran') ?? '2026/2027';
$linkGrupWa = getSetting($conn, 'link_grup_wa') ?? '';
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran - SPMB <?= htmlspecialchars($tahunAjaran) ?></title>
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
        /* Hide scrollbar but keep scroll functionality */
        html,
        body {
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE/Edge */
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: linear-gradient(135deg, #E67E22 0%, #F39C12 100%);
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            outline: none;
            text-transform: uppercase;
        }

        .form-input[type="password"],
        #password,
        #passwordConfirm,
        input[name="password"],
        input[name="password_confirm"] {
            text-transform: none;
        }

        .form-input:focus {
            border-color: #E67E22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }

        .form-input.error {
            border-color: #ef4444;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-label.required::after {
            content: ' *';
            color: #ef4444;
        }

        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
        }

        .step-dot.active {
            background: white;
            transform: scale(1.2);
        }

        .step-dot.completed {
            background: #10b981;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeIn 0.3s ease;
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

        .file-upload {
            border: 2px dashed #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-upload:hover {
            border-color: #E67E22;
            background: rgba(230, 126, 34, 0.02);
        }

        .file-upload.has-file {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        /* Page load animation */
        .page-loaded {
            animation: pageLoad 0.5s ease-out;
        }

        @keyframes pageLoad {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Form step slide animation */
        .form-step.active {
            display: block;
            animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Form group stagger */
        .form-group {
            animation: fadeInUp 0.4s ease-out backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-step.active .form-group:nth-child(1) {
            animation-delay: 0.1s;
        }

        .form-step.active .form-group:nth-child(2) {
            animation-delay: 0.15s;
        }

        .form-step.active .form-group:nth-child(3) {
            animation-delay: 0.2s;
        }

        .form-step.active .form-group:nth-child(4) {
            animation-delay: 0.25s;
        }

        .form-step.active .form-group:nth-child(5) {
            animation-delay: 0.3s;
        }

        .form-step.active .form-group:nth-child(6) {
            animation-delay: 0.35s;
        }

        /* Button hover effects */
        .btn-next,
        .btn-prev,
        .btn-submit {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-next::before,
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-next:hover::before,
        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-next:hover,
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -6px rgba(230, 126, 34, 0.4);
        }

        /* Progress indicator animation */
        .step-indicator {
            transition: all 0.3s ease;
        }

        .step-indicator.active {
            transform: scale(1.1);
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
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        .autocomplete-dropdown .item:hover {
            background-color: #fef3e2;
        }

        .autocomplete-dropdown .item:last-child {
            border-bottom: none;
        }

        .autocomplete-dropdown .no-result {
            padding: 0.75rem 1rem;
            color: #9ca3af;
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen page-loaded">
    <!-- Sticky Topbar -->
    <header class="topbar">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div>
                    <h1 class="font-bold text-lg">PPDB <?= htmlspecialchars($tahunAjaran) ?></h1>
                    <p class="text-sm text-white/70 hidden sm:block">PP Mambaul Huda Pajomblangan</p>
                </div>
            </div>
            <a href="index.php"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition flex items-center gap-2">
                <i class="fas fa-home"></i><span class="hidden sm:inline">Home</span>
            </a>
        </div>
        <!-- Progress Tracker -->
        <div class="border-t border-white/10">
            <div class="max-w-4xl mx-auto px-4 py-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 flex-1">
                        <div class="hidden sm:flex items-center gap-1 text-xs text-white/70">
                            <span id="progressPercentage">0%</span>
                        </div>
                        <div class="flex-1 bg-white/20 rounded-full h-2 overflow-hidden">
                            <div id="progressBar" class="bg-white h-full rounded-full transition-all duration-500"
                                style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="step-indicator" data-step="1">
                            <span
                                class="w-6 h-6 rounded-full bg-white text-primary text-xs font-bold flex items-center justify-center">1</span>
                        </div>
                        <div class="w-4 h-0.5 bg-white/30"></div>
                        <div class="step-indicator" data-step="2">
                            <span
                                class="w-6 h-6 rounded-full bg-white/30 text-white/70 text-xs font-bold flex items-center justify-center">2</span>
                        </div>
                        <div class="w-4 h-0.5 bg-white/30"></div>
                        <div class="step-indicator" data-step="3">
                            <span
                                class="w-6 h-6 rounded-full bg-white/30 text-white/70 text-xs font-bold flex items-center justify-center">3</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Draft Banner -->
    <div id="draftBanner" class="hidden bg-blue-50 border-b border-blue-200">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-save text-blue-600"></i>
                <span class="text-sm text-blue-800">Ada draft tersimpan. <button type="button" onclick="loadDraft()"
                        class="font-semibold underline">Lanjutkan pengisian</button></span>
            </div>
            <button type="button" onclick="clearDraft()" class="text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-times"></i> Hapus
            </button>
        </div>
    </div>

    <!-- Form Container -->
    <div class="max-w-4xl mx-auto px-4 py-6">
        <form id="registrationForm" enctype="multipart/form-data">
            <!-- Step 1: Data Calon Siswa -->
            <div class="form-step active" id="step1">
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">Data Calon Siswa</h2>
                    <p class="text-sm text-gray-500 mb-6">Lengkapi data diri calon siswa dengan benar</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-input" placeholder="Masukkan nama lengkap"
                                required>
                        </div>

                        <div>
                            <label class="form-label required">Lembaga yang Dituju</label>
                            <select name="lembaga" class="form-input" required>
                                <option value="">Pilih Lembaga</option>
                                <option value="SMP NU BP">SMP NU BP</option>
                                <option value="MA ALHIKAM">MA ALHIKAM</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">NISN</label>
                            <input type="text" name="nisn" class="form-input" placeholder="Nomor Induk Siswa Nasional">
                        </div>

                        <div>
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-input" placeholder="Kota kelahiran">
                        </div>

                        <div>
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-input">
                        </div>

                        <div>
                            <label class="form-label required">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-input" required>
                                <option value="">Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Jumlah Saudara</label>
                            <input type="number" name="jumlah_saudara" class="form-input" value="0" min="0">
                        </div>

                        <div>
                            <label class="form-label">No. Kartu Keluarga</label>
                            <input type="text" name="no_kk" class="form-input" placeholder="16 digit nomor KK">
                        </div>

                        <div>
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" class="form-input" placeholder="16 digit NIK">
                        </div>

                        <div>
                            <label class="form-label required">Provinsi</label>
                            <select id="provinsi" name="provinsi" class="form-input" required
                                onchange="onProvinsiChange(this)">
                                <option value="">-- Pilih Provinsi --</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label required">Kota/Kabupaten</label>
                            <select id="kota_kab" name="kota_kab" class="form-input" required
                                onchange="onKotaChange(this)">
                                <option value="">-- Pilih Kota/Kabupaten --</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label required">Kecamatan</label>
                            <select id="kecamatan" name="kecamatan" class="form-input" required disabled
                                onchange="onKecamatanChange(this)">
                                <option value="">-- Pilih Provinsi & Kota dulu --</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label required">Kelurahan/Desa</label>
                            <select id="kelurahan_desa" name="kelurahan_desa" class="form-input" required disabled>
                                <option value="">-- Pilih Kecamatan dulu --</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Detail Alamat</label>
                            <textarea name="alamat" class="form-input" rows="2"
                                placeholder="RT/RW, Nama Jalan, Nomor Rumah, dll"></textarea>
                        </div>

                        <div>
                            <label class="form-label">Asal Sekolah</label>
                            <input type="text" name="asal_sekolah" class="form-input"
                                placeholder="Nama sekolah sebelumnya">
                        </div>

                        <div>
                            <label class="form-label required">Status Mukim</label>
                            <select name="status_mukim" class="form-input" required>
                                <option value="">Pilih Status</option>
                                <option value="PONDOK PP MAMBAUL HUDA">Pondok PP Mambaul Huda</option>
                                <option value="PONDOK SELAIN PP MAMBAUL HUDA">Pondok Selain PP Mambaul Huda</option>
                                <option value="TIDAK PONDOK">Tidak Pondok</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Prestasi Section -->
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h3 class="text-md font-bold text-gray-800 mb-1">Prestasi (Opsional)</h3>
                    <p class="text-sm text-gray-500 mb-4">Jika memiliki prestasi akademik/non-akademik</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="form-label">Nama Prestasi</label>
                            <input type="text" name="prestasi" class="form-input"
                                placeholder="Contoh: Lomba MTQ, Olimpiade Matematika">
                        </div>

                        <div>
                            <label class="form-label">Tingkat</label>
                            <select name="tingkat_prestasi" class="form-input">
                                <option value="">Pilih Tingkat</option>
                                <option value="KABUPATEN">Kabupaten</option>
                                <option value="PROVINSI">Provinsi</option>
                                <option value="NASIONAL">Nasional</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Juara</label>
                            <select name="juara" class="form-input">
                                <option value="">Pilih Juara</option>
                                <option value="1">Juara 1</option>
                                <option value="2">Juara 2</option>
                                <option value="3">Juara 3</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Upload Sertifikat</label>
                            <div class="file-upload" onclick="document.getElementById('file_sertifikat').click()">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500" id="fileLabel">Klik untuk upload sertifikat (JPG, PNG,
                                    PDF, max 5MB)</p>
                                <input type="file" id="file_sertifikat" name="file_sertifikat" class="hidden"
                                    accept=".jpg,.jpeg,.png,.pdf" onchange="updateFileLabel(this)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tambahan -->
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h3 class="text-md font-bold text-gray-800 mb-4">Informasi Tambahan</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">No. PIP/PKH</label>
                            <input type="text" name="pip_pkh" class="form-input" placeholder="Jika memiliki">
                        </div>

                        <div>
                            <label class="form-label">Sumber Informasi</label>
                            <select name="sumber_info" class="form-input">
                                <option value="">Pilih</option>
                                <option value="ALUMNI">Alumni</option>
                                <option value="KELUARGA">Keluarga</option>
                                <option value="TEMAN">Teman</option>
                                <option value="SOSIAL MEDIA">Sosial Media</option>
                                <option value="BROSUR">Brosur</option>
                                <option value="LAINNYA">Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="nextStep(1)"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition">
                    Lanjutkan <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>

            <!-- Step 2: Data Wali -->
            <div class="form-step" id="step2">
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">Data Ayah</h2>
                    <p class="text-sm text-gray-500 mb-6">Lengkapi data ayah/wali</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="form-label">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="form-input" placeholder="Nama lengkap ayah">
                        </div>

                        <div>
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir_ayah" class="form-input" placeholder="Kota kelahiran">
                        </div>

                        <div>
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir_ayah" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">NIK Ayah</label>
                            <input type="text" name="nik_ayah" class="form-input" placeholder="16 digit NIK">
                        </div>

                        <div>
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan_ayah" class="form-input" placeholder="Pekerjaan ayah">
                        </div>

                        <div>
                            <label class="form-label">Penghasilan</label>
                            <select name="penghasilan_ayah" class="form-input">
                                <option value="">Pilih Range</option>
                                <option value="< 1 Juta">
                                    < 1 Juta</option>
                                <option value="1-3 Juta">1-3 Juta</option>
                                <option value="3-5 Juta">3-5 Juta</option>
                                <option value="> 5 Juta">> 5 Juta</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">Data Ibu</h2>
                    <p class="text-sm text-gray-500 mb-6">Lengkapi data ibu</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="form-label">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="form-input" placeholder="Nama lengkap ibu">
                        </div>

                        <div>
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir_ibu" class="form-input" placeholder="Kota kelahiran">
                        </div>

                        <div>
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir_ibu" class="form-input">
                        </div>

                        <div>
                            <label class="form-label">NIK Ibu</label>
                            <input type="text" name="nik_ibu" class="form-input" placeholder="16 digit NIK">
                        </div>

                        <div>
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan_ibu" class="form-input" placeholder="Pekerjaan ibu">
                        </div>

                        <div>
                            <label class="form-label">Penghasilan</label>
                            <select name="penghasilan_ibu" class="form-input">
                                <option value="">Pilih Range</option>
                                <option value="< 1 Juta">
                                    < 1 Juta</option>
                                <option value="1-3 Juta">1-3 Juta</option>
                                <option value="3-5 Juta">3-5 Juta</option>
                                <option value="> 5 Juta">> 5 Juta</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen Section -->
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h3 class="text-md font-bold text-gray-800 mb-1">Upload Dokumen</h3>
                    <p class="text-sm text-gray-500 mb-4">Upload dokumen dalam format PDF atau Gambar (JPG, PNG, max
                        2MB)</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Kartu Keluarga (KK)</label>
                            <input type="file" name="file_kk" accept=".pdf,.jpg,.jpeg,.png" class="form-input text-sm">
                        </div>
                        <div>
                            <label class="form-label">KTP Orang Tua</label>
                            <input type="file" name="file_ktp_ortu" accept=".pdf,.jpg,.jpeg,.png"
                                class="form-input text-sm">
                        </div>
                        <div>
                            <label class="form-label">Akta Kelahiran</label>
                            <input type="file" name="file_akta" accept=".pdf,.jpg,.jpeg,.png"
                                class="form-input text-sm">
                        </div>
                        <div>
                            <label class="form-label">Ijazah (Opsional)</label>
                            <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png"
                                class="form-input text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h3 class="text-md font-bold text-gray-800 mb-4">Kontak Wali</h3>
                    <div>
                        <label class="form-label required">No. HP WhatsApp Wali</label>
                        <div class="flex">
                            <span
                                class="bg-gray-100 border border-r-0 border-gray-300 px-3 py-2 rounded-l-xl text-gray-600 text-sm font-medium">+62</span>
                            <input type="tel" name="no_hp_wali" id="noHpWali" class="form-input rounded-l-none flex-1"
                                placeholder="8xxxxxxxxxx" required minlength="9" maxlength="13" pattern="[0-9]{9,13}"
                                onblur="checkDuplicatePhone()" oninput="formatPhoneInput(this)">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Contoh: 812345678 (tanpa 0 di depan). Nomor ini akan
                            menjadi username untuk login.</p>
                        <p id="phoneError" class="text-xs text-red-600 mt-1 hidden"></p>
                    </div>
                </div>

                <!-- Account Creation Section -->
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4 border-2 border-primary/20">
                    <h3 class="text-md font-bold text-gray-800 mb-1"><i
                            class="fas fa-user-lock text-primary mr-2"></i>Buat Akun</h3>
                    <p class="text-sm text-gray-500 mb-4">Buat password untuk mengakses portal pendaftar</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label required">Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" class="form-input pr-10"
                                    placeholder="Minimal 6 karakter" required minlength="6">
                                <button type="button" onclick="togglePasswordVisibility('password', 'eyeIcon1')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="eyeIcon1"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label required">Konfirmasi Password</label>
                            <div class="relative">
                                <input type="password" name="password_confirm" id="passwordConfirm"
                                    class="form-input pr-10" placeholder="Ulangi password" required>
                                <button type="button" onclick="togglePasswordVisibility('passwordConfirm', 'eyeIcon2')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="eyeIcon2"></i>
                                </button>
                            </div>
                            <p id="passwordError" class="text-xs text-red-600 mt-1 hidden">Password tidak cocok!</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3"><i class="fas fa-info-circle mr-1"></i>Setelah mendaftar, Anda
                        dapat login menggunakan No. HP dan password ini untuk melihat/mengedit data pendaftaran.</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="prevStep(2)"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 rounded-xl transition">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </button>
                    <button type="button" onclick="nextStep(2)"
                        class="flex-1 bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition">
                        Lanjutkan <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Konfirmasi -->
            <div class="form-step" id="step3">
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">Konfirmasi Data</h2>
                    <p class="text-sm text-gray-500 mb-6">Periksa kembali data sebelum mengirim</p>

                    <div id="summaryContent" class="space-y-4">
                        <!-- Will be filled by JavaScript -->
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">Perhatian!</p>
                            <p class="text-xs text-yellow-700">Pastikan semua data sudah benar. Data yang sudah dikirim
                                tidak dapat diubah.</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="prevStep(3)"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 rounded-xl transition">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </button>
                    <button type="submit"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Pendaftaran
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-green-600 text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Pendaftaran Berhasil!</h3>

            <!-- Registrant Info -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4 text-left">
                <p class="text-sm text-gray-700 mb-2">
                    <i class="fas fa-user text-green-600 mr-2"></i>
                    <span class="font-semibold" id="successNama">-</span> telah terdaftar
                </p>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-ticket-alt text-green-600 mr-2"></i>
                    No. Registrasi: <span class="font-mono font-bold text-green-700" id="successNoReg">-</span>
                </p>
            </div>

            <!-- Login Info -->
            <div class="bg-primary/10 rounded-lg p-3 mb-4 text-left">
                <p class="text-xs text-gray-600 mb-1"><i class="fas fa-info-circle text-primary mr-1"></i>Untuk melihat
                    atau mengedit data:</p>
                <p class="text-sm font-medium text-gray-800">Klik tombol <span class="text-primary">Masuk</span> di
                    halaman utama, lalu login dengan No. HP <span class="font-mono font-bold text-primary"
                        id="successPhone">-</span> dan password Anda.</p>
            </div>

            <!-- Buttons -->
            <div class="space-y-2">
                <?php if (!empty($linkGrupWa)): ?>
                    <a href="<?= htmlspecialchars($linkGrupWa) ?>" target="_blank" onclick="redirectToHome(event, this)"
                        class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition">
                        <i class="fab fa-whatsapp mr-2"></i>Masuk Grup WA Pendaftar
                    </a>
                <?php endif; ?>
                <a href="index.php"
                    class="block w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times text-red-600 text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Terjadi Kesalahan</h3>
            <p class="text-gray-500 text-sm mb-6" id="errorMessage">Gagal mengirim data pendaftaran.</p>
            <button onclick="closeErrorModal()"
                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>

    <!-- Validation Modal -->
    <div id="validationModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Data Belum Lengkap</h3>
                    <p class="text-gray-500 text-sm">Mohon lengkapi field berikut:</p>
                </div>
            </div>
            <ul id="validationErrors"
                class="bg-red-50 text-red-700 text-sm rounded-lg p-4 mb-4 space-y-1 max-h-60 overflow-y-auto">
            </ul>
            <button onclick="closeValidationModal()"
                class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition">
                <i class="fas fa-check mr-2"></i>Oke, Saya Perbaiki
            </button>
        </div>
    </div>

    <script>
        let currentStep = 1;

        function updateStepIndicator() {
            document.querySelectorAll('.step-dot').forEach((dot, index) => {
                dot.classList.remove('active', 'completed');
                if (index + 1 === currentStep) {
                    dot.classList.add('active');
                } else if (index + 1 < currentStep) {
                    dot.classList.add('completed');
                }
            });
        }

        // Toggle password visibility
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Field labels for validation messages
        const fieldLabels = {
            'nama': 'Nama Lengkap',
            'lembaga': 'Lembaga yang Dituju',
            'jenis_kelamin': 'Jenis Kelamin',
            'alamat': 'Alamat Lengkap',
            'status_mukim': 'Status Mukim',
            'no_hp_wali': 'No. HP WhatsApp Wali',
            'password': 'Password',
            'password_confirm': 'Konfirmasi Password'
        };

        function getFieldLabel(field) {
            const name = field.getAttribute('name');
            if (fieldLabels[name]) return fieldLabels[name];

            // Try to get from label
            const label = field.closest('div')?.querySelector('label');
            if (label) return label.textContent.replace('*', '').trim();

            return name;
        }

        function showValidationModal(errors) {
            const list = document.getElementById('validationErrors');
            list.innerHTML = errors.map(e => `<li><i class="fas fa-times-circle mr-2"></i>${e}</li>`).join('');
            document.getElementById('validationModal').classList.remove('hidden');
            document.getElementById('validationModal').classList.add('flex');
        }

        function closeValidationModal() {
            document.getElementById('validationModal').classList.add('hidden');
            document.getElementById('validationModal').classList.remove('flex');
        }

        function nextStep(from) {
            const step = document.getElementById('step' + from);
            const requiredFields = step.querySelectorAll('[required]');
            let errors = [];

            requiredFields.forEach(field => {
                const value = field.value.trim();
                const label = getFieldLabel(field);

                // Check empty
                if (!value) {
                    field.classList.add('error');
                    errors.push(`${label} harus diisi`);
                    return;
                }

                // Check minlength
                const minLen = field.getAttribute('minlength');
                if (minLen && value.length < parseInt(minLen)) {
                    field.classList.add('error');
                    errors.push(`${label} minimal ${minLen} karakter`);
                    return;
                }

                // Check pattern
                const pattern = field.getAttribute('pattern');
                if (pattern && !new RegExp(`^${pattern}$`).test(value)) {
                    field.classList.add('error');
                    errors.push(`${label} format tidak valid`);
                    return;
                }

                field.classList.remove('error');
            });

            // Additional validation for step 2 - password match
            if (from === 2) {
                const password = document.getElementById('password')?.value;
                const confirm = document.getElementById('passwordConfirm')?.value;
                if (password && confirm && password !== confirm) {
                    document.getElementById('passwordConfirm').classList.add('error');
                    errors.push('Konfirmasi Password tidak cocok dengan Password');
                }

                // Check phone duplicate
                if (!phoneValid) {
                    errors.push('Nomor WA sudah terdaftar! Gunakan nomor lain atau login.');
                }
            }

            if (errors.length > 0) {
                showValidationModal(errors);
                return;
            }

            if (from === 2) {
                generateSummary();
            }

            document.getElementById('step' + from).classList.remove('active');
            document.getElementById('step' + (from + 1)).classList.add('active');
            currentStep = from + 1;
            updateStepIndicator();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function prevStep(from) {
            document.getElementById('step' + from).classList.remove('active');
            document.getElementById('step' + (from - 1)).classList.add('active');
            currentStep = from - 1;
            updateStepIndicator();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function generateSummary() {
            const form = document.getElementById('registrationForm');
            const formData = new FormData(form);

            // Build combined alamat from provinsi to kelurahan
            const provinsi = formData.get('provinsi') || '';
            const kotaKab = formData.get('kota_kab') || '';
            const kecamatan = formData.get('kecamatan') || '';
            const kelurahan = formData.get('kelurahan_desa') || '';
            const alamatParts = [kelurahan, kecamatan, kotaKab, provinsi].filter(p => p);
            const alamatGabungan = alamatParts.join(', ') || '-';

            let html = '<div class="border border-gray-200 rounded-lg overflow-hidden">';

            const fields = [
                { label: 'Nama', name: 'nama' },
                { label: 'Lembaga', name: 'lembaga' },
                { label: 'Jenis Kelamin', name: 'jenis_kelamin', format: v => v === 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' },
                { label: 'Alamat', value: alamatGabungan },
                { label: 'Status Mukim', name: 'status_mukim' },
                { label: 'Nama Ayah', name: 'nama_ayah' },
                { label: 'Nama Ibu', name: 'nama_ibu' },
                { label: 'No. HP Wali', name: 'no_hp_wali' },
            ];

            fields.forEach((field, i) => {
                let value = field.value || formData.get(field.name) || '-';
                if (field.format && value !== '-') value = field.format(value);
                // Convert to uppercase (except phone number)
                if (field.name !== 'no_hp_wali' && typeof value === 'string') {
                    value = value.toUpperCase();
                }
                html += `<div class="flex justify-between p-3 ${i % 2 === 0 ? 'bg-gray-50' : ''}">
                    <span class="text-sm text-gray-500">${field.label}</span>
                    <span class="text-sm font-medium text-gray-800">${value}</span>
                </div>`;
            });

            html += '</div>';
            document.getElementById('summaryContent').innerHTML = html;
        }

        function updateFileLabel(input) {
            const label = document.getElementById('fileLabel');
            const container = input.closest('.file-upload');
            if (input.files.length > 0) {
                label.textContent = input.files[0].name;
                container.classList.add('has-file');
            } else {
                label.textContent = 'Klik untuk upload sertifikat (JPG, PNG, PDF, max 5MB)';
                container.classList.remove('has-file');
            }
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
            document.getElementById('errorModal').classList.remove('flex');
        }

        // ============ DROPDOWN ADDRESS SELECTION ============
        let allProvinsi = [];
        let allKota = [];
        let kecamatanData = [];
        let kelurahanData = [];

        // Load all provinces and cities on page load
        async function loadAllWilayah() {
            try {
                // Load provinces
                const provResponse = await fetch('api/wilayah.php?type=provinsi');
                allProvinsi = await provResponse.json();

                // Populate provinsi dropdown
                const provSelect = document.getElementById('provinsi');
                allProvinsi.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name;
                    opt.dataset.id = p.id;
                    opt.textContent = p.name;
                    provSelect.appendChild(opt);
                });

                // Load all cities in PARALLEL
                const kotaPromises = allProvinsi.map(prov =>
                    fetch('api/wilayah.php?type=kota&id=' + prov.id)
                        .then(r => r.json())
                        .then(kotaList => {
                            if (Array.isArray(kotaList)) {
                                kotaList.forEach(kota => {
                                    kota.provinsi_id = prov.id;
                                    kota.provinsi_name = prov.name;
                                    allKota.push(kota);
                                });
                            }
                        })
                );
                await Promise.all(kotaPromises);

                // Populate kota dropdown
                populateKotaDropdown();

                console.log('Loaded', allProvinsi.length, 'provinces and', allKota.length, 'cities');
            } catch (e) {
                console.error('Failed to load wilayah:', e);
            }
        }

        // Populate kota dropdown (optionally filtered by provinsi)
        function populateKotaDropdown(provinsiName = null) {
            const kotaSelect = document.getElementById('kota_kab');
            kotaSelect.innerHTML = '<option value="">-- Pilih Kota/Kabupaten --</option>';

            let kotaList = allKota;
            if (provinsiName) {
                kotaList = allKota.filter(k => k.provinsi_name === provinsiName);
            }

            kotaList.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.name;
                opt.dataset.id = k.id;
                opt.dataset.provinsiName = k.provinsi_name;
                opt.textContent = provinsiName ? k.name : `${k.name} (${k.provinsi_name})`;
                kotaSelect.appendChild(opt);
            });
        }

        // When provinsi changes
        function onProvinsiChange(select) {
            const provinsiName = select.value;

            // Filter kota by selected provinsi
            populateKotaDropdown(provinsiName || null);

            // Reset child fields
            resetKecamatan();
            resetKelurahan();
        }

        // When kota changes
        async function onKotaChange(select) {
            const selectedOption = select.options[select.selectedIndex];
            const kotaId = selectedOption?.dataset?.id;
            const provinsiName = selectedOption?.dataset?.provinsiName;

            if (kotaId) {
                // Auto-fill provinsi if not set
                const provSelect = document.getElementById('provinsi');
                if (!provSelect.value && provinsiName) {
                    provSelect.value = provinsiName;
                }

                // Load and populate kecamatan
                await loadKecamatan(kotaId);
            } else {
                resetKecamatan();
                resetKelurahan();
            }
        }

        // When kecamatan changes
        async function onKecamatanChange(select) {
            const selectedOption = select.options[select.selectedIndex];
            const kecId = selectedOption?.dataset?.id;

            if (kecId) {
                await loadKelurahan(kecId);
            } else {
                resetKelurahan();
            }
        }

        // Load kecamatan data
        async function loadKecamatan(kotaId) {
            try {
                const response = await fetch('api/wilayah.php?type=kecamatan&id=' + kotaId);
                kecamatanData = await response.json();

                const kecSelect = document.getElementById('kecamatan');
                kecSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                kecamatanData.forEach(k => {
                    const opt = document.createElement('option');
                    opt.value = k.name;
                    opt.dataset.id = k.id;
                    opt.textContent = k.name;
                    kecSelect.appendChild(opt);
                });
                kecSelect.disabled = false;

                resetKelurahan();
            } catch (e) {
                console.error('Failed to load kecamatan:', e);
            }
        }

        // Load kelurahan data
        async function loadKelurahan(kecamatanId) {
            try {
                const response = await fetch('api/wilayah.php?type=kelurahan&id=' + kecamatanId);
                kelurahanData = await response.json();

                const kelSelect = document.getElementById('kelurahan_desa');
                kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';
                kelurahanData.forEach(k => {
                    const opt = document.createElement('option');
                    opt.value = k.name;
                    opt.dataset.id = k.id;
                    opt.textContent = k.name;
                    kelSelect.appendChild(opt);
                });
                kelSelect.disabled = false;
            } catch (e) {
                console.error('Failed to load kelurahan:', e);
            }
        }

        // Reset helpers
        function resetKecamatan() {
            const kec = document.getElementById('kecamatan');
            kec.innerHTML = '<option value="">-- Pilih Provinsi & Kota dulu --</option>';
            kec.disabled = true;
            kecamatanData = [];
        }

        function resetKelurahan() {
            const kel = document.getElementById('kelurahan_desa');
            kel.innerHTML = '<option value="">-- Pilih Kecamatan dulu --</option>';
            kel.disabled = true;
            kelurahanData = [];
        }

        // Initialize on page load
        loadAllWilayah();

        // Open WhatsApp group then redirect to home
        function redirectToHome(event, element) {
            // Open the WA link in new tab (default behavior)
            // then redirect current page to home after a short delay
            setTimeout(function () {
                window.location.href = 'index.php';
            }, 500);
        }

        // Phone duplicate check
        let phoneValid = true;

        // Format phone input - remove non-digits and leading 0
        function formatPhoneInput(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            // Remove leading 0 if present
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            input.value = value;
        }

        async function checkDuplicatePhone() {
            const phone = document.getElementById('noHpWali').value.trim();
            const errorEl = document.getElementById('phoneError');

            // Minimum 9 digits for valid Indonesian mobile
            if (phone.length < 9) {
                if (phone.length > 0) {
                    errorEl.textContent = 'Nomor harus minimal 9 digit (contoh: 812345678)';
                    errorEl.classList.remove('hidden');
                } else {
                    errorEl.classList.add('hidden');
                }
                phoneValid = phone.length === 0;
                return;
            }

            // Prepend +62 for API check
            const fullPhone = '+62' + phone;

            try {
                const response = await fetch('api/pendaftaran.php?check_phone=' + encodeURIComponent(fullPhone));
                const result = await response.json();

                if (result.exists) {
                    errorEl.textContent = 'Nomor WA sudah terdaftar! Silakan login melalui tombol Profil di halaman utama.';
                    errorEl.classList.remove('hidden');
                    document.getElementById('noHpWali').classList.add('error');
                    phoneValid = false;
                } else {
                    errorEl.classList.add('hidden');
                    document.getElementById('noHpWali').classList.remove('error');
                    phoneValid = true;
                }
            } catch (e) {
                phoneValid = true;
            }
        }

        // Password match validation
        document.getElementById('passwordConfirm').addEventListener('input', function () {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            const errorEl = document.getElementById('passwordError');

            if (confirm && password !== confirm) {
                errorEl.classList.remove('hidden');
                this.classList.add('error');
            } else {
                errorEl.classList.add('hidden');
                this.classList.remove('error');
            }
        });

        document.getElementById('registrationForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            // Prepend +62 to phone number
            const phone = formData.get('no_hp_wali');
            formData.set('no_hp_wali', '+62' + phone);

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...';

            try {
                const response = await fetch('api/pendaftaran.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Clear draft on success
                    clearDraft();

                    // Populate success modal with registration data
                    if (result.data) {
                        document.getElementById('successNama').textContent = result.data.nama || '-';
                        document.getElementById('successNoReg').textContent = result.data.no_registrasi || '-';
                        document.getElementById('successPhone').textContent = result.data.no_hp_wali || '-';
                    }

                    document.getElementById('successModal').classList.remove('hidden');
                    document.getElementById('successModal').classList.add('flex');
                } else {
                    document.getElementById('errorMessage').textContent = result.message || 'Gagal mengirim data pendaftaran.';
                    document.getElementById('errorModal').classList.remove('hidden');
                    document.getElementById('errorModal').classList.add('flex');
                }
            } catch (error) {
                document.getElementById('errorMessage').textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                document.getElementById('errorModal').classList.remove('hidden');
                document.getElementById('errorModal').classList.add('flex');
            }

            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Kirim Pendaftaran';
        });

        // ============ SAVE DRAFT FUNCTIONALITY ============
        const DRAFT_KEY = 'spmb_draft';
        const DRAFT_FIELDS = [
            'nama', 'lembaga', 'nisn', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
            'jumlah_saudara', 'no_kk', 'nik', 'provinsi', 'kota_kab', 'kecamatan',
            'kelurahan_desa', 'alamat', 'asal_sekolah', 'status_mukim', 'prestasi',
            'tingkat_prestasi', 'juara', 'pip_pkh', 'sumber_info',
            'nama_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'nik_ayah',
            'pekerjaan_ayah', 'penghasilan_ayah',
            'nama_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'nik_ibu',
            'pekerjaan_ibu', 'penghasilan_ibu', 'no_hp_wali'
        ];

        function saveDraft() {
            const form = document.getElementById('registrationForm');
            const draft = { step: currentStep, timestamp: Date.now() };

            DRAFT_FIELDS.forEach(field => {
                const element = form.querySelector(`[name="${field}"]`);
                if (element) {
                    if (element.type === 'radio') {
                        const checked = form.querySelector(`[name="${field}"]:checked`);
                        draft[field] = checked ? checked.value : '';
                    } else {
                        draft[field] = element.value;
                    }
                }
            });

            localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
            console.log('Draft saved');
        }

        function loadDraft() {
            const draftStr = localStorage.getItem(DRAFT_KEY);
            if (!draftStr) return false;

            try {
                const draft = JSON.parse(draftStr);
                const form = document.getElementById('registrationForm');

                DRAFT_FIELDS.forEach(field => {
                    if (draft[field] !== undefined) {
                        const element = form.querySelector(`[name="${field}"]`);
                        if (element) {
                            if (element.type === 'radio') {
                                const radio = form.querySelector(`[name="${field}"][value="${draft[field]}"]`);
                                if (radio) radio.checked = true;
                            } else {
                                element.value = draft[field];
                            }
                        }
                    }
                });

                // Go to saved step
                if (draft.step && draft.step > 1) {
                    for (let i = 1; i < draft.step; i++) {
                        document.getElementById('step' + i).classList.remove('active');
                    }
                    document.getElementById('step' + draft.step).classList.add('active');
                    currentStep = draft.step;
                    updateProgressTracker();
                }

                document.getElementById('draftBanner').classList.add('hidden');
                updateProgress();
                console.log('Draft loaded');
                return true;
            } catch (e) {
                console.error('Failed to load draft:', e);
                return false;
            }
        }

        function clearDraft() {
            localStorage.removeItem(DRAFT_KEY);
            document.getElementById('draftBanner').classList.add('hidden');
            console.log('Draft cleared');
        }

        function checkDraft() {
            const draftStr = localStorage.getItem(DRAFT_KEY);
            if (draftStr) {
                try {
                    const draft = JSON.parse(draftStr);
                    // Only show if draft is less than 7 days old
                    if (Date.now() - draft.timestamp < 7 * 24 * 60 * 60 * 1000) {
                        document.getElementById('draftBanner').classList.remove('hidden');
                    } else {
                        clearDraft();
                    }
                } catch (e) {
                    clearDraft();
                }
            }
        }

        // Auto-save on input change
        document.getElementById('registrationForm').addEventListener('input', function (e) {
            // Debounce save
            clearTimeout(window.draftSaveTimeout);
            window.draftSaveTimeout = setTimeout(saveDraft, 1000);
            updateProgress();
        });

        // ============ PROGRESS TRACKER ============
        function updateProgress() {
            const form = document.getElementById('registrationForm');
            const allFields = form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), select, textarea');
            let filled = 0;
            let total = 0;

            allFields.forEach(field => {
                if (field.name && !field.disabled) {
                    total++;
                    if (field.type === 'radio') {
                        const checked = form.querySelector(`[name="${field.name}"]:checked`);
                        if (checked) filled++;
                    } else if (field.value.trim()) {
                        filled++;
                    }
                }
            });

            // Count unique radio groups
            const radioGroups = {};
            form.querySelectorAll('input[type="radio"]').forEach(radio => {
                radioGroups[radio.name] = true;
            });
            total -= Object.keys(radioGroups).length; // Remove duplicates from total

            const percentage = total > 0 ? Math.round((filled / total) * 100) : 0;
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressPercentage').textContent = percentage + '%';
        }

        function updateProgressTracker() {
            document.querySelectorAll('.step-indicator').forEach(indicator => {
                const step = parseInt(indicator.dataset.step);
                const span = indicator.querySelector('span');

                if (step < currentStep) {
                    span.className = 'w-6 h-6 rounded-full bg-green-500 text-white text-xs font-bold flex items-center justify-center';
                    span.innerHTML = '<i class="fas fa-check text-[10px]"></i>';
                } else if (step === currentStep) {
                    span.className = 'w-6 h-6 rounded-full bg-white text-primary text-xs font-bold flex items-center justify-center';
                    span.textContent = step;
                } else {
                    span.className = 'w-6 h-6 rounded-full bg-white/30 text-white/70 text-xs font-bold flex items-center justify-center';
                    span.textContent = step;
                }
            });
        }

        // Update original step functions to include tracker update
        const originalNextStep = nextStep;
        nextStep = function (from) {
            originalNextStep(from);
            if (currentStep > from) {
                updateProgressTracker();
                saveDraft();
            }
        };

        const originalPrevStep = prevStep;
        prevStep = function (from) {
            originalPrevStep(from);
            updateProgressTracker();
        };

        // Initialize
        checkDraft();
        updateProgress();
        updateProgressTracker();
    </script>

    <!-- Image Compression -->
    <script src="js/image-compress.js"></script>
    <script>
        // Attach image compression to file inputs
        document.querySelectorAll('input[type="file"][accept*="image"], input[type="file"][accept*=".jpg"], input[type="file"][accept*=".png"]').forEach(input => {
            ImageCompressor.attachToInput(input);
        });
    </script>
</body>

</html>