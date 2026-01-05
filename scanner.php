<?php
/**
 * QR Code Scanner Page
 * Halaman untuk guru scan QR siswa untuk absensi
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDB();
$pageTitle = 'Scanner Absensi';

// Get jadwal list
$jadwalList = $pdo->query("SELECT * FROM jadwal_absens ORDER BY start_time ASC")->fetchAll();

$extraCss = '';
$extraStyles = <<<'CSS'
<style>
    .scanner-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .scanner-box {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    #reader {
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
    }
    #reader video {
        border-radius: 12px;
    }
    .jadwal-select {
        font-size: 1.1rem;
        padding: 1rem;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
    }
    .jadwal-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(134, 89, 241, 0.1);
    }
    .result-box {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
    }
    .result-box.error {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    .result-box.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    .scan-instruction {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
    }
</style>
CSS;
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<div class="main-content">
    <div class="scanner-container">
        <h4 class="fw-bold text-center mb-4"><i class="fas fa-qrcode me-2"></i>Scanner Absensi</h4>

        <div class="scanner-box">
            <!-- Jadwal Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">Pilih Jadwal Absensi</label>
                <select id="jadwal_id" class="form-select jadwal-select">
                    <option value="">-- Pilih Jadwal --</option>
                    <?php foreach ($jadwalList as $j): ?>
                        <option value="<?= $j['id'] ?>" data-type="<?= $j['type'] ?>">
                            <?= e($j['name']) ?> (
                            <?= substr($j['start_time'], 0, 5) ?> -
                            <?= substr($j['end_time'], 0, 5) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Scanner Area -->
            <div id="scanner-area" class="d-none">
                <div class="scan-instruction mb-3">
                    <i class="fas fa-camera fa-2x text-primary mb-2"></i>
                    <p class="mb-0 small text-muted">Arahkan kamera ke QR Code siswa</p>
                </div>

                <div id="reader" class="mb-3"></div>

                <button type="button" id="btn-stop" class="btn btn-danger w-100">
                    <i class="fas fa-stop me-1"></i> Tutup Kamera
                </button>
            </div>

            <!-- Start Button -->
            <div id="start-area">
                <button type="button" id="btn-start" class="btn btn-primary btn-lg w-100" disabled>
                    <i class="fas fa-play me-2"></i> Mulai Scan
                </button>
                <small class="text-muted d-block text-center mt-2">Pilih jadwal terlebih dahulu</small>
            </div>

            <!-- Result Area -->
            <div id="result-area" class="mt-4 d-none">
                <div id="result-box" class="result-box">
                    <i class="fas fa-check-circle fa-3x mb-2"></i>
                    <h5 id="result-name" class="fw-bold mb-1">Nama Siswa</h5>
                    <p id="result-message" class="mb-0">Berhasil absen</p>
                </div>
            </div>

            <!-- Recent Scans -->
            <div class="mt-4">
                <h6 class="fw-bold text-muted mb-3"><i class="fas fa-history me-1"></i> Scan Terakhir</h6>
                <div id="recent-scans" class="list-group">
                    <div class="text-center text-muted small py-3">Belum ada scan</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let scanner = null;
        let isScanning = false;
        const recentScans = [];

        const jadwalSelect = document.getElementById('jadwal_id');
        const btnStart = document.getElementById('btn-start');
        const btnStop = document.getElementById('btn-stop');
        const scannerArea = document.getElementById('scanner-area');
        const startArea = document.getElementById('start-area');
        const resultArea = document.getElementById('result-area');
        const resultBox = document.getElementById('result-box');

        // Enable start button when jadwal selected
        jadwalSelect.addEventListener('change', function () {
            btnStart.disabled = !this.value;
        });

        // Start scanning
        btnStart.addEventListener('click', function () {
            if (!jadwalSelect.value) {
                Swal.fire('Pilih Jadwal', 'Silakan pilih jadwal absensi terlebih dahulu', 'warning');
                return;
            }

            startArea.classList.add('d-none');
            scannerArea.classList.remove('d-none');

            scanner = new Html5Qrcode("reader");
            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess,
                onScanFailure
            ).catch(function (err) {
                Swal.fire('Error Kamera', 'Tidak dapat mengakses kamera: ' + err, 'error');
                stopScanner();
            });
            isScanning = true;
        });

        // Stop scanning
        btnStop.addEventListener('click', stopScanner);

        function stopScanner() {
            if (scanner && isScanning) {
                scanner.stop().catch(() => { });
            }
            isScanning = false;
            scannerArea.classList.add('d-none');
            startArea.classList.remove('d-none');
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Pause scanner briefly
            if (scanner && isScanning) {
                scanner.pause();
            }

            // Process attendance
            processAttendance(decodedText);
        }

        function onScanFailure(error) {
            // Ignore - normal when no QR in frame
        }

        function processAttendance(nomorInduk) {
            const jadwalId = jadwalSelect.value;

            // Get current location
            if (!navigator.geolocation) {
                showResult(false, 'Error', 'Geolocation tidak didukung browser ini');
                resumeScanner();
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const data = new FormData();
                    data.append('nomor_induk', nomorInduk);
                    data.append('jadwal_id', jadwalId);
                    data.append('latitude', position.coords.latitude);
                    data.append('longitude', position.coords.longitude);

                    fetch('api/attendance.php', {
                        method: 'POST',
                        body: data
                    })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                showResult(true, result.siswa_name || 'Siswa', result.message);
                                addRecentScan(result.siswa_name || nomorInduk, true, result.message);
                            } else {
                                showResult(false, 'Gagal', result.message);
                                addRecentScan(nomorInduk, false, result.message);
                            }
                            setTimeout(resumeScanner, 2000);
                        })
                        .catch(error => {
                            showResult(false, 'Error', 'Terjadi kesalahan: ' + error.message);
                            setTimeout(resumeScanner, 2000);
                        });
                },
                function (error) {
                    showResult(false, 'Error Lokasi', 'Tidak dapat mengambil lokasi: ' + error.message);
                    setTimeout(resumeScanner, 2000);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        function showResult(success, name, message) {
            resultArea.classList.remove('d-none');
            resultBox.className = 'result-box' + (success ? '' : ' error');
            document.getElementById('result-name').textContent = name;
            document.getElementById('result-message').textContent = message;
        }

        function addRecentScan(name, success, message) {
            recentScans.unshift({ name, success, message, time: new Date().toLocaleTimeString('id-ID', { hour12: false }) });
            if (recentScans.length > 5) recentScans.pop();
            renderRecentScans();
        }

        function renderRecentScans() {
            const container = document.getElementById('recent-scans');
            if (recentScans.length === 0) {
                container.innerHTML = '<div class="text-center text-muted small py-3">Belum ada scan</div>';
                return;
            }
            container.innerHTML = recentScans.map(s => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-${s.success ? 'check text-success' : 'times text-danger'} me-2"></i>
                    <strong>${s.name}</strong>
                    <small class="text-muted d-block">${s.message}</small>
                </div>
                <small class="text-muted">${s.time}</small>
            </div>
        `).join('');
        }

        function resumeScanner() {
            if (scanner && isScanning) {
                scanner.resume();
            }
        }
    });
</script>
<?php include __DIR__ . '/include/footer.php'; ?>