<?php
/**
 * Siswa Management (For all users)
 * View/search siswa, print QR code
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDB();
$spmb = getSPMBDB();
$flash = getFlash();
$pageTitle = 'Data Santri';
$role = $user['role'];

// Only admin can create/edit/delete (via admin/siswa.php)
$canEdit = false; // Disabled - editing via admin/siswa.php with SPMB integration

// Search and pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$filterKelas = $_GET['kelas'] ?? '';
$filterLembaga = $_GET['lembaga'] ?? '';

// Build query for SPMB pendaftaran (same as admin/siswa.php)
$whereClause = '1=1';
$params = [];

if ($search) {
    $whereClause .= " AND (nama LIKE ? OR no_hp_wali LIKE ? OR nik LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filterLembaga) {
    $whereClause .= " AND lembaga = ?";
    $params[] = $filterLembaga;
}

// Count total from SPMB
$countStmt = $spmb->prepare("SELECT COUNT(*) FROM pendaftaran WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Get data from SPMB
$stmt = $spmb->prepare("SELECT * FROM pendaftaran WHERE $whereClause ORDER BY nama ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$pendaftaranList = $stmt->fetchAll();

// Get additional data from siswa table (nomor_induk, kelas, rfid)
$pendaftaranIds = array_column($pendaftaranList, 'id');
$siswaData = [];
if (!empty($pendaftaranIds)) {
    $placeholders = str_repeat('?,', count($pendaftaranIds) - 1) . '?';
    $siswaStmt = $pdo->prepare("SELECT * FROM siswa WHERE pendaftaran_id IN ($placeholders)");
    $siswaStmt->execute($pendaftaranIds);
    while ($row = $siswaStmt->fetch()) {
        $siswaData[$row['pendaftaran_id']] = $row;
    }
}

// Merge the data
$siswaList = [];
foreach ($pendaftaranList as $p) {
    $s = $siswaData[$p['id']] ?? [];
    $siswaList[] = [
        'id' => $s['id'] ?? null,
        'pendaftaran_id' => $p['id'],
        'nama_lengkap' => $p['nama'] ?? '-',
        'nomor_induk' => $s['nomor_induk'] ?? null,
        'no_kartu_rfid' => $s['no_kartu_rfid'] ?? null,
        'kelas' => $s['kelas'] ?? null,
        'lembaga' => $p['lembaga'] ?? '-',
        'status_spmb' => $p['status'] ?? '-',
        'jenis_kelamin' => $p['jenis_kelamin'] ?? '-',
        'no_wa_wali' => $p['no_hp_wali'] ?? '-',
        'nik' => $p['nik'] ?? '-',
        'nisn' => $p['nisn'] ?? '-',
    ];
}

// Filter by kelas (after merge)
if ($filterKelas) {
    $siswaList = array_filter($siswaList, fn($s) => $s['kelas'] === $filterKelas);
    $siswaList = array_values($siswaList);
}

// Get unique kelas for filter
$kelasList = $pdo->query("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL ORDER BY kelas")->fetchAll(PDO::FETCH_COLUMN);

// Get unique lembaga for filter
$lembagaList = $spmb->query("SELECT DISTINCT lembaga FROM pendaftaran WHERE lembaga IS NOT NULL ORDER BY lembaga")->fetchAll(PDO::FETCH_COLUMN);

// Get settings for school name
$settingStmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` = 'school_name'");
$settings = [];
while ($row = $settingStmt->fetch()) {
    $settings[$row['key']] = $row['value'];
}
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h5 class="fw-bold mb-0"><i class="fas fa-user-graduate me-2"></i>Data Santri (
            <?= $total ?>)
        </h5>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-success d-none" id="btnBulkPrint" onclick="printSelectedCards()">
                <i class="fas fa-id-card me-1"></i> Cetak <span id="selectedCount">0</span> Kartu
            </button>
            <form class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari nama/NIS..."
                    value="<?= e($search) ?>" style="width: 200px;">
                <select name="kelas" class="form-select" style="width: auto;">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= e($k) ?>" <?= $filterKelas === $k ? 'selected' : '' ?>>
                            <?= e($k) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-light border"><i class="fas fa-filter"></i></button>
            </form>
            <?php if ($canEdit): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSiswa" onclick="resetForm()">
                    <i class="fas fa-plus me-1"></i> Tambah
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sortable">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="checkAll" onclick="toggleAll(this)">
                        </th>
                        <th>Nama</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>WA Wali</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswaList as $s): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input siswa-check" data-id="<?= $s['id'] ?>"
                                    data-nama="<?= e($s['nama_lengkap']) ?>" data-nis="<?= e($s['nomor_induk']) ?>"
                                    data-kelas="<?= e($s['kelas']) ?>" onchange="updateSelectedCount()">
                            </td>
                            <td><strong>
                                    <?= e($s['nama_lengkap']) ?>
                                </strong></td>
                            <td><code><?= e($s['nomor_induk']) ?></code></td>
                            <td><span class="badge bg-light text-dark border">
                                    <?= e($s['kelas']) ?>
                                </span></td>
                            <td>
                                <?= e($s['no_wa_wali'] ?? '-') ?>
                            </td>
                            <td>
                                <?php if (!empty($s['nomor_induk']) && !empty($s['kelas']) && !empty($s['no_kartu_rfid'])): ?>
                                    <button class="btn btn-sm btn-outline-primary" title="Kartu Santri"
                                        onclick='showCard(<?= json_encode($s) ?>)'>
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small" title="Lengkapi NIS, Kelas, dan RFID terlebih dahulu">
                                        <i class="fas fa-qrcode text-muted"></i>
                                    </span>
                                <?php endif; ?>
                                <?php if ($canEdit): ?>
                                    <button class="btn btn-sm btn-outline-warning"
                                        onclick="editSiswa(<?= htmlspecialchars(json_encode($s)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus siswa ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top">
                <nav>
                    <ul class="pagination mb-0 justify-content-center">
                        <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&kelas=<?= urlencode($filterKelas) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($canEdit): ?>
    <!-- Modal Create/Edit -->
    <div class="modal fade" id="modalSiswa" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" id="form_action" value="store">
                    <input type="hidden" name="id" id="form_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Santri</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama_lengkap" id="f_nama" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Induk (NIS) <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_induk" id="f_nis" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="kelas" id="f_kelas" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. Kartu RFID</label>
                                <input type="text" name="no_kartu_rfid" id="f_rfid" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. WA Santri</label>
                                <input type="text" name="no_wa" id="f_wa" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. WA Wali</label>
                                <input type="text" name="no_wa_wali" id="f_wa_wali" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" id="f_alamat" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function resetForm() {
            document.getElementById('form_action').value = 'store';
            document.getElementById('form_id').value = '';
            ['f_nama', 'f_nis', 'f_kelas', 'f_rfid', 'f_wa', 'f_wa_wali', 'f_alamat'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('modalTitle').textContent = 'Tambah Santri';
        }
        function editSiswa(s) {
            document.getElementById('form_action').value = 'update';
            document.getElementById('form_id').value = s.id;
            document.getElementById('f_nama').value = s.nama_lengkap;
            document.getElementById('f_nis').value = s.nomor_induk || '';
            document.getElementById('f_kelas').value = s.kelas || '';
            document.getElementById('f_rfid').value = s.no_kartu_rfid || '';
            document.getElementById('f_wa').value = s.no_wa || '';
            document.getElementById('f_wa_wali').value = s.no_wa_wali || '';
            document.getElementById('f_alamat').value = s.alamat || '';
            document.getElementById('modalTitle').textContent = 'Edit Santri';
            new bootstrap.Modal(document.getElementById('modalSiswa')).show();
        }
    </script>
<?php endif; ?>

<script>
    function toggleAll(source) {
        document.querySelectorAll('.siswa-check').forEach(cb => cb.checked = source.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.siswa-check:checked');
        const count = checked.length;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('btnBulkPrint').classList.toggle('d-none', count === 0);
    }

    function printSelectedCards() {
        const checked = document.querySelectorAll('.siswa-check:checked');
        if (checked.length === 0) {
            alert('Pilih minimal 1 siswa!');
            return;
        }

        const ids = [];
        checked.forEach(cb => ids.push(cb.dataset.id));

        // Open bulk print page with selected IDs
        window.open('print-cards.php?ids=' + ids.join(','), '_blank');
    }

    // Card Modal Functions
    let currentCardData = null;
    const schoolName = '<?= addslashes($settings['school_name'] ?? 'Pondok Pesantren Mambaul Huda') ?>';

    function showCard(siswa) {
        currentCardData = siswa;
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(siswa.nomor_induk)}`;

        document.getElementById('cardSchoolName').textContent = schoolName;
        document.getElementById('cardQr').src = qrUrl;
        document.getElementById('cardNama').textContent = siswa.nama_lengkap;
        document.getElementById('cardNis').textContent = siswa.nomor_induk;
        document.getElementById('cardKelas').textContent = 'Kelas ' + siswa.kelas;

        new bootstrap.Modal(document.getElementById('modalCard')).show();
    }

    function printCard() {
        const printWindow = window.open('', '_blank');
        const cardHtml = document.getElementById('cardContainer').innerHTML;
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Kartu Santri - ${currentCardData.nama_lengkap}</title>
                <style>
                    body { margin: 20px; font-family: 'Poppins', sans-serif; }
                    ${document.getElementById('cardStyles').textContent}
                </style>
            </head>
            <body>${cardHtml}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.onload = () => { printWindow.print(); };
    }

    function downloadCardImage() {
        const card = document.querySelector('#cardContainer .id-card');
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;

        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;

        html2canvas(card, { scale: 3, useCORS: true, allowTaint: true, backgroundColor: null })
            .then(canvas => {
                const link = document.createElement('a');
                link.download = `Kartu_${currentCardData.nomor_induk}_${currentCardData.nama_lengkap.replace(/[^a-zA-Z0-9]/g, '_')}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(() => {
                alert('Gagal download. Gunakan tombol Cetak.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }
</script>

<!-- Card Modal -->
<style id="cardStyles">
    .id-card {
        width: 340px;
        height: 215px;
        background: linear-gradient(135deg, #1e3a5f 0%, #3b82f6 50%, #60a5fa 100%);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(30, 58, 95, 0.3);
        color: white;
        font-family: 'Poppins', sans-serif;
    }

    .id-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .card-top {
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        z-index: 1;
    }

    .school-info {
        flex: 1;
    }

    .school-name {
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
    }

    .card-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #fbbf24;
        margin-top: 2px;
    }

    .card-logo {
        width: 35px;
        height: 35px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-logo img {
        width: 30px;
        height: 30px;
        object-fit: contain;
    }

    .card-body-inner {
        display: flex;
        padding: 0 16px;
        gap: 14px;
        position: relative;
        z-index: 1;
    }

    .qr-section {
        background: white;
        padding: 6px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .qr-section img {
        display: block;
        width: 90px;
        height: 90px;
    }

    .info-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .student-name {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .student-nis {
        font-size: 16px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
        color: #fbbf24;
        margin-bottom: 6px;
    }

    .student-class span {
        background: rgba(255, 255, 255, 0.2);
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
    }

    .card-bottom {
        position: absolute;
        bottom: 10px;
        left: 16px;
        right: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 7px;
        opacity: 0.7;
        z-index: 1;
    }

    .chip {
        width: 35px;
        height: 26px;
        background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
        border-radius: 4px;
        position: relative;
    }

    .chip::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 25px;
        height: 18px;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 2px;
    }
</style>

<div class="modal fade" id="modalCard" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fas fa-id-card me-2"></i>Kartu Santri</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="cardContainer">
                <div class="id-card mx-auto">
                    <div class="card-top">
                        <div class="school-info">
                            <div class="school-name" id="cardSchoolName"></div>
                            <div class="card-title">Kartu Santri</div>
                        </div>
                        <div class="card-logo">
                            <img src="logo-pondok.png" alt="Logo">
                        </div>
                    </div>
                    <div class="card-body-inner">
                        <div class="qr-section">
                            <img src="" alt="QR" id="cardQr">
                        </div>
                        <div class="info-section">
                            <div class="student-name" id="cardNama"></div>
                            <div class="student-nis" id="cardNis"></div>
                            <div class="student-class"><span id="cardKelas"></span></div>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <div class="chip"></div>
                        <div>Scan QR untuk absensi</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button class="btn btn-primary" onclick="printCard()">
                    <i class="fas fa-print me-1"></i> Cetak
                </button>
                <button class="btn btn-success" onclick="downloadCardImage()">
                    <i class="fas fa-download me-1"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<?php include __DIR__ . '/include/footer.php'; ?>