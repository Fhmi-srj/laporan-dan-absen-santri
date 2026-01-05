<?php
require_once '../api/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();
$userId = $_SESSION['user_id'];
$uploadMessage = '';
$uploadError = '';

// Handle AJAX field update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_field') {
    header('Content-Type: application/json');

    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';

    $allowedFields = [
        'nama',
        'nik',
        'nisn',
        'no_kk',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'provinsi',
        'kota_kab',
        'kecamatan',
        'kelurahan_desa',
        'alamat',
        'lembaga',
        'status_mukim',
        'asal_sekolah',
        'pip_pkh',
        'sumber_info',
        'jumlah_saudara',
        'nama_ayah',
        'nik_ayah',
        'tempat_lahir_ayah',
        'tanggal_lahir_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'nama_ibu',
        'nik_ibu',
        'tempat_lahir_ibu',
        'tanggal_lahir_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'prestasi',
        'tingkat_prestasi',
        'juara'
    ];

    if (!in_array($field, $allowedFields)) {
        echo json_encode(['success' => false, 'message' => 'Field tidak valid']);
        exit;
    }

    $stmt = $conn->prepare("SELECT status FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!in_array($result['status'], ['pending', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak dapat diedit']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE pendaftaran SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan']);
    }
    exit;
}

// Handle file upload via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_file') {
    header('Content-Type: application/json');

    $field = $_POST['field'] ?? '';
    $allowedFields = ['file_kk', 'file_ktp_ortu', 'file_akta', 'file_ijazah', 'file_sertifikat'];

    if (!in_array($field, $allowedFields)) {
        echo json_encode(['success' => false, 'message' => 'Field tidak valid']);
        exit;
    }

    // Check can edit
    $stmt = $conn->prepare("SELECT status, $field as old_file FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!in_array($result['status'], ['pending', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak dapat diedit']);
        exit;
    }

    // Handle upload
    $uploadDir = $field === 'file_sertifikat' ? '../uploads/sertifikat/' : '../uploads/dokumen/';
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);

    if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        // Validate extension - allow images for all document types
        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowedExt)) {
            echo json_encode(['success' => false, 'message' => 'Format file tidak valid']);
            exit;
        }

        // Validate size (2MB)
        if ($_FILES['file']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2MB']);
            exit;
        }

        // Generate filename
        $filename = $userId . '_' . $field . '_' . time() . '.' . $ext;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename)) {
            // Delete old file
            if (!empty($result['old_file']) && file_exists($uploadDir . $result['old_file'])) {
                unlink($uploadDir . $result['old_file']);
            }

            // Update database
            $stmt = $conn->prepare("UPDATE pendaftaran SET $field = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $userId);

            if ($stmt->execute()) {
                $viewUrl = $uploadDir . $filename;
                echo json_encode(['success' => true, 'filename' => $filename, 'url' => $viewUrl]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupload file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
    }
    exit;
}

// Handle password change via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    header('Content-Type: application/json');

    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Password baru tidak cocok']);
        exit;
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        exit;
    }

    // Check can edit
    $stmt = $conn->prepare("SELECT status, password FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!in_array($result['status'], ['pending', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak dapat diedit karena sudah terverifikasi']);
        exit;
    }

    // Verify old password
    if (!password_verify($oldPassword, $result['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password lama salah']);
        exit;
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE pendaftaran SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password berhasil diubah']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengubah password']);
    }
    exit;
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM pendaftaran WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Get kontak for footer
$kontakList = [];
$result = $conn->query("SELECT * FROM kontak ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $kontakList[] = $row;
}

$_SESSION['user_status'] = $data['status'];
$conn->close();

$canEdit = in_array($data['status'], ['pending', 'rejected']);

$statusLabels = [
    'pending' => ['Menunggu Verifikasi', 'bg-yellow-100 text-yellow-800', 'fa-clock'],
    'verified' => ['Terverifikasi', 'bg-green-100 text-green-800', 'fa-check-circle'],
    'rejected' => ['Ditolak', 'bg-red-100 text-red-800', 'fa-times-circle']
];
$statusInfo = $statusLabels[$data['status']] ?? $statusLabels['pending'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SPMB</title>
    <link href="../images/logo-pondok.png" rel="icon">
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

        .main-scroll {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .main-scroll::-webkit-scrollbar {
            display: none;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .tab-btn {
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }

        .tab-btn:hover {
            color: #E67E22;
        }

        .tab-btn.active {
            color: #E67E22;
            border-bottom-color: #E67E22;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .form-row {
            display: flex;
            margin-bottom: 1rem;
            align-items: flex-start;
        }

        .form-label {
            width: 160px;
            flex-shrink: 0;
            font-size: 0.875rem;
            color: #6b7280;
            padding-top: 0.5rem;
        }

        .form-label.required::after {
            content: ' *';
            color: #ef4444;
        }

        .form-value {
            flex: 1;
        }

        .form-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #E67E22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }

        .form-input:disabled {
            background-color: #f9fafb;
            cursor: not-allowed;
        }

        .form-input.saving {
            border-color: #fbbf24;
        }

        .form-input.saved {
            border-color: #10b981;
        }

        .form-hint {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .file-upload-area {
            border: 2px dashed #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #E67E22;
            background: #fef8f4;
        }

        .file-upload-area.dragging {
            border-color: #E67E22;
            background: #fef3e8;
        }

        .file-upload-area.uploading {
            border-color: #fbbf24;
            background: #fffbeb;
        }

        .file-upload-area.success {
            border-color: #10b981;
            background: #ecfdf5;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }

            .form-label {
                width: 100%;
                padding-bottom: 0.25rem;
            }
        }

        .main-scroll {
            height: calc(100vh - 64px);
            overflow-y: auto;
        }

        /* Page load animation */
        .page-loaded {
            animation: pageLoad 0.5s ease-out;
        }

        @keyframes pageLoad {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tab content slide animation */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Card hover effect */
        .profile-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .profile-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
        }

        /* Tab button enhanced */
        .tab-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 2px solid transparent;
            position: relative;
        }

        .tab-btn::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: #E67E22;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .tab-btn:hover::after {
            width: 100%;
        }

        .tab-btn.active::after {
            width: 100%;
        }

        /* Form input pulse on save */
        @keyframes savePulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .form-input.saved {
            border-color: #10b981;
            animation: savePulse 0.6s ease-out;
        }

        /* Stagger animation for form rows */
        .form-row {
            animation: fadeInUp 0.4s ease-out backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-content.active .form-row:nth-child(1) {
            animation-delay: 0.05s;
        }

        .tab-content.active .form-row:nth-child(2) {
            animation-delay: 0.1s;
        }

        .tab-content.active .form-row:nth-child(3) {
            animation-delay: 0.15s;
        }

        .tab-content.active .form-row:nth-child(4) {
            animation-delay: 0.2s;
        }

        .tab-content.active .form-row:nth-child(5) {
            animation-delay: 0.25s;
        }

        .tab-content.active .form-row:nth-child(6) {
            animation-delay: 0.3s;
        }

        .tab-content.active .form-row:nth-child(7) {
            animation-delay: 0.35s;
        }

        .tab-content.active .form-row:nth-child(8) {
            animation-delay: 0.4s;
        }

        .tab-content.active .form-row:nth-child(9) {
            animation-delay: 0.45s;
        }

        .tab-content.active .form-row:nth-child(10) {
            animation-delay: 0.5s;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col page-loaded">
    <!-- Sticky Header -->
    <header class="bg-gradient-to-r from-primary to-primary-light text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div>
                    <h1 class="font-bold text-lg">SPMB Terpadu</h1>
                    <p class="text-sm text-white/70 hidden sm:block">Yayasan Almukarromah Pajomblangan</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="../index.php"
                    class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fas fa-home"></i><span class="hidden sm:inline">Home</span>
                </a>
                <a href="logout.php"
                    class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Scrollable Main Area -->
    <div class="main-scroll flex-1">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Sidebar Profile -->
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="profile-card bg-white rounded-2xl shadow-sm p-6 text-center">
                        <div
                            class="w-32 h-32 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-primary/20 to-primary-light/20 overflow-hidden border-4 border-white shadow-lg">
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-user text-4xl text-gray-400"></i>
                            </div>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800 mb-1"><?= htmlspecialchars($data['nama']) ?></h2>
                        <?php if (!empty($data['no_registrasi'])): ?>
                            <p class="text-xs text-primary font-mono font-bold mb-1">No. Reg:
                                <?= htmlspecialchars($data['no_registrasi']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($data['no_hp_wali'] ?? '-') ?>
                        </p>
                        <div
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium <?= $statusInfo[1] ?>">
                            <i class="fas <?= $statusInfo[2] ?>"></i><?= $statusInfo[0] ?>
                        </div>

                        <!-- Print Card Button -->
                        <a href="../kartu-peserta.php?id=<?= $userId ?>" target="_blank"
                            class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition w-full justify-center">
                            <i class="fas fa-id-card"></i>Cetak Kartu Peserta
                        </a>
                    </div>

                    <?php if (!empty($data['catatan_admin']) && $data['status'] !== 'verified'): ?>
                        <!-- Admin Notification Card -->
                        <?php
                        $notifClass = $data['status'] === 'rejected'
                            ? 'bg-red-50 border-red-200'
                            : 'bg-blue-50 border-blue-200';
                        $iconClass = $data['status'] === 'rejected'
                            ? 'text-red-500'
                            : 'text-blue-500';
                        $titleClass = $data['status'] === 'rejected'
                            ? 'text-red-700'
                            : 'text-blue-700';
                        $textClass = $data['status'] === 'rejected'
                            ? 'text-red-600'
                            : 'text-blue-600';
                        $title = $data['status'] === 'rejected'
                            ? 'Alasan Penolakan'
                            : 'Pesan dari Admin';
                        ?>
                        <div class="rounded-2xl shadow-sm p-4 mt-4 border <?= $notifClass ?>" id="adminNotifCard">
                            <h4 class="font-semibold text-sm mb-2 flex items-center gap-2 <?= $titleClass ?>">
                                <i
                                    class="fas <?= $data['status'] === 'rejected' ? 'fa-exclamation-circle' : 'fa-info-circle' ?> <?= $iconClass ?>"></i>
                                <?= $title ?>
                            </h4>
                            <p class="text-sm <?= $textClass ?> leading-relaxed">
                                <?= nl2br(htmlspecialchars($data['catatan_admin'])) ?>
                            </p>
                            <?php if (!empty($data['catatan_updated_at'])): ?>
                                <p class="text-xs text-gray-400 mt-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?= date('d M Y, H:i', strtotime($data['catatan_updated_at'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Document Completion Card -->
                    <div class="bg-white rounded-2xl shadow-sm p-4 mt-4" id="documentCheckerCard">
                        <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                            <i class="fas fa-folder-open text-primary"></i>Kelengkapan Dokumen
                        </h4>
                        <?php
                        $documents = [
                            'file_kk' => ['Kartu Keluarga', true],
                            'file_ktp_ortu' => ['KTP Orang Tua', true],
                            'file_akta' => ['Akta Kelahiran', true],
                            'file_ijazah' => ['Ijazah', false],
                            'file_sertifikat' => ['Sertifikat', false]
                        ];
                        $completed = 0;
                        $requiredComplete = 0;
                        $requiredTotal = 0;
                        foreach ($documents as $field => $info) {
                            if (!empty($data[$field]))
                                $completed++;
                            if ($info[1]) {
                                $requiredTotal++;
                                if (!empty($data[$field]))
                                    $requiredComplete++;
                            }
                        }
                        $percentage = $requiredTotal > 0 ? round(($requiredComplete / $requiredTotal) * 100) : 0;
                        ?>
                        <div class="mb-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">Wajib: <?= $requiredComplete ?>/<?= $requiredTotal ?></span>
                                <span
                                    class="font-semibold <?= $percentage >= 100 ? 'text-green-600' : 'text-primary' ?>"><?= $percentage ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all <?= $percentage >= 100 ? 'bg-green-500' : 'bg-primary' ?>"
                                    style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <?php foreach ($documents as $field => $info):
                                $hasFile = !empty($data[$field]);
                                ?>
                                <div class="flex items-center gap-2 text-xs">
                                    <?php if ($hasFile): ?>
                                        <i class="fas fa-check-circle text-green-500"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle <?= $info[1] ? 'text-red-500' : 'text-gray-300' ?>"></i>
                                    <?php endif; ?>
                                    <span
                                        class="<?= $hasFile ? 'text-gray-700' : ($info[1] ? 'text-gray-500' : 'text-gray-400') ?>">
                                        <?= $info[0] ?>     <?= $info[1] ? '' : ' (opsional)' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1 min-w-0">
                    <!-- Tabs -->
                    <div class="bg-white rounded-2xl shadow-sm mb-6 overflow-x-auto">
                        <div class="flex border-b border-gray-100 min-w-max">
                            <button onclick="showTab('identitas')"
                                class="tab-btn active px-4 py-3 text-sm font-medium flex items-center gap-2"
                                data-tab="identitas">
                                <i class="fas fa-id-card"></i><span>Identitas</span>
                            </button>
                            <button onclick="showTab('keluarga')"
                                class="tab-btn px-4 py-3 text-sm font-medium flex items-center gap-2"
                                data-tab="keluarga">
                                <i class="fas fa-users"></i><span>Anggota Keluarga</span>
                            </button>
                            <button onclick="showTab('pendidikan')"
                                class="tab-btn px-4 py-3 text-sm font-medium flex items-center gap-2"
                                data-tab="pendidikan">
                                <i class="fas fa-school"></i><span>Riwayat Pendidikan</span>
                            </button>
                            <button onclick="showTab('prestasi')"
                                class="tab-btn px-4 py-3 text-sm font-medium flex items-center gap-2"
                                data-tab="prestasi">
                                <i class="fas fa-trophy"></i><span>Prestasi</span>
                            </button>
                            <button onclick="showTab('berkas')"
                                class="tab-btn px-4 py-3 text-sm font-medium flex items-center gap-2" data-tab="berkas">
                                <i class="fas fa-folder"></i><span>Berkas</span>
                            </button>
                            <button onclick="showTab('keamanan')"
                                class="tab-btn px-4 py-3 text-sm font-medium flex items-center gap-2"
                                data-tab="keamanan">
                                <i class="fas fa-lock"></i><span>Keamanan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tab Contents -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <!-- Tab: Identitas -->
                        <div id="tab-identitas" class="tab-content active">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Informasi Pribadi</h3>

                            <div class="form-row">
                                <label class="form-label required">Nama Lengkap</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="nama"
                                        value="<?= htmlspecialchars($data['nama']) ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">NIK</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="nik"
                                        value="<?= htmlspecialchars($data['nik'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                    <p class="form-hint">Nomor Induk Kependudukan Nasional (NIK)</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">No. Kartu Keluarga</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="no_kk"
                                        value="<?= htmlspecialchars($data['no_kk'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">NISN</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="nisn"
                                        value="<?= htmlspecialchars($data['nisn'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Tempat Lahir</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="tempat_lahir"
                                        value="<?= htmlspecialchars($data['tempat_lahir'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Tanggal Lahir</label>
                                <div class="form-value">
                                    <input type="date" class="form-input" data-field="tanggal_lahir"
                                        value="<?= !empty($data['tanggal_lahir']) ? date('Y-m-d', strtotime($data['tanggal_lahir'])) : '' ?>"
                                        <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Jenis Kelamin</label>
                                <div class="form-value">
                                    <div class="flex items-center gap-6 py-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="jenis_kelamin" value="L"
                                                data-field="jenis_kelamin" <?= $data['jenis_kelamin'] === 'L' ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?> class="text-primary">
                                            <span class="text-sm">Laki-laki</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="jenis_kelamin" value="P"
                                                data-field="jenis_kelamin" <?= $data['jenis_kelamin'] === 'P' ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?> class="text-primary">
                                            <span class="text-sm">Perempuan</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">No. HP Wali</label>
                                <div class="form-value">
                                    <input type="text" class="form-input bg-gray-50"
                                        value="<?= htmlspecialchars($data['no_hp_wali'] ?? '') ?>" disabled>
                                    <p class="form-hint">Nomor HP tidak dapat diubah (digunakan untuk login)</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Provinsi</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="provinsi" id="userProvinsi" <?= !$canEdit ? 'disabled' : '' ?> onchange="onUserProvinsiChange(this)">
                                        <option value="">-- Pilih Provinsi --</option>
                                        <?php if (!empty($data['provinsi'])): ?>
                                            <option value="<?= htmlspecialchars($data['provinsi']) ?>" selected>
                                                <?= htmlspecialchars($data['provinsi']) ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Kota/Kabupaten</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="kota_kab" id="userKotaKab" <?= !$canEdit ? 'disabled' : '' ?> onchange="onUserKotaChange(this)">
                                        <option value="">-- Pilih Kota/Kabupaten --</option>
                                        <?php if (!empty($data['kota_kab'])): ?>
                                            <option value="<?= htmlspecialchars($data['kota_kab']) ?>" selected>
                                                <?= htmlspecialchars($data['kota_kab']) ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Kecamatan</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="kecamatan" id="userKecamatan" <?= !$canEdit ? 'disabled' : '' ?> onchange="onUserKecamatanChange(this)">
                                        <option value="">-- Pilih Provinsi & Kota dulu --</option>
                                        <?php if (!empty($data['kecamatan'])): ?>
                                            <option value="<?= htmlspecialchars($data['kecamatan']) ?>" selected>
                                                <?= htmlspecialchars($data['kecamatan']) ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Kelurahan/Desa</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="kelurahan_desa" id="userKelurahan"
                                        <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="">-- Pilih Kecamatan dulu --</option>
                                        <?php if (!empty($data['kelurahan_desa'])): ?>
                                            <option value="<?= htmlspecialchars($data['kelurahan_desa']) ?>" selected>
                                                <?= htmlspecialchars($data['kelurahan_desa']) ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Detail Alamat</label>
                                <div class="form-value">
                                    <textarea class="form-input" data-field="alamat" rows="2" <?= !$canEdit ? 'disabled' : '' ?>><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                                    <p class="form-hint">RT/RW, Nama Jalan, Nomor Rumah, dll (opsional)</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Lembaga Dituju</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="lembaga" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="SMP NU BP" <?= $data['lembaga'] === 'SMP NU BP' ? 'selected' : '' ?>>SMP NU BP</option>
                                        <option value="MA ALHIKAM" <?= $data['lembaga'] === 'MA ALHIKAM' ? 'selected' : '' ?>>MA ALHIKAM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label required">Status Mukim</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="status_mukim" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="PONDOK PP MAMBAUL HUDA" <?= $data['status_mukim'] === 'PONDOK PP MAMBAUL HUDA' ? 'selected' : '' ?>>Pondok PP Mambaul Huda</option>
                                        <option value="PONDOK SELAIN PP MAMBAUL HUDA" <?= $data['status_mukim'] === 'PONDOK SELAIN PP MAMBAUL HUDA' ? 'selected' : '' ?>>Pondok Selain PP Mambaul Huda
                                        </option>
                                        <option value="TIDAK PONDOK" <?= $data['status_mukim'] === 'TIDAK PONDOK' ? 'selected' : '' ?>>Tidak Pondok</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Keluarga -->
                        <div id="tab-keluarga" class="tab-content">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Data Orang Tua</h3>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Data Ayah -->
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">Data Ayah</h4>

                                    <div class="form-row">
                                        <label class="form-label">Nama</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="nama_ayah"
                                                value="<?= htmlspecialchars($data['nama_ayah'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">NIK</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="nik_ayah"
                                                value="<?= htmlspecialchars($data['nik_ayah'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Tempat Lahir</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="tempat_lahir_ayah"
                                                value="<?= htmlspecialchars($data['tempat_lahir_ayah'] ?? '') ?>"
                                                <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <div class="form-value">
                                            <input type="date" class="form-input" data-field="tanggal_lahir_ayah"
                                                value="<?= !empty($data['tanggal_lahir_ayah']) ? date('Y-m-d', strtotime($data['tanggal_lahir_ayah'])) : '' ?>"
                                                <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Pekerjaan</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="pekerjaan_ayah"
                                                value="<?= htmlspecialchars($data['pekerjaan_ayah'] ?? '') ?>"
                                                <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Penghasilan</label>
                                        <div class="form-value">
                                            <select class="form-input" data-field="penghasilan_ayah" <?= !$canEdit ? 'disabled' : '' ?>>
                                                <option value="">Pilih Range</option>
                                                <option value="< 1 Juta" <?= ($data['penghasilan_ayah'] ?? '') === '< 1 Juta' ? 'selected' : '' ?>>
                                                    < 1 Juta</option>
                                                <option value="1-3 Juta" <?= ($data['penghasilan_ayah'] ?? '') === '1-3 Juta' ? 'selected' : '' ?>>1-3 Juta</option>
                                                <option value="3-5 Juta" <?= ($data['penghasilan_ayah'] ?? '') === '3-5 Juta' ? 'selected' : '' ?>>3-5 Juta</option>
                                                <option value="> 5 Juta" <?= ($data['penghasilan_ayah'] ?? '') === '> 5 Juta' ? 'selected' : '' ?>>> 5 Juta</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Ibu -->
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">Data Ibu</h4>

                                    <div class="form-row">
                                        <label class="form-label">Nama</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="nama_ibu"
                                                value="<?= htmlspecialchars($data['nama_ibu'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">NIK</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="nik_ibu"
                                                value="<?= htmlspecialchars($data['nik_ibu'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Tempat Lahir</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="tempat_lahir_ibu"
                                                value="<?= htmlspecialchars($data['tempat_lahir_ibu'] ?? '') ?>"
                                                <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <div class="form-value">
                                            <input type="date" class="form-input" data-field="tanggal_lahir_ibu"
                                                value="<?= !empty($data['tanggal_lahir_ibu']) ? date('Y-m-d', strtotime($data['tanggal_lahir_ibu'])) : '' ?>"
                                                <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Pekerjaan</label>
                                        <div class="form-value">
                                            <input type="text" class="form-input" data-field="pekerjaan_ibu"
                                                value="<?= htmlspecialchars($data['pekerjaan_ibu'] ?? '') ?>"
                                                <?= !$canEdit ? 'disabled' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <label class="form-label">Penghasilan</label>
                                        <div class="form-value">
                                            <select class="form-input" data-field="penghasilan_ibu" <?= !$canEdit ? 'disabled' : '' ?>>
                                                <option value="">Pilih Range</option>
                                                <option value="< 1 Juta" <?= ($data['penghasilan_ibu'] ?? '') === '< 1 Juta' ? 'selected' : '' ?>>
                                                    < 1 Juta</option>
                                                <option value="1-3 Juta" <?= ($data['penghasilan_ibu'] ?? '') === '1-3 Juta' ? 'selected' : '' ?>>1-3 Juta</option>
                                                <option value="3-5 Juta" <?= ($data['penghasilan_ibu'] ?? '') === '3-5 Juta' ? 'selected' : '' ?>>3-5 Juta</option>
                                                <option value="> 5 Juta" <?= ($data['penghasilan_ibu'] ?? '') === '> 5 Juta' ? 'selected' : '' ?>>> 5 Juta</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6">

                            <div class="form-row">
                                <label class="form-label">Jumlah Saudara</label>
                                <div class="form-value">
                                    <input type="number" class="form-input w-32" data-field="jumlah_saudara"
                                        value="<?= $data['jumlah_saudara'] ?? '0' ?>" min="0" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Pendidikan -->
                        <div id="tab-pendidikan" class="tab-content">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Riwayat Pendidikan</h3>

                            <div class="form-row">
                                <label class="form-label">Asal Sekolah</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="asal_sekolah"
                                        value="<?= htmlspecialchars($data['asal_sekolah'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>



                            <div class="form-row">
                                <label class="form-label">No. PIP/PKH</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="pip_pkh"
                                        value="<?= htmlspecialchars($data['pip_pkh'] ?? '') ?>" <?= !$canEdit ? 'disabled' : '' ?>>
                                    <p class="form-hint">Isi jika memiliki kartu PIP atau PKH</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Sumber Informasi</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="sumber_info" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="">Pilih</option>
                                        <option value="ALUMNI" <?= ($data['sumber_info'] ?? '') === 'ALUMNI' ? 'selected' : '' ?>>Alumni</option>
                                        <option value="KELUARGA" <?= ($data['sumber_info'] ?? '') === 'KELUARGA' ? 'selected' : '' ?>>Keluarga</option>
                                        <option value="TEMAN" <?= ($data['sumber_info'] ?? '') === 'TEMAN' ? 'selected' : '' ?>>Teman</option>
                                        <option value="SOSIAL MEDIA" <?= ($data['sumber_info'] ?? '') === 'SOSIAL MEDIA' ? 'selected' : '' ?>>Sosial Media</option>
                                        <option value="BROSUR" <?= ($data['sumber_info'] ?? '') === 'BROSUR' ? 'selected' : '' ?>>Brosur</option>
                                        <option value="LAINNYA" <?= ($data['sumber_info'] ?? '') === 'LAINNYA' ? 'selected' : '' ?>>Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Prestasi -->
                        <div id="tab-prestasi" class="tab-content">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Prestasi</h3>

                            <div class="form-row">
                                <label class="form-label">Nama Prestasi</label>
                                <div class="form-value">
                                    <input type="text" class="form-input" data-field="prestasi"
                                        value="<?= htmlspecialchars($data['prestasi'] ?? '') ?>"
                                        placeholder="Contoh: Lomba MTQ, Olimpiade Matematika" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Tingkat</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="tingkat_prestasi" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="">Pilih Tingkat</option>
                                        <option value="KABUPATEN" <?= ($data['tingkat_prestasi'] ?? '') === 'KABUPATEN' ? 'selected' : '' ?>>Kabupaten</option>
                                        <option value="PROVINSI" <?= ($data['tingkat_prestasi'] ?? '') === 'PROVINSI' ? 'selected' : '' ?>>Provinsi</option>
                                        <option value="NASIONAL" <?= ($data['tingkat_prestasi'] ?? '') === 'NASIONAL' ? 'selected' : '' ?>>Nasional</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Juara</label>
                                <div class="form-value">
                                    <select class="form-input" data-field="juara" <?= !$canEdit ? 'disabled' : '' ?>>
                                        <option value="">Pilih Juara</option>
                                        <option value="1" <?= ($data['juara'] ?? '') === '1' ? 'selected' : '' ?>>Juara 1
                                        </option>
                                        <option value="2" <?= ($data['juara'] ?? '') === '2' ? 'selected' : '' ?>>Juara 2
                                        </option>
                                        <option value="3" <?= ($data['juara'] ?? '') === '3' ? 'selected' : '' ?>>Juara 3
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Sertifikat</label>
                                <div class="form-value">
                                    <div id="upload-file_sertifikat" class="file-upload-area"
                                        data-field="file_sertifikat" data-accept=".pdf,.jpg,.jpeg,.png">
                                        <?php if (!empty($data['file_sertifikat'])): ?>
                                            <div class="flex items-center justify-between">
                                                <a href="../uploads/sertifikat/<?= htmlspecialchars($data['file_sertifikat']) ?>"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-2 text-primary hover:underline">
                                                    <i class="fas fa-file-pdf"></i> Lihat Sertifikat
                                                </a>
                                                <?php if ($canEdit): ?>
                                                    <span class="text-gray-400 text-xs">Klik untuk ganti</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-gray-400">
                                                <i class="fas fa-cloud-upload-alt text-2xl mb-2"></i>
                                                <p class="text-sm">
                                                    <?= $canEdit ? 'Klik atau drag file PDF/Gambar' : 'Belum upload' ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="input-file_sertifikat" class="hidden"
                                        accept=".pdf,.jpg,.jpeg,.png" <?= !$canEdit ? 'disabled' : '' ?>>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Berkas -->
                        <div id="tab-berkas" class="tab-content">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Dokumen</h3>

                            <?php
                            $docs = [
                                'file_kk' => ['Kartu Keluarga', 'fa-id-card'],
                                'file_ktp_ortu' => ['KTP Orang Tua', 'fa-address-card'],
                                'file_akta' => ['Akta Kelahiran', 'fa-file-alt'],
                                'file_ijazah' => ['Ijazah', 'fa-graduation-cap']
                            ];
                            foreach ($docs as $key => $info):
                                $hasFile = !empty($data[$key]);
                                ?>
                                <div class="form-row">
                                    <label class="form-label"><?= $info[0] ?></label>
                                    <div class="form-value">
                                        <div id="upload-<?= $key ?>" class="file-upload-area" data-field="<?= $key ?>"
                                            data-accept=".pdf,.jpg,.jpeg,.png">
                                            <?php if ($hasFile): ?>
                                                <div class="flex items-center justify-between">
                                                    <a href="../uploads/dokumen/<?= htmlspecialchars($data[$key]) ?>"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-2 text-primary hover:underline">
                                                        <i class="fas <?= $info[1] ?>"></i> Lihat Dokumen
                                                    </a>
                                                    <?php if ($canEdit): ?>
                                                        <span class="text-gray-400 text-xs">Klik untuk ganti</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-gray-400">
                                                    <i class="fas fa-cloud-upload-alt text-2xl mb-2"></i>
                                                    <p class="text-sm">
                                                        <?= $canEdit ? 'Klik atau drag file PDF/Gambar (maks 2MB)' : 'Belum upload' ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" id="input-<?= $key ?>" class="hidden"
                                            accept=".pdf,.jpg,.jpeg,.png" <?= !$canEdit ? 'disabled' : '' ?>>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <p class="text-sm text-gray-500 mt-4">
                                <i class="fas fa-info-circle mr-1"></i>
                                File PDF akan otomatis dikompresi jika ukuran lebih dari 500KB.
                            </p>
                        </div>

                        <!-- Tab: Keamanan -->
                        <div id="tab-keamanan" class="tab-content">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Keamanan Akun</h3>

                            <div class="form-row">
                                <label class="form-label">Username</label>
                                <div class="form-value">
                                    <input type="text" class="form-input bg-gray-50"
                                        value="<?= htmlspecialchars($data['no_hp_wali'] ?? '') ?>" disabled>
                                    <p class="form-hint">Gunakan No. HP untuk login</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Status Akun</label>
                                <div class="form-value">
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium <?= $statusInfo[1] ?>">
                                        <i class="fas <?= $statusInfo[2] ?>"></i><?= $statusInfo[0] ?>
                                    </span>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Terdaftar</label>
                                <div class="form-value">
                                    <input type="text" class="form-input bg-gray-50"
                                        value="<?= date('d F Y, H:i', strtotime($data['created_at'])) ?>" disabled>
                                </div>
                            </div>

                            <?php if ($canEdit): ?>
                                <hr class="my-6">
                                <h4 class="font-semibold text-gray-800 mb-4">Ubah Password</h4>
                                <div id="password-change-form">
                                    <div class="form-row">
                                        <label class="form-label required">Password Lama</label>
                                        <div class="form-value">
                                            <div class="relative">
                                                <input type="password" id="old_password" class="form-input pr-10"
                                                    placeholder="Masukkan password lama">
                                                <button type="button" onclick="togglePwVisibility('old_password', this)"
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label required">Password Baru</label>
                                        <div class="form-value">
                                            <div class="relative">
                                                <input type="password" id="new_password" class="form-input pr-10"
                                                    placeholder="Minimal 6 karakter">
                                                <button type="button" onclick="togglePwVisibility('new_password', this)"
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label required">Konfirmasi Password</label>
                                        <div class="form-value">
                                            <div class="relative">
                                                <input type="password" id="confirm_password" class="form-input pr-10"
                                                    placeholder="Ketik ulang password baru">
                                                <button type="button" onclick="togglePwVisibility('confirm_password', this)"
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <label class="form-label"></label>
                                        <div class="form-value">
                                            <button type="button" onclick="changePassword()"
                                                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition">
                                                <i class="fas fa-key mr-2"></i>Ubah Password
                                            </button>
                                            <p id="password-message" class="form-hint mt-2"></p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-sm text-green-700">
                                        <i class="fas fa-lock mr-2"></i>
                                        Akun Anda sudah terverifikasi. Untuk mengubah data, silakan hubungi admin.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-50 border-t border-gray-200 mt-8">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">SPMB Terpadu</h3>
                                <p class="text-sm text-gray-500">Yayasan Almukarromah Pajomblangan</p>
                            </div>
                        </div>
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
                    <div class="md:text-right">
                        <h4 class="text-sm font-bold text-gray-700 mb-2">YAYASAN AL MUKARROMAH</h4>
                        <p class="text-sm text-gray-500 mb-4">
                            Jl. Pajomblangan Timur, Ds. Pajomblangan, Kec.<br>
                            Kedungwuni, Kab. Pekalongan, Jawa Tengah
                        </p>
                        <div class="flex gap-2 md:justify-end">
                            <a href="#"
                                class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center"><i
                                    class="fab fa-facebook-f text-sm"></i></a>
                            <a href="#"
                                class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center"><i
                                    class="fab fa-instagram text-sm"></i></a>
                            <a href="#"
                                class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center"><i
                                    class="fab fa-tiktok text-sm"></i></a>
                            <a href="#"
                                class="w-8 h-8 bg-primary hover:bg-primary-dark text-white rounded-lg flex items-center justify-center"><i
                                    class="fab fa-youtube text-sm"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-primary text-white py-3">
                <div class="max-w-7xl mx-auto px-4 text-center">
                    <p class="text-xs">© Copyright 2025 Yayasan Al Mukarromah</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- PDF Compression Script -->
    <script src="../js/pdf-compress.js"></script>

    <script>
        const canEdit = <?= $canEdit ? 'true' : 'false' ?>;

        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        }

        // Auto-save fields on blur
        let saveTimeout;
        document.querySelectorAll('[data-field]').forEach(input => {
            if (input.classList.contains('file-upload-area')) return; // Skip file uploads

            const saveField = async (el) => {
                const field = el.dataset.field;
                const value = el.type === 'radio' ? (el.checked ? el.value : null) : el.value;

                if (value === null) return;

                el.classList.add('saving');
                el.classList.remove('saved');

                try {
                    const formData = new FormData();
                    formData.append('action', 'update_field');
                    formData.append('field', field);
                    formData.append('value', value);

                    const response = await fetch('dashboard.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        el.classList.remove('saving');
                        el.classList.add('saved');
                        setTimeout(() => el.classList.remove('saved'), 2000);
                    } else {
                        el.classList.remove('saving');
                        alert('Gagal menyimpan: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    el.classList.remove('saving');
                    console.error('Save error:', error);
                }
            };

            if (input.type === 'radio') {
                input.addEventListener('change', () => saveField(input));
            } else {
                input.addEventListener('blur', () => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => saveField(input), 300);
                });

                input.addEventListener('change', () => {
                    if (input.tagName === 'SELECT') {
                        saveField(input);
                    }
                });
            }
        });

        // File upload handling
        if (canEdit) {
            document.querySelectorAll('.file-upload-area').forEach(area => {
                const field = area.dataset.field;
                const input = document.getElementById('input-' + field);
                const accept = area.dataset.accept || '.pdf';

                // Click to upload
                area.addEventListener('click', () => input.click());

                // Drag & drop
                area.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    area.classList.add('dragging');
                });

                area.addEventListener('dragleave', () => {
                    area.classList.remove('dragging');
                });

                area.addEventListener('drop', (e) => {
                    e.preventDefault();
                    area.classList.remove('dragging');
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        handleFileUpload(field, files[0], area);
                    }
                });

                // File input change
                input.addEventListener('change', () => {
                    if (input.files.length > 0) {
                        handleFileUpload(field, input.files[0], area);
                    }
                });
            });
        }

        async function handleFileUpload(field, file, area) {
            // Validate file type
            const ext = file.name.split('.').pop().toLowerCase();
            const allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];

            if (!allowedExt.includes(ext)) {
                alert('Format file tidak valid! Gunakan: ' + allowedExt.join(', '));
                return;
            }

            // Validate size
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB!');
                return;
            }

            area.classList.add('uploading');
            area.innerHTML = '<div class="text-yellow-600"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p class="text-sm">Mengupload...</p></div>';

            // Compress PDF if needed
            let fileToUpload = file;
            if (ext === 'pdf' && file.size > 500 * 1024 && window.PDFCompressor) {
                try {
                    area.innerHTML = '<div class="text-yellow-600"><i class="fas fa-compress-arrows-alt fa-spin text-2xl mb-2"></i><p class="text-sm">Mengompresi PDF...</p></div>';
                    fileToUpload = await window.PDFCompressor.compress(file);
                } catch (e) {
                    console.warn('Compression failed, using original:', e);
                }
            }

            const formData = new FormData();
            formData.append('action', 'upload_file');
            formData.append('field', field);
            formData.append('file', fileToUpload);

            try {
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    area.classList.remove('uploading');
                    area.classList.add('success');

                    const viewUrl = result.url;
                    const icon = field === 'file_sertifikat' ? 'fa-file-pdf' : getIconForField(field);

                    area.innerHTML = `
                        <div class="flex items-center justify-between">
                            <a href="${viewUrl}" target="_blank" class="inline-flex items-center gap-2 text-primary hover:underline">
                                <i class="fas ${icon}"></i> Lihat Dokumen
                            </a>
                            <span class="text-green-500 text-xs"><i class="fas fa-check"></i> Tersimpan</span>
                        </div>
                    `;

                    setTimeout(() => {
                        area.classList.remove('success');
                        area.querySelector('.text-green-500').textContent = 'Klik untuk ganti';
                        area.querySelector('.text-green-500').className = 'text-gray-400 text-xs';
                    }, 2000);
                } else {
                    area.classList.remove('uploading');
                    area.innerHTML = `<div class="text-red-500"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p class="text-sm">${result.message}</p></div>`;
                }
            } catch (error) {
                area.classList.remove('uploading');
                area.innerHTML = '<div class="text-red-500"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p class="text-sm">Gagal mengupload</p></div>';
                console.error('Upload error:', error);
            }
        }

        function getIconForField(field) {
            const icons = {
                'file_kk': 'fa-id-card',
                'file_ktp_ortu': 'fa-address-card',
                'file_akta': 'fa-file-alt',
                'file_ijazah': 'fa-graduation-cap',
                'file_sertifikat': 'fa-file-pdf'
            };
            return icons[field] || 'fa-file';
        }

        // Password change function
        async function changePassword() {
            const oldPassword = document.getElementById('old_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const messageEl = document.getElementById('password-message');

            // Client-side validation
            if (!oldPassword || !newPassword || !confirmPassword) {
                messageEl.innerHTML = '<span class="text-red-500">Semua field harus diisi</span>';
                return;
            }

            if (newPassword !== confirmPassword) {
                messageEl.innerHTML = '<span class="text-red-500">Password baru tidak cocok</span>';
                return;
            }

            if (newPassword.length < 6) {
                messageEl.innerHTML = '<span class="text-red-500">Password minimal 6 karakter</span>';
                return;
            }

            messageEl.innerHTML = '<span class="text-yellow-600"><i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...</span>';

            try {
                const formData = new FormData();
                formData.append('action', 'change_password');
                formData.append('old_password', oldPassword);
                formData.append('new_password', newPassword);
                formData.append('confirm_password', confirmPassword);

                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageEl.innerHTML = '<span class="text-green-600"><i class="fas fa-check mr-1"></i>' + result.message + '</span>';
                    // Clear form
                    document.getElementById('old_password').value = '';
                    document.getElementById('new_password').value = '';
                    document.getElementById('confirm_password').value = '';
                } else {
                    messageEl.innerHTML = '<span class="text-red-500"><i class="fas fa-times mr-1"></i>' + result.message + '</span>';
                }
            } catch (error) {
                messageEl.innerHTML = '<span class="text-red-500">Terjadi kesalahan</span>';
                console.error('Password change error:', error);
            }
        }
    </script>

    <!-- Address Autocomplete Styles -->
    <style>
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
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        .autocomplete-dropdown .item:hover {
            background-color: #fef3e2;
        }

        .autocomplete-dropdown .no-result {
            padding: 0.5rem 0.75rem;
            color: #9ca3af;
            font-size: 0.875rem;
        }
    </style>

    <!-- Address Dropdown Script -->
    <script>
        let userAllProvinsi = [];
        let userAllKota = [];
        let userKecamatanData = [];
        let userKelurahanData = [];

        async function loadUserWilayah() {
            try {
                const provResponse = await fetch('../api/wilayah.php?type=provinsi');
                userAllProvinsi = await provResponse.json();

                // Populate provinsi dropdown
                const provSelect = document.getElementById('userProvinsi');
                const currentProv = provSelect.value;
                provSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                userAllProvinsi.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name;
                    opt.dataset.id = p.id;
                    opt.textContent = p.name;
                    if (p.name === currentProv) opt.selected = true;
                    provSelect.appendChild(opt);
                });

                // Fetch all cities in PARALLEL
                const kotaPromises = userAllProvinsi.map(prov =>
                    fetch('../api/wilayah.php?type=kota&id=' + prov.id)
                        .then(r => r.json())
                        .then(kotaList => {
                            if (Array.isArray(kotaList)) {
                                kotaList.forEach(kota => {
                                    kota.provinsi_id = prov.id;
                                    kota.provinsi_name = prov.name;
                                    userAllKota.push(kota);
                                });
                            }
                        })
                );
                await Promise.all(kotaPromises);

                // Populate kota dropdown
                populateUserKotaDropdown();

                console.log('Loaded', userAllProvinsi.length, 'provinces and', userAllKota.length, 'cities');

                // Pre-load kecamatan if kota_kab already has a value
                const existingKotaKab = document.getElementById('userKotaKab')?.value;
                if (existingKotaKab) {
                    const matchingKota = userAllKota.find(k => k.name === existingKotaKab);
                    if (matchingKota) {
                        await loadUserKecamatan(matchingKota.id, true);
                        const existingKecamatan = document.getElementById('userKecamatan')?.value;
                        if (existingKecamatan) {
                            const matchingKecamatan = userKecamatanData.find(k => k.name === existingKecamatan);
                            if (matchingKecamatan) {
                                await loadUserKelurahan(matchingKecamatan.id, true);
                            }
                        }
                    }
                }
            } catch (e) { console.error(e); }
        }

        function populateUserKotaDropdown(provinsiName = null) {
            const kotaSelect = document.getElementById('userKotaKab');
            const currentKota = kotaSelect.value;
            kotaSelect.innerHTML = '<option value="">-- Pilih Kota/Kabupaten --</option>';

            let kotaList = userAllKota;
            if (provinsiName) {
                kotaList = userAllKota.filter(k => k.provinsi_name === provinsiName);
            }

            kotaList.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.name;
                opt.dataset.id = k.id;
                opt.dataset.provinsiName = k.provinsi_name;
                opt.textContent = provinsiName ? k.name : `${k.name} (${k.provinsi_name})`;
                if (k.name === currentKota) opt.selected = true;
                kotaSelect.appendChild(opt);
            });
        }

        function onUserProvinsiChange(select) {
            const provinsiName = select.value;
            populateUserKotaDropdown(provinsiName || null);
            resetUserKecamatan();
            resetUserKelurahan();
        }

        async function onUserKotaChange(select) {
            const selectedOption = select.options[select.selectedIndex];
            const kotaId = selectedOption?.dataset?.id;
            const provinsiName = selectedOption?.dataset?.provinsiName;

            if (kotaId) {
                const provSelect = document.getElementById('userProvinsi');
                if (!provSelect.value && provinsiName) {
                    provSelect.value = provinsiName;
                }
                await loadUserKecamatan(kotaId);
            } else {
                resetUserKecamatan();
                resetUserKelurahan();
            }
        }

        async function onUserKecamatanChange(select) {
            const selectedOption = select.options[select.selectedIndex];
            const kecId = selectedOption?.dataset?.id;

            if (kecId) {
                await loadUserKelurahan(kecId);
            } else {
                resetUserKelurahan();
            }
        }

        async function loadUserKecamatan(kotaId, keepSelection = false) {
            try {
                const response = await fetch('../api/wilayah.php?type=kecamatan&id=' + kotaId);
                userKecamatanData = await response.json();

                const kecSelect = document.getElementById('userKecamatan');
                const currentKec = keepSelection ? kecSelect.value : '';
                kecSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                userKecamatanData.forEach(k => {
                    const opt = document.createElement('option');
                    opt.value = k.name;
                    opt.dataset.id = k.id;
                    opt.textContent = k.name;
                    if (k.name === currentKec) opt.selected = true;
                    kecSelect.appendChild(opt);
                });
                kecSelect.disabled = false;

                if (!keepSelection) resetUserKelurahan();
            } catch (e) { console.error(e); }
        }

        async function loadUserKelurahan(kecamatanId, keepSelection = false) {
            try {
                const response = await fetch('../api/wilayah.php?type=kelurahan&id=' + kecamatanId);
                userKelurahanData = await response.json();

                const kelSelect = document.getElementById('userKelurahan');
                const currentKel = keepSelection ? kelSelect.value : '';
                kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';
                userKelurahanData.forEach(k => {
                    const opt = document.createElement('option');
                    opt.value = k.name;
                    opt.dataset.id = k.id;
                    opt.textContent = k.name;
                    if (k.name === currentKel) opt.selected = true;
                    kelSelect.appendChild(opt);
                });
                kelSelect.disabled = false;
            } catch (e) { console.error(e); }
        }

        function resetUserKecamatan() {
            const kec = document.getElementById('userKecamatan');
            kec.innerHTML = '<option value="">-- Pilih Provinsi & Kota dulu --</option>';
            kec.disabled = true;
            userKecamatanData = [];
        }

        function resetUserKelurahan() {
            const kel = document.getElementById('userKelurahan');
            kel.innerHTML = '<option value="">-- Pilih Kecamatan dulu --</option>';
            kel.disabled = true;
            userKelurahanData = [];
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadUserWilayah();
        });
    </script>

    <!-- Image Compression -->
    <script src="../js/image-compress.js"></script>
    <script>
        // Attach image compression to file inputs in dashboard
        document.querySelectorAll('input[type="file"]').forEach(input => {
            if (input.accept && (input.accept.includes('image') || input.accept.includes('.jpg') || input.accept.includes('.png'))) {
                ImageCompressor.attachToInput(input);
            }
        });

        // Toggle password visibility
        function togglePwVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
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
    </script>
</body>

</html>