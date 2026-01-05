<?php
/**
 * Admin: Santri Management
 * Data langsung dari SPMB pendaftaran
 * Admin hanya mengelola: Nomor Induk, Kelas, No. RFID
 */

require_once __DIR__ . '/../functions.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDB();
$spmb = getSPMBDB();
$flash = getFlash();
$pageTitle = 'Data Santri';

// Handle Update (only NIS, Kelas, RFID)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update' && !empty($_POST['pendaftaran_id'])) {
        $pendaftaranId = $_POST['pendaftaran_id'];

        // Check if exists in siswa table
        $checkStmt = $pdo->prepare("SELECT id FROM siswa WHERE pendaftaran_id = ?");
        $checkStmt->execute([$pendaftaranId]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            // Update existing
            $stmt = $pdo->prepare("UPDATE siswa SET nomor_induk=?, no_kartu_rfid=?, kelas=?, updated_at=NOW() WHERE pendaftaran_id=?");
            $stmt->execute([
                $_POST['nomor_induk'] ?: null,
                $_POST['no_kartu_rfid'] ?: null,
                $_POST['kelas'] ?: null,
                $pendaftaranId
            ]);
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO siswa (pendaftaran_id, nomor_induk, no_kartu_rfid, kelas, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $pendaftaranId,
                $_POST['nomor_induk'] ?: null,
                $_POST['no_kartu_rfid'] ?: null,
                $_POST['kelas'] ?: null
            ]);
        }
        redirectWith('siswa.php', 'success', 'Data santri berhasil diperbarui!');
    }
}

// Pagination & Search
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterLembaga = $_GET['lembaga'] ?? '';

// Build query for SPMB pendaftaran
$whereClause = '1=1';
$params = [];

if ($search) {
    $whereClause .= " AND (nama LIKE ? OR no_hp_wali LIKE ? OR nik LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filterStatus) {
    $whereClause .= " AND status = ?";
    $params[] = $filterStatus;
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

// Merge data
foreach ($pendaftaranList as &$p) {
    $s = $siswaData[$p['id']] ?? [];
    $p['nomor_induk'] = $s['nomor_induk'] ?? null;
    $p['no_kartu_rfid'] = $s['no_kartu_rfid'] ?? null;
    $p['kelas'] = $s['kelas'] ?? null;
    $p['siswa_id'] = $s['id'] ?? null;
}
unset($p);

// Get filter options
$lembagaList = $spmb->query("SELECT DISTINCT lembaga FROM pendaftaran ORDER BY lembaga")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1"><i class="fas fa-user-graduate me-2"></i>Data Santri</h5>
            <small class="text-muted">Data dari SPMB (<?= $total ?> pendaftar)</small>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-custom p-3 mb-4">
        <form class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Cari</label>
                <input type="text" name="search" class="form-control" placeholder="Nama/WA/NIK..."
                    value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="verified" <?= $filterStatus === 'verified' ? 'selected' : '' ?>>Verified</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Lembaga</label>
                <select name="lembaga" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($lembagaList as $l): ?>
                        <option value="<?= e($l) ?>" <?= $filterLembaga === $l ? 'selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sortable">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Lembaga</th>
                        <th>Status SPMB</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>RFID</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendaftaranList as $i => $p): ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td>
                                <strong><?= e($p['nama']) ?></strong><br>
                                <small class="text-muted">
                                    <?= $p['jenis_kelamin'] == 'L' ? '♂' : '♀' ?>
                                    <?= e($p['no_hp_wali']) ?>
                                </small>
                            </td>
                            <td><small><?= e($p['lembaga']) ?></small></td>
                            <td>
                                <?php if ($p['status'] === 'verified'): ?>
                                    <span class="badge bg-success">Verified</span>
                                <?php elseif ($p['status'] === 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['nomor_induk']): ?>
                                    <code><?= e($p['nomor_induk']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['kelas']): ?>
                                    <span class="badge bg-light text-dark border"><?= e($p['kelas']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($p['no_kartu_rfid'] ?? '-') ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="Edit NIS/Kelas/RFID"
                                    onclick='editSantri(<?= json_encode($p) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" title="Lihat Detail"
                                    onclick='viewDetail(<?= json_encode($p) ?>)'>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pendaftaranList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Tidak ada data pendaftar
                            </td>
                        </tr>
                    <?php endif; ?>
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
                                    href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filterStatus) ?>&lembaga=<?= urlencode($filterLembaga) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit NIS/Kelas/RFID -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="pendaftaran_id" id="edit_pendaftaran_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Data Santri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong id="edit_nama"></strong><br>
                        <small id="edit_info"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Induk (NIS)</label>
                        <input type="text" name="nomor_induk" id="edit_nis" class="form-control"
                            placeholder="Contoh: 2024001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" id="edit_kelas" class="form-control" placeholder="Contoh: X-A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Kartu RFID</label>
                        <input type="text" name="no_kartu_rfid" id="edit_rfid" class="form-control"
                            placeholder="Contoh: RFID001">
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

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>Detail Pendaftar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
            </div>
        </div>
    </div>
</div>

<script>
    function editSantri(p) {
        document.getElementById('edit_pendaftaran_id').value = p.id;
        document.getElementById('edit_nama').textContent = p.nama;
        document.getElementById('edit_info').innerHTML = `${p.lembaga} | ${p.jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'} | WA: ${p.no_hp_wali}`;
        document.getElementById('edit_nis').value = p.nomor_induk || '';
        document.getElementById('edit_kelas').value = p.kelas || '';
        document.getElementById('edit_rfid').value = p.no_kartu_rfid || '';
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    function viewDetail(p) {
        const content = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold border-bottom pb-2">Data Pribadi</h6>
                <table class="table table-sm">
                    <tr><td class="text-muted">Nama</td><td><strong>${p.nama || '-'}</strong></td></tr>
                    <tr><td class="text-muted">No. Registrasi</td><td><code>${p.no_registrasi || '-'}</code></td></tr>
                    <tr><td class="text-muted">Lembaga</td><td>${p.lembaga || '-'}</td></tr>
                    <tr><td class="text-muted">Jenis Kelamin</td><td>${p.jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
                    <tr><td class="text-muted">NIK</td><td>${p.nik || '-'}</td></tr>
                    <tr><td class="text-muted">NISN</td><td>${p.nisn || '-'}</td></tr>
                    <tr><td class="text-muted">Tempat, Tgl Lahir</td><td>${p.tempat_lahir || '-'}, ${p.tanggal_lahir || '-'}</td></tr>
                    <tr><td class="text-muted">No. HP Wali</td><td>${p.no_hp_wali || '-'}</td></tr>
                    <tr><td class="text-muted">Asal Sekolah</td><td>${p.asal_sekolah || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold border-bottom pb-2">Data Orang Tua</h6>
                <table class="table table-sm">
                    <tr><td class="text-muted">Nama Ayah</td><td>${p.nama_ayah || '-'}</td></tr>
                    <tr><td class="text-muted">Pekerjaan Ayah</td><td>${p.pekerjaan_ayah || '-'}</td></tr>
                    <tr><td class="text-muted">Nama Ibu</td><td>${p.nama_ibu || '-'}</td></tr>
                    <tr><td class="text-muted">Pekerjaan Ibu</td><td>${p.pekerjaan_ibu || '-'}</td></tr>
                </table>
                
                <h6 class="fw-bold border-bottom pb-2 mt-3">Alamat</h6>
                <p class="small">${p.alamat || '-'}, ${p.kelurahan_desa || ''}, ${p.kecamatan || ''}, ${p.kota_kab || ''}, ${p.provinsi || ''}</p>
                
                <h6 class="fw-bold border-bottom pb-2 mt-3">Data Admin</h6>
                <table class="table table-sm">
                    <tr><td class="text-muted">NIS</td><td><code>${p.nomor_induk || '-'}</code></td></tr>
                    <tr><td class="text-muted">Kelas</td><td>${p.kelas || '-'}</td></tr>
                    <tr><td class="text-muted">RFID</td><td>${p.no_kartu_rfid || '-'}</td></tr>
                </table>
            </div>
        </div>
    `;
        document.getElementById('detailContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('modalDetail')).show();
    }
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>