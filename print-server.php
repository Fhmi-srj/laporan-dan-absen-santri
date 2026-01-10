<?php
/**
 * Print Server - Auto Print Page
 * Halaman ini berjalan di komputer kantor untuk mencetak otomatis
 * Buka halaman ini dan biarkan berjalan
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Print Server';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🖨️ Print Server - Aktivitas Santri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #059669;
        }

        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .server-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            margin: 50px auto;
        }

        .status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            animation: pulse 2s infinite;
        }

        .status-active {
            background: #10b981;
        }

        .status-inactive {
            background: #ef4444;
            animation: none;
        }

        .status-printing {
            background: #f59e0b;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.7;
                transform: scale(1.1);
            }
        }

        .log-container {
            background: #1e293b;
            color: #94a3b8;
            border-radius: 12px;
            padding: 20px;
            height: 300px;
            overflow-y: auto;
            font-family: 'Consolas', monospace;
            font-size: 0.85rem;
        }

        .log-success {
            color: #10b981;
        }

        .log-error {
            color: #ef4444;
        }

        .log-info {
            color: #60a5fa;
        }

        .log-warning {
            color: #f59e0b;
        }

        .stat-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="server-card">
            <div class="text-center mb-4">
                <i class="fas fa-print fa-3x text-success mb-3"></i>
                <h3 class="fw-bold">Print Server</h3>
                <p class="text-muted">Halaman ini otomatis mencetak surat dari antrian</p>
            </div>

            <!-- Status -->
            <div class="d-flex align-items-center justify-content-center mb-4 p-3 rounded" style="background: #f0fdf4;">
                <span class="status-indicator status-inactive" id="statusIndicator"></span>
                <span class="fw-bold" id="statusText">Menunggu koneksi...</span>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-3">
                    <div class="stat-box">
                        <div class="stat-number" id="statPending">0</div>
                        <small class="text-muted">Antrian</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-box">
                        <div class="stat-number" id="statProcessing">0</div>
                        <small class="text-muted">Proses</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-box">
                        <div class="stat-number" id="statCompleted">0</div>
                        <small class="text-muted">Selesai</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-box">
                        <div class="stat-number text-danger" id="statFailed">0</div>
                        <small class="text-muted">Gagal</small>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-success flex-grow-1" id="btnStart" onclick="startServer()">
                    <i class="fas fa-play me-1"></i> Mulai Server
                </button>
                <button class="btn btn-danger" id="btnStop" onclick="stopServer()" disabled>
                    <i class="fas fa-stop me-1"></i> Stop
                </button>
            </div>

            <!-- Log -->
            <div class="log-container" id="logContainer">
                <div class="log-info">[System] Print Server siap. Klik "Mulai Server" untuk memulai.</div>
            </div>

            <div class="mt-3 text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Biarkan halaman ini terbuka di komputer kantor
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
    <script src="assets/js/qz-tray-config.js?v=<?= time() ?>"></script>
    <script>
        let serverRunning = false;
        let pollInterval = null;
        const POLL_INTERVAL_MS = 3000; // Check every 3 seconds

        // Add log entry
        function addLog(message, type = 'info') {
            const container = document.getElementById('logContainer');
            const time = new Date().toLocaleTimeString('id-ID');
            const div = document.createElement('div');
            div.className = `log-${type}`;
            div.innerHTML = `[${time}] ${message}`;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }

        // Update status indicator
        function setStatus(status, text) {
            const indicator = document.getElementById('statusIndicator');
            const statusText = document.getElementById('statusText');
            indicator.className = 'status-indicator status-' + status;
            statusText.textContent = text;
        }

        // Update statistics
        async function updateStats() {
            try {
                const res = await fetch('api/print-queue.php?action=stats');
                const data = await res.json();
                if (data.success) {
                    document.getElementById('statPending').textContent = data.stats.pending || 0;
                    document.getElementById('statProcessing').textContent = data.stats.processing || 0;
                    document.getElementById('statCompleted').textContent = data.stats.completed_today || 0;
                    document.getElementById('statFailed').textContent = data.stats.failed_today || 0;
                }
            } catch (e) {
                console.error('Stats error:', e);
            }
        }

        // Start print server
        async function startServer() {
            addLog('Menginisialisasi QZ Tray...', 'info');

            try {
                const connected = await QzPrint.init();
                if (!connected) {
                    addLog('❌ Gagal terhubung ke QZ Tray. Pastikan QZ Tray sudah berjalan!', 'error');
                    return;
                }

                addLog('✅ QZ Tray terhubung', 'success');

                serverRunning = true;
                document.getElementById('btnStart').disabled = true;
                document.getElementById('btnStop').disabled = false;
                setStatus('active', 'Server Aktif - Menunggu antrian...');

                addLog('🚀 Print Server dimulai. Polling setiap 3 detik...', 'success');

                // Start polling
                pollInterval = setInterval(pollQueue, POLL_INTERVAL_MS);
                pollQueue(); // Run immediately

            } catch (e) {
                addLog('❌ Error: ' + e.message, 'error');
            }
        }

        // Stop print server
        function stopServer() {
            serverRunning = false;
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            document.getElementById('btnStart').disabled = false;
            document.getElementById('btnStop').disabled = true;
            setStatus('inactive', 'Server Dihentikan');
            addLog('⏹️ Print Server dihentikan', 'warning');
        }

        // Poll queue for pending jobs
        async function pollQueue() {
            if (!serverRunning) return;

            try {
                const res = await fetch('api/print-queue.php?action=pending');
                const data = await res.json();

                updateStats();

                if (data.success && data.jobs && data.jobs.length > 0) {
                    for (const job of data.jobs) {
                        await processJob(job);
                    }
                }
            } catch (e) {
                console.error('Poll error:', e);
            }
        }

        // Process a single print job
        async function processJob(job) {
            addLog(`📄 Memproses job #${job.id}: ${job.job_type}`, 'info');
            setStatus('printing', 'Mencetak...');

            try {
                // Mark as processing
                await fetch(`api/print-queue.php?action=processing&id=${job.id}`);

                // Print based on job type
                if (job.job_type === 'surat_izin') {
                    await QzPrint.print(job.job_data);
                }

                // Mark as completed
                await fetch(`api/print-queue.php?action=complete&id=${job.id}`);
                addLog(`✅ Job #${job.id} berhasil dicetak`, 'success');

            } catch (e) {
                addLog(`❌ Job #${job.id} gagal: ${e.message}`, 'error');
                await fetch(`api/print-queue.php?action=fail&id=${job.id}&error=${encodeURIComponent(e.message)}`);
            }

            setStatus('active', 'Server Aktif - Menunggu antrian...');
            updateStats();
        }

        // Initial load
        updateStats();
    </script>
</body>

</html>