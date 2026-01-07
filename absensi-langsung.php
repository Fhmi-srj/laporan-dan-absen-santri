<?php
/**
 * Live Attendance Dashboard
 * Real-time view of attendance status
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$pdo = getDB();
$pageTitle = 'Live Attendance';

// Get jadwal list
$jadwalList = $pdo->query("SELECT * FROM jadwal_absens WHERE deleted_at IS NULL ORDER BY start_time ASC")->fetchAll();

$extraStyles = <<<'CSS'
<style>
    .stat-card {
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .stat-card .count {
        font-size: 2.5rem;
        font-weight: 700;
    }
    .stat-card.hadir { background: linear-gradient(135deg, #10b981, #059669); color: white; }
    .stat-card.terlambat { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    .stat-card.belum { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
    .stat-card.total { background: linear-gradient(135deg, #3b82f6, #60a5fa); color: white; }
    
    .siswa-list {
        max-height: 400px;
        overflow-y: auto;
    }
    .siswa-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .siswa-item:last-child { border-bottom: none; }
    .refresh-indicator {
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
CSS;
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0"><i class="fas fa-broadcast-tower me-2"></i>Live Attendance</h4>
        <div class="d-flex gap-2 align-items-center">
            <select id="jadwal_id" class="form-select" style="width: auto;">
                <option value="">-- Pilih Jadwal --</option>
                <?php foreach ($jadwalList as $j): ?>
                    <option value="<?= $j['id'] ?>">
                        <?= e($j['name']) ?> (
                        <?= substr($j['scheduled_time'], 0, 5) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button id="btn-refresh" class="btn btn-light border" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
            <span id="auto-refresh" class="badge bg-success refresh-indicator d-none">
                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Auto Refresh
            </span>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card total">
                <div class="count" id="count-total">0</div>
                <div>Total Siswa</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card hadir">
                <div class="count" id="count-hadir">0</div>
                <div>Hadir</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card terlambat">
                <div class="count" id="count-terlambat">0</div>
                <div>Terlambat</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card belum">
                <div class="count" id="count-belum">0</div>
                <div>Belum Hadir</div>
            </div>
        </div>
    </div>

    <!-- Lists -->
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-custom">
                <div class="p-3 border-bottom bg-success bg-opacity-10">
                    <h6 class="fw-bold text-success mb-0"><i class="fas fa-check-circle me-2"></i>Hadir</h6>
                </div>
                <div class="siswa-list" id="list-hadir">
                    <div class="text-center text-muted py-4">Pilih jadwal</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-custom">
                <div class="p-3 border-bottom bg-warning bg-opacity-10">
                    <h6 class="fw-bold text-warning mb-0"><i class="fas fa-clock me-2"></i>Terlambat</h6>
                </div>
                <div class="siswa-list" id="list-terlambat">
                    <div class="text-center text-muted py-4">Pilih jadwal</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-custom">
                <div class="p-3 border-bottom bg-danger bg-opacity-10">
                    <h6 class="fw-bold text-danger mb-0"><i class="fas fa-times-circle me-2"></i>Belum Hadir</h6>
                </div>
                <div class="siswa-list" id="list-belum">
                    <div class="text-center text-muted py-4">Pilih jadwal</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jadwalSelect = document.getElementById('jadwal_id');
        const btnRefresh = document.getElementById('btn-refresh');
        const autoRefreshBadge = document.getElementById('auto-refresh');
        let refreshInterval = null;

        jadwalSelect.addEventListener('change', function () {
            if (this.value) {
                loadData();
                startAutoRefresh();
            } else {
                stopAutoRefresh();
                resetDisplay();
            }
        });

        btnRefresh.addEventListener('click', loadData);

        function startAutoRefresh() {
            stopAutoRefresh();
            autoRefreshBadge.classList.remove('d-none');
            refreshInterval = setInterval(loadData, 10000); // Every 10 seconds
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
            autoRefreshBadge.classList.add('d-none');
        }

        function loadData() {
            const jadwalId = jadwalSelect.value;
            if (!jadwalId) return;

            btnRefresh.querySelector('i').classList.add('fa-spin');

            fetch('api/live-attendance.php?jadwal_id=' + jadwalId)
                .then(response => response.json())
                .then(data => {
                    updateStats(data.count);
                    updateList('list-hadir', data.hadir, 'success');
                    updateList('list-terlambat', data.terlambat, 'warning');
                    updateList('list-belum', data.belum_hadir, 'danger');
                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    btnRefresh.querySelector('i').classList.remove('fa-spin');
                });
        }

        function updateStats(count) {
            document.getElementById('count-total').textContent = count.total || 0;
            document.getElementById('count-hadir').textContent = count.hadir || 0;
            document.getElementById('count-terlambat').textContent = count.terlambat || 0;
            document.getElementById('count-belum').textContent = count.belum_hadir || 0;
        }

        function updateList(containerId, items, color) {
            const container = document.getElementById(containerId);
            if (!items || items.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-4">Tidak ada data</div>';
                return;
            }

            container.innerHTML = items.map(s => `
            <div class="siswa-item">
                <div>
                    <div class="fw-bold">${s.nama_lengkap}</div>
                    <small class="text-muted">${s.kelas || ''}</small>
                </div>
                ${s.waktu_absen ? `<span class="badge bg-${color}">${s.waktu_absen}</span>` : ''}
            </div>
        `).join('');
        }

        function resetDisplay() {
            ['count-total', 'count-hadir', 'count-terlambat', 'count-belum'].forEach(id => {
                document.getElementById(id).textContent = '0';
            });
            ['list-hadir', 'list-terlambat', 'list-belum'].forEach(id => {
                document.getElementById(id).innerHTML = '<div class="text-center text-muted py-4">Pilih jadwal</div>';
            });
        }
    });
</script>
<?php include __DIR__ . '/include/footer.php'; ?>