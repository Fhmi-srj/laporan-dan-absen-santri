<?php
/**
 * RFID Kiosk - Public Attendance Display
 * Halaman publik untuk absensi RFID (tanpa login)
 * Menampilkan live attendance + input RFID
 */

require_once __DIR__ . '/functions.php';

// NO LOGIN REQUIRED - Public page

$pdo = getDB();

// Get jadwal list
$jadwalList = $pdo->query("SELECT * FROM jadwal_absens ORDER BY start_time ASC")->fetchAll();

// Get today's attendance for live display
$today = date('Y-m-d');
$recentStmt = $pdo->prepare("
    SELECT a.*, s.nomor_induk, s.kelas, s.pendaftaran_id, j.name as jadwal_name, j.type as jadwal_type
    FROM attendances a
    JOIN siswa s ON a.user_id = s.id
    LEFT JOIN jadwal_absens j ON a.jadwal_id = j.id
    WHERE a.attendance_date = ?
    ORDER BY a.created_at DESC
    LIMIT 20
");
$recentStmt->execute([$today]);
$recentAttendances = $recentStmt->fetchAll();

// Enrich with SPMB data
$pendaftaranIds = array_column($recentAttendances, 'pendaftaran_id');
$pendaftaranData = getPendaftaranData($pendaftaranIds);

foreach ($recentAttendances as &$a) {
    $p = $pendaftaranData[$a['pendaftaran_id']] ?? [];
    $a['nama_lengkap'] = $p['nama'] ?? '-';
    $a['jenis_kelamin'] = $p['jenis_kelamin'] ?? '-';
}
unset($a);

// Get today's stats
$statsStmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendances WHERE attendance_date = ?");
$statsStmt->execute([$today]);
$todayTotal = $statsStmt->fetchColumn() ?: 0;

$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi RFID -
        <?= APP_NAME ?>
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            min-height: 100vh;
            color: white;
            overflow: hidden;
        }

        .kiosk-container {
            height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
            padding: 2rem;
        }

        /* Left Panel - RFID Input */
        .rfid-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .rfid-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .rfid-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }
        }

        .rfid-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .rfid-subtitle {
            opacity: 0.7;
            margin-bottom: 2rem;
        }

        .rfid-input {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            padding: 1.25rem;
            font-size: 1.5rem;
            color: white;
            text-align: center;
            width: 100%;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .rfid-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1px;
            font-size: 1rem;
        }

        .rfid-input:focus {
            outline: none;
            border-color: var(--success-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3);
        }

        .jadwal-select-wrapper {
            margin-top: 1.5rem;
            width: 100%;
        }

        .jadwal-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.25rem;
        }

        .jadwal-select:focus {
            outline: none;
            border-color: var(--success-color);
            background-color: rgba(255, 255, 255, 0.15);
        }

        .jadwal-select option {
            background: #1e293b;
            color: white;
            padding: 0.5rem;
        }

        /* Result Popup */
        .result-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: white;
            border-radius: 24px;
            padding: 3rem 4rem;
            text-align: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .result-popup.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .result-popup.success {
            color: var(--success-color);
        }

        .result-popup.error {
            color: var(--danger-color);
        }

        .result-popup.warning {
            color: var(--warning-color);
        }

        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        .result-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .result-class {
            font-size: 1.25rem;
            color: #64748b;
            margin-bottom: 1rem;
        }

        .result-status {
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Right Panel - Live Display */
        .live-panel {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .live-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .live-title {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .live-stats {
            display: flex;
            gap: 2rem;
        }

        .stat-box {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .live-clock {
            font-size: 3rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
        }

        .live-date {
            text-align: center;
            opacity: 0.7;
            margin-bottom: 1.5rem;
        }

        .live-list {
            max-height: 320px;
            /* Show ~3 items */
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1rem;
        }

        .live-list::-webkit-scrollbar {
            width: 8px;
        }

        .live-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }

        .live-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .live-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .live-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .live-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #60a5fa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .live-info {
            flex: 1;
        }

        .live-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .live-meta {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        .live-time {
            text-align: right;
        }

        .live-time-value {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .live-status {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
        }

        .status-hadir {
            background: var(--success-color);
        }

        .status-terlambat {
            background: var(--warning-color);
        }

        .status-pulang {
            background: var(--primary-color);
        }

        .status-error {
            background: var(--danger-color);
        }

        .live-item-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .live-item-error .live-avatar {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .empty-list {
            text-align: center;
            padding: 3rem;
            opacity: 0.5;
        }

        .filter-bar {
            background: rgba(255, 255, 255, 0.05);
            padding: 0.75rem;
            border-radius: 12px;
        }

        .filter-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            color: white;
            font-size: 0.9rem;
            flex: 1;
            min-width: 150px;
        }

        .filter-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .filter-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .filter-select option {
            background: #1e293b;
            color: white;
        }

        .filter-btn {
            background: rgba(239, 68, 68, 0.3);
            border: 1px solid rgba(239, 68, 68, 0.5);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background: rgba(239, 68, 68, 0.5);
        }

        .live-item.hidden {
            display: none !important;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .page-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-btn:hover:not(:disabled) {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-info {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        @media (max-width: 991px) {
            .kiosk-container {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }

            .rfid-card {
                padding: 2rem;
            }

            .rfid-icon {
                font-size: 3rem;
            }

            .rfid-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="kiosk-container">
        <!-- Left Panel - RFID Input -->
        <div class="rfid-panel">
            <div class="rfid-card">
                <div class="rfid-icon"><i class="fas fa-id-card"></i></div>
                <h1 class="rfid-title">Tempelkan Kartu</h1>
                <p class="rfid-subtitle">Arahkan kartu RFID ke reader</p>

                <form id="rfidForm" autocomplete="off">
                    <input type="text" id="rfidInput" class="rfid-input" placeholder="Menunggu kartu..." autofocus
                        autocomplete="off">
                </form>

                <div class="jadwal-select-wrapper">
                    <select id="jadwalSelect" class="jadwal-select">
                        <option value="">-- Pilih Jenis Absen --</option>
                        <?php foreach ($jadwalList as $j): ?>
                            <option value="<?= $j['id'] ?>" data-type="<?= $j['type'] ?>">
                                <?= e($j['name']) ?> (<?= date('H:i', strtotime($j['start_time'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Right Panel - Live Display -->
        <div class="live-panel">
            <div class="live-header">
                <div class="live-title"><i class="fas fa-broadcast-tower me-2"></i>Live Attendance</div>
                <div class="live-stats">
                    <div class="stat-box">
                        <div class="stat-number" id="statTotal">
                            <?= $todayTotal ?>
                        </div>
                        <div class="stat-label">Hadir Hari Ini</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">
                            <?= $totalSiswa ?>
                        </div>
                        <div class="stat-label">Total Santri</div>
                    </div>
                </div>
            </div>

            <div class="live-clock" id="liveClock">--:--:--</div>
            <div class="live-date" id="liveDate">-</div>

            <!-- Filter Bar -->
            <div class="filter-bar mb-3">
                <div class="d-flex gap-2 flex-wrap">
                    <input type="text" id="filterSearch" class="filter-input" placeholder="Cari nama..."
                        autocomplete="off">
                    <select id="filterGender" class="filter-select">
                        <option value="">Semua JK</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    <select id="filterStatus" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="pulang">Pulang</option>
                    </select>
                    <button type="button" class="filter-btn" onclick="clearFilters()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="live-list" id="liveList">
                <?php if (empty($recentAttendances)): ?>
                    <div class="empty-list">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Belum ada absensi hari ini</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentAttendances as $a): ?>
                        <div class="live-item" data-name="<?= strtolower(e($a['nama_lengkap'])) ?>"
                            data-gender="<?= e($a['jenis_kelamin']) ?>" data-status="<?= e($a['status']) ?>">
                            <div class="live-avatar">
                                <?= strtoupper(substr($a['nama_lengkap'], 0, 1)) ?>
                            </div>
                            <div class="live-info">
                                <div class="live-name">
                                    <?= e($a['nama_lengkap']) ?>
                                </div>
                                <div class="live-meta">
                                    Kelas <?= e($a['kelas']) ?> | <?= e($a['no_kartu_rfid'] ?? '-') ?>
                                </div>
                            </div>
                            <div class="live-time">
                                <div class="live-time-value">
                                    <?= date('H:i', strtotime($a['attendance_time'])) ?>
                                </div>
                                <span class="live-status status-<?= $a['status'] ?>">
                                    <?= ucfirst($a['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination Controls -->
            <div class="pagination-controls mt-3" id="paginationControls">
                <button type="button" class="page-btn" id="btnPrevPage" onclick="changePage(-1)" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="page-info">
                    <span id="pageInfo">Halaman 1</span>
                </span>
                <button type="button" class="page-btn" id="btnNextPage" onclick="changePage(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>



    <!-- Password Modal for Jadwal Switch -->
    <div class="modal fade" id="passwordModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0" style="background: #1e293b; color: white;">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fas fa-lock me-2"></i>Masukkan Sandi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small opacity-75 mb-3">Untuk mengubah jenis absensi, masukkan sandi kiosk:</p>
                    <input type="password" id="kioskPassword" class="form-control form-control-lg text-center"
                        placeholder="****" autocomplete="off" maxlength="10">
                    <div id="passwordError" class="text-danger small mt-2 d-none">Sandi salah!</div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnVerifyPassword">Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rfidInput = document.getElementById('rfidInput');
            const rfidForm = document.getElementById('rfidForm');
            const resultPopup = document.getElementById('resultPopup');
            const liveList = document.getElementById('liveList');

            const KIOSK_PASSWORD = '<?= KIOSK_PASSWORD ?>';
            let selectedJadwal = null;
            let jadwalLocked = false; // Jadwal terkunci setelah dipilih
            let pendingJadwalBtn = null; // Tombol jadwal yang pending
            let isProcessing = false;
            let todayTotal = <?= $todayTotal ?>;

            const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));

            // Clock update
            function updateClock() {
                const now = new Date();
                document.getElementById('liveClock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
                document.getElementById('liveDate').textContent = now.toLocaleDateString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });
            }
            updateClock();
            setInterval(updateClock, 1000);

            // Keep focus on input (but respect filter/dropdown interactions)
            const interactiveElements = ['filterSearch', 'filterGender', 'filterStatus', 'jadwalSelect', 'kioskPassword'];

            function focusInput() {
                if (isProcessing) return;
                if (document.getElementById('passwordModal').classList.contains('show')) return;

                // Don't steal focus from interactive elements
                const activeEl = document.activeElement;
                if (activeEl && (interactiveElements.includes(activeEl.id) || activeEl.tagName === 'SELECT')) {
                    return;
                }

                rfidInput.focus();
            }

            // Only auto-focus when clicking outside interactive areas
            document.addEventListener('click', function (e) {
                const clickedEl = e.target;
                if (clickedEl.tagName === 'SELECT' ||
                    clickedEl.tagName === 'INPUT' ||
                    clickedEl.closest('.filter-bar') ||
                    clickedEl.closest('.jadwal-select-wrapper')) {
                    return;
                }
                focusInput();
            });

            // Less aggressive interval - only focus if nothing else is focused
            setInterval(function () {
                const activeEl = document.activeElement;
                if (activeEl === document.body || activeEl === null) {
                    focusInput();
                }
            }, 2000);

            // Jadwal selection with dropdown and password protection
            const jadwalSelect = document.getElementById('jadwalSelect');
            let pendingJadwalValue = null;

            jadwalSelect.addEventListener('change', function () {
                const newValue = this.value;
                const selectedOption = this.options[this.selectedIndex];

                if (!newValue) return;

                // Jika jadwal belum terkunci, langsung pilih
                if (!jadwalLocked) {
                    selectedJadwal = {
                        id: newValue,
                        type: selectedOption.dataset.type
                    };
                    jadwalLocked = true;
                    focusInput();
                    return;
                }

                // Jika sudah terkunci dan pilih jadwal yang sama, abaikan
                if (selectedJadwal && newValue === selectedJadwal.id) {
                    focusInput();
                    return;
                }

                // Jika ingin ganti jadwal, minta password
                pendingJadwalValue = newValue;
                // Reset ke jadwal sebelumnya sementara
                this.value = selectedJadwal ? selectedJadwal.id : '';
                document.getElementById('kioskPassword').value = '';
                document.getElementById('passwordError').classList.add('d-none');
                passwordModal.show();
                setTimeout(() => document.getElementById('kioskPassword').focus(), 300);
            });

            // Password verification
            document.getElementById('btnVerifyPassword').addEventListener('click', verifyPassword);
            document.getElementById('kioskPassword').addEventListener('keyup', function (e) {
                if (e.key === 'Enter') verifyPassword();
            });

            function verifyPassword() {
                const pwd = document.getElementById('kioskPassword').value;
                if (pwd === KIOSK_PASSWORD) {
                    passwordModal.hide();
                    if (pendingJadwalValue) {
                        jadwalSelect.value = pendingJadwalValue;
                        const selectedOption = jadwalSelect.options[jadwalSelect.selectedIndex];
                        selectedJadwal = {
                            id: pendingJadwalValue,
                            type: selectedOption.dataset.type
                        };
                        pendingJadwalValue = null;
                        focusInput();
                    }
                } else {
                    document.getElementById('passwordError').classList.remove('d-none');
                    document.getElementById('kioskPassword').value = '';
                    document.getElementById('kioskPassword').focus();
                }
            }

            // Auto-select first jadwal
            if (jadwalSelect.options.length > 1) {
                jadwalSelect.selectedIndex = 1; // Select first actual option
                const firstOption = jadwalSelect.options[1];
                selectedJadwal = { id: firstOption.value, type: firstOption.dataset.type };
                jadwalLocked = true;
            }

            // Handle RFID input
            rfidForm.addEventListener('submit', function (e) {
                e.preventDefault();
                processRFID();
            });

            // Auto-submit when input looks complete
            rfidInput.addEventListener('input', function () {
                const val = this.value.trim();
                if (val.length >= 10 && /^\d+$/.test(val)) {
                    setTimeout(() => {
                        if (rfidInput.value.length >= 10) processRFID();
                    }, 100);
                }
            });

            function processRFID() {
                const rfidNumber = rfidInput.value.trim();
                if (!rfidNumber || !selectedJadwal) return;

                isProcessing = true;
                rfidInput.disabled = true;

                const formData = new FormData();
                formData.append('no_kartu_rfid', rfidNumber);
                formData.append('jadwal_id', selectedJadwal.id);

                fetch('api/attendance.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        // Only add successful attendance to live list
                        if (data.success) {
                            addToLiveList(data);
                            todayTotal++;
                            document.getElementById('statTotal').textContent = todayTotal;
                            playSound('success');
                        }
                        // Silently ignore errors (duplicate attendance, etc.)
                    })
                    .catch(err => {
                        // Silently ignore network errors
                        console.error('Attendance error:', err);
                    })
                    .finally(() => {
                        rfidInput.value = '';
                        rfidInput.disabled = false;
                        isProcessing = false;
                        focusInput();
                    });
            }

            function addToLiveList(data) {
                const emptyDiv = liveList.querySelector('.empty-list');
                if (emptyDiv) emptyDiv.remove();

                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
                const initial = (data.siswa_name || '?').charAt(0).toUpperCase();
                const name = data.siswa_name || 'Unknown';
                const kelas = data.siswa_kelas || '-';
                const rfidNo = data.siswa_rfid || '-';

                // Determine status and styling
                let statusClass, statusText, itemClass;
                const statusVal = data.status || 'error';
                const genderVal = data.siswa_gender || '';

                if (data.success) {
                    statusClass = data.status === 'terlambat' ? 'status-terlambat' :
                        (data.status === 'pulang' ? 'status-pulang' : 'status-hadir');
                    statusText = data.status;
                    itemClass = '';
                } else {
                    statusClass = 'status-error';
                    statusText = 'GAGAL';
                    itemClass = 'live-item-error';
                }

                const itemHtml = `
            <div class="live-item ${itemClass}" data-name="${name.toLowerCase()}" data-gender="${genderVal}" data-status="${statusVal}">
                <div class="live-avatar">${initial}</div>
                <div class="live-info">
                    <div class="live-name">${name}</div>
                    <div class="live-meta">Kelas ${kelas} | ${rfidNo}</div>
                </div>
                <div class="live-time">
                    <div class="live-time-value">${timeStr}</div>
                    <span class="live-status ${statusClass}">${statusText}</span>
                </div>
            </div>
        `;

                liveList.insertAdjacentHTML('afterbegin', itemHtml);

                // Keep only 50 items
                while (liveList.children.length > 50) {
                    liveList.removeChild(liveList.lastChild);
                }

                // Apply filters and pagination to updated list
                applyFiltersAndPagination();
            }

            // Filter functionality
            const filterSearch = document.getElementById('filterSearch');
            const filterGender = document.getElementById('filterGender');
            const filterStatus = document.getElementById('filterStatus');

            // Pagination
            const ITEMS_PER_PAGE = 10;
            let currentPage = 1;

            filterSearch.addEventListener('input', () => { currentPage = 1; applyFiltersAndPagination(); });
            filterGender.addEventListener('change', () => { currentPage = 1; applyFiltersAndPagination(); });
            filterStatus.addEventListener('change', () => { currentPage = 1; applyFiltersAndPagination(); });

            function applyFiltersAndPagination() {
                const searchVal = filterSearch.value.toLowerCase().trim();
                const genderVal = filterGender.value;
                const statusVal = filterStatus.value;

                const allItems = document.querySelectorAll('.live-item');
                let filteredItems = [];

                allItems.forEach(item => {
                    const name = item.dataset.name || '';
                    const gender = item.dataset.gender || '';
                    const status = item.dataset.status || '';

                    let matchesFilter = true;
                    if (searchVal && !name.includes(searchVal)) matchesFilter = false;
                    if (genderVal && gender !== genderVal) matchesFilter = false;
                    if (statusVal && status !== statusVal) matchesFilter = false;

                    if (matchesFilter) {
                        filteredItems.push(item);
                    }
                    item.classList.add('hidden');
                });

                // Calculate pagination
                const totalItems = filteredItems.length;
                const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
                if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
                const endIndex = startIndex + ITEMS_PER_PAGE;

                // Show only items for current page
                filteredItems.slice(startIndex, endIndex).forEach(item => {
                    item.classList.remove('hidden');
                });

                // Update pagination UI
                document.getElementById('pageInfo').textContent = totalPages > 0
                    ? `Halaman ${currentPage} dari ${totalPages}`
                    : 'Tidak ada data';
                document.getElementById('btnPrevPage').disabled = currentPage <= 1;
                document.getElementById('btnNextPage').disabled = currentPage >= totalPages;
            }

            function changePage(delta) {
                currentPage += delta;
                applyFiltersAndPagination();
                focusInput();
            }
            window.changePage = changePage;

            function clearFilters() {
                filterSearch.value = '';
                filterGender.value = '';
                filterStatus.value = '';
                currentPage = 1;
                applyFiltersAndPagination();
                focusInput();
            }

            // Make clearFilters available globally
            window.clearFilters = clearFilters;

            // Initial pagination
            applyFiltersAndPagination();

            function playSound(type) {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();
                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);

                    if (type === 'success') {
                        oscillator.frequency.value = 800;
                        gainNode.gain.value = 0.3;
                    } else {
                        oscillator.frequency.value = 300;
                        gainNode.gain.value = 0.3;
                    }

                    oscillator.start();
                    setTimeout(() => oscillator.stop(), 150);
                } catch (e) { }
            }

            // Auto-refresh live list every 30 seconds
            setInterval(() => {
                fetch('api/live-attendance.php?jadwal_id=' + (selectedJadwal?.id || ''))
                    .then(res => res.json())
                    .then(data => {
                        if (data.today_count !== undefined) {
                            document.getElementById('statTotal').textContent = data.today_count;
                            todayTotal = data.today_count;
                        }
                    })
                    .catch(() => { });
            }, 30000);
        });
    </script>

</body>

</html>