<?php
/**
 * RFID Card Registration
 * Halaman untuk mendaftarkan kartu RFID ke santri
 * Diakses oleh semua role
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$pdo = getDB();

// Get all students with enriched SPMB data
$siswaStmt = $pdo->query("SELECT * FROM siswa ORDER BY id DESC");
$siswaList = $siswaStmt->fetchAll();
enrichSiswaWithSPMB($siswaList);

$pageTitle = 'Daftarkan Kartu RFID';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $pageTitle ?> -
        <?= APP_NAME ?>
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
            margin-top: 1rem;
        }

        .card-scan-panel {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            text-align: center;
            height: fit-content;
            position: sticky;
            top: 80px;
        }

        .card-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
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

        .rfid-input {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 1rem;
            font-size: 1.5rem;
            color: white;
            text-align: center;
            width: 100%;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
        }

        .rfid-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1px;
            font-size: 1rem;
        }

        .rfid-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3);
        }

        .scanned-card {
            background: rgba(16, 185, 129, 0.2);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .scanned-card .card-number {
            font-size: 1.75rem;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .card-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .status-available {
            background: #10b981;
        }

        .status-registered {
            background: #f59e0b;
        }

        .siswa-list-panel {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .search-box {
            margin-bottom: 1rem;
        }

        .siswa-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .siswa-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .siswa-item:hover {
            border-color: var(--primary-color);
            background: #f0f7ff;
        }

        .siswa-item.selected {
            border-color: var(--primary-color);
            background: #e0f2fe;
        }

        .siswa-item.disabled-item {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .siswa-card-badge.incomplete {
            background: #fef3c7;
            color: #92400e;
        }

        .siswa-card-badge.available {
            background: #d1fae5;
            color: #065f46;
        }

        .siswa-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #60a5fa);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .siswa-info {
            flex: 1;
        }

        .siswa-name {
            font-weight: 600;
            color: #1e293b;
        }

        .siswa-meta {
            font-size: 0.85rem;
            color: #64748b;
        }

        .siswa-card-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
        }

        .siswa-card-badge.registered {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-register {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 12px;
            margin-top: 1.5rem;
        }

        .btn-register:disabled {
            background: #94a3b8;
        }

        @media (max-width: 991px) {
            .register-container {
                grid-template-columns: 1fr;
            }

            .card-scan-panel {
                position: relative;
                top: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header mb-4">
                <h4 class="mb-1"><i class="fas fa-id-card me-2"></i>
                    <?= $pageTitle ?>
                </h4>
                <p class="text-muted mb-0">Tap kartu RFID lalu pilih santri untuk mengaitkan kartu</p>
            </div>

            <div class="register-container">
                <!-- Left Panel - Card Scanner -->
                <div class="card-scan-panel">
                    <div class="card-icon"><i class="fas fa-id-card"></i></div>
                    <h4>Tempelkan Kartu</h4>
                    <p class="opacity-75 mb-3">Arahkan kartu baru ke reader</p>

                    <input type="text" id="rfidInput" class="rfid-input" placeholder="Menunggu kartu..." autofocus
                        autocomplete="off">

                    <div id="scannedCardInfo" class="scanned-card d-none">
                        <div class="mb-2"><i class="fas fa-credit-card me-2"></i>Nomor Kartu</div>
                        <div class="card-number" id="cardNumber">-</div>
                        <div class="card-status" id="cardStatus">-</div>
                    </div>

                    <button type="button" class="btn btn-primary btn-register w-100" id="btnRegister" disabled>
                        <i class="fas fa-link me-2"></i>Daftarkan Kartu ke Santri
                    </button>
                </div>

                <!-- Right Panel - Student List -->
                <div class="siswa-list-panel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Pilih Santri</h5>
                        <span class="badge bg-primary" id="selectedCount">0 dipilih</span>
                    </div>

                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchSiswa" class="form-control"
                                placeholder="Cari nama atau NIS...">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="hideRegistered">
                        <label class="form-check-label" for="hideRegistered">
                            Sembunyikan yang sudah punya kartu
                        </label>
                    </div>

                    <div class="siswa-list" id="siswaList">
                        <?php foreach ($siswaList as $s):
                            $hasCard = !empty($s['no_kartu_rfid']);
                            $hasNis = !empty($s['nomor_induk']);
                            $hasKelas = !empty($s['kelas']);
                            $isComplete = $hasNis && $hasKelas;
                            $isDisabled = $hasCard || !$isComplete;
                            ?>
                            <div class="siswa-item <?= $isDisabled ? 'disabled-item' : '' ?>" data-id="<?= $s['id'] ?>"
                                data-name="<?= strtolower(e($s['nama_lengkap'] ?? '')) ?>"
                                data-nis="<?= e($s['nomor_induk'] ?? '') ?>" data-rfid="<?= e($s['no_kartu_rfid'] ?? '') ?>"
                                data-complete="<?= $isComplete ? '1' : '0' ?>">
                                <div class="siswa-avatar">
                                    <?= strtoupper(substr($s['nama_lengkap'] ?? 'S', 0, 1)) ?>
                                </div>
                                <div class="siswa-info">
                                    <div class="siswa-name">
                                        <?= e($s['nama_lengkap'] ?? '-') ?>
                                    </div>
                                    <div class="siswa-meta">
                                        Kelas <?= $hasKelas ? e($s['kelas']) : '<span class="text-danger">-</span>' ?> |
                                        <?= $hasNis ? e($s['nomor_induk']) : '<span class="text-danger">Belum ada NIS</span>' ?>
                                    </div>
                                </div>
                                <?php if ($hasCard): ?>
                                    <span class="siswa-card-badge registered">
                                        <i class="fas fa-check-circle me-1"></i>Sudah ada
                                    </span>
                                <?php elseif (!$isComplete): ?>
                                    <span class="siswa-card-badge incomplete">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Data belum lengkap
                                    </span>
                                <?php else: ?>
                                    <span class="siswa-card-badge available">
                                        <i class="fas fa-plus-circle me-1"></i>Siap didaftarkan
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rfidInput = document.getElementById('rfidInput');
            const scannedCardInfo = document.getElementById('scannedCardInfo');
            const cardNumber = document.getElementById('cardNumber');
            const cardStatus = document.getElementById('cardStatus');
            const btnRegister = document.getElementById('btnRegister');
            const searchSiswa = document.getElementById('searchSiswa');
            const hideRegistered = document.getElementById('hideRegistered');

            let scannedRFID = null;
            let selectedSiswaId = null;

            // Keep focus on RFID input (only when not interacting with siswa panel)
            let isSearching = false;

            function focusInput() {
                if (!Swal.isVisible() && !isSearching && document.activeElement !== searchSiswa) {
                    rfidInput.focus();
                }
            }

            // Mark when user is using search
            searchSiswa.addEventListener('focus', () => { isSearching = true; });
            searchSiswa.addEventListener('blur', () => { isSearching = false; });

            // Only focus rfid when clicking outside siswa panel
            document.addEventListener('click', e => {
                if (!e.target.closest('.siswa-list-panel') && !e.target.closest('.form-check')) {
                    isSearching = false;
                    focusInput();
                }
            });

            // Handle RFID input
            rfidInput.addEventListener('input', function () {
                const val = this.value.trim();
                if (val.length >= 10 && /^\d+$/.test(val)) {
                    setTimeout(() => {
                        if (rfidInput.value.length >= 10) checkCard(rfidInput.value.trim());
                    }, 100);
                }
            });

            function checkCard(rfidNumber) {
                scannedRFID = rfidNumber;
                cardNumber.textContent = rfidNumber;
                scannedCardInfo.classList.remove('d-none');

                // Check if card is already registered
                fetch('api/check-rfid.php?rfid=' + encodeURIComponent(rfidNumber))
                    .then(res => res.json())
                    .then(data => {
                        if (data.registered) {
                            cardStatus.textContent = 'Sudah terdaftar: ' + data.siswa_name;
                            cardStatus.className = 'card-status status-registered';
                            scannedRFID = null;
                            updateRegisterButton();
                        } else {
                            cardStatus.textContent = 'Belum terdaftar - Siap digunakan';
                            cardStatus.className = 'card-status status-available';
                            updateRegisterButton();
                        }
                    })
                    .catch(err => {
                        cardStatus.textContent = 'Kartu baru - Siap didaftarkan';
                        cardStatus.className = 'card-status status-available';
                        updateRegisterButton();
                    });

                rfidInput.value = '';
            }

            // Student list click
            document.querySelectorAll('.siswa-item').forEach(item => {
                item.addEventListener('click', function () {
                    document.querySelectorAll('.siswa-item').forEach(i => i.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedSiswaId = this.dataset.id;
                    document.getElementById('selectedCount').textContent = '1 dipilih';
                    updateRegisterButton();
                });
            });

            // Search filter
            searchSiswa.addEventListener('input', filterList);
            hideRegistered.addEventListener('change', filterList);

            function filterList() {
                const searchVal = searchSiswa.value.toLowerCase().trim();
                const hideReg = hideRegistered.checked;

                document.querySelectorAll('.siswa-item').forEach(item => {
                    const name = item.dataset.name || '';
                    const nis = item.dataset.nis || '';
                    const hasRfid = item.dataset.rfid !== '';

                    let show = true;
                    if (searchVal && !name.includes(searchVal) && !nis.includes(searchVal)) show = false;
                    if (hideReg && hasRfid) show = false;

                    item.style.display = show ? 'flex' : 'none';
                });
            }
            filterList();

            function updateRegisterButton() {
                btnRegister.disabled = !(scannedRFID && selectedSiswaId);
            }

            // Register button
            btnRegister.addEventListener('click', function () {
                if (!scannedRFID || !selectedSiswaId) return;

                const selectedItem = document.querySelector(`.siswa-item[data-id="${selectedSiswaId}"]`);
                const siswaName = selectedItem.querySelector('.siswa-name').textContent;

                Swal.fire({
                    title: 'Konfirmasi Pendaftaran',
                    html: `
                    <p>Daftarkan kartu:</p>
                    <p class="fw-bold fs-4" style="letter-spacing:2px;font-family:monospace">${scannedRFID}</p>
                    <p>Ke santri:</p>
                    <p class="fw-bold">${siswaName}</p>
                `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Daftarkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#10b981'
                }).then(result => {
                    if (result.isConfirmed) {
                        registerCard();
                    }
                });
            });

            function registerCard() {
                const formData = new FormData();
                formData.append('siswa_id', selectedSiswaId);
                formData.append('rfid', scannedRFID);

                fetch('api/register-rfid.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal mendaftarkan kartu', 'error');
                    });
            }
        });
    </script>
</body>

</html>