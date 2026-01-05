<?php
require_once '../api/config.php';
requireLogin();

$conn = getConnection();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $kategori = sanitize($conn, $_POST['kategori']);
        $nama_item = sanitize($conn, $_POST['nama_item']);
        $biaya_pondok = intval($_POST['biaya_pondok'] ?? 0);
        $biaya_smp = intval($_POST['biaya_smp'] ?? 0);
        $biaya_ma = intval($_POST['biaya_ma'] ?? 0);
        $urutan = intval($_POST['urutan'] ?? 0);
        
        $stmt = $conn->prepare("INSERT INTO biaya (kategori, nama_item, biaya_pondok, biaya_smp, biaya_ma, urutan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiii", $kategori, $nama_item, $biaya_pondok, $biaya_smp, $biaya_ma, $urutan);
        
        if ($stmt->execute()) {
            $message = 'Data biaya berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan data!';
        }
    }
    
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $kategori = sanitize($conn, $_POST['kategori']);
        $nama_item = sanitize($conn, $_POST['nama_item']);
        $biaya_pondok = intval($_POST['biaya_pondok'] ?? 0);
        $biaya_smp = intval($_POST['biaya_smp'] ?? 0);
        $biaya_ma = intval($_POST['biaya_ma'] ?? 0);
        $urutan = intval($_POST['urutan'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE biaya SET kategori=?, nama_item=?, biaya_pondok=?, biaya_smp=?, biaya_ma=?, urutan=? WHERE id=?");
        $stmt->bind_param("ssiiiii", $kategori, $nama_item, $biaya_pondok, $biaya_smp, $biaya_ma, $urutan, $id);
        
        if ($stmt->execute()) {
            $message = 'Data biaya berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate data!';
        }
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM biaya WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Data biaya berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus data!';
        }
    }
}

// Get all biaya
$result = $conn->query("SELECT * FROM biaya ORDER BY kategori DESC, urutan ASC");
$biayaList = [];
while ($row = $result->fetch_assoc()) {
    $biayaList[] = $row;
}

// Calculate totals
$totals = ['pondok' => 0, 'smp' => 0, 'ma' => 0];
foreach ($biayaList as $b) {
    $totals['pondok'] += $b['biaya_pondok'];
    $totals['smp'] += $b['biaya_smp'];
    $totals['ma'] += $b['biaya_ma'];
}

$conn->close();

// Page config
$pageTitle = 'Kelola Biaya - Admin SPMB';
$currentPage = 'biaya';
?>
<?php include 'includes/header.php'; ?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Biaya</h2>
                <p class="text-gray-500 text-sm">Atur biaya pendaftaran dan daftar ulang</p>
            </div>
            <button onclick="openModal('addModal')" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-2"></i>Tambah Item
            </button>
        </div>

        <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-check-circle mr-2"></i><?= $message ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i><?= $error ?>
        </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Item</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pondok</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">SMP</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">MA</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php 
                        $currentKategori = '';
                        $no = 1;
                        foreach ($biayaList as $row): 
                            if ($currentKategori !== $row['kategori']):
                                $currentKategori = $row['kategori'];
                        ?>
                        <tr class="bg-primary/5">
                            <td colspan="7" class="px-4 py-2 font-semibold text-primary text-sm">
                                <?= $row['kategori'] === 'PENDAFTARAN' ? 'A. PENDAFTARAN' : 'B. DAFTAR ULANG' ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500"><?= $no++ ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= $row['kategori'] ?></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800"><?= htmlspecialchars($row['nama_item']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right"><?= $row['biaya_pondok'] > 0 ? 'Rp' . number_format($row['biaya_pondok'], 0, ',', '.') : '-' ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right"><?= $row['biaya_smp'] > 0 ? 'Rp' . number_format($row['biaya_smp'], 0, ',', '.') : '-' ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right"><?= $row['biaya_ma'] > 0 ? 'Rp' . number_format($row['biaya_ma'], 0, ',', '.') : '-' ?></td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="editItem(<?= htmlspecialchars(json_encode($row)) ?>)" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteItem(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_item']) ?>')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-primary text-white font-bold">
                            <td colspan="3" class="px-4 py-3 text-sm">TOTAL</td>
                            <td class="px-4 py-3 text-sm text-right">Rp<?= number_format($totals['pondok'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-sm text-right">Rp<?= number_format($totals['smp'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-sm text-right">Rp<?= number_format($totals['ma'], 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="modal-container bg-white rounded-xl max-w-md w-full">
        <div class="modal-header flex items-center justify-between">
            <h3>Tambah Item Biaya</h3>
            <button onclick="closeModal('addModal')" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body space-y-4">
                <input type="hidden" name="action" value="add">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="kategori" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    <option value="PENDAFTARAN">Pendaftaran</option>
                    <option value="DAFTAR_ULANG">Daftar Ulang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                <input type="text" name="nama_item" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pondok</label>
                    <input type="number" name="biaya_pondok" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMP</label>
                    <input type="number" name="biaya_smp" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MA</label>
                    <input type="number" name="biaya_ma" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
            </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="urutan" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('addModal')" class="btn btn-cancel">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="modal-container bg-white rounded-xl max-w-md w-full">
        <div class="modal-header flex items-center justify-between">
            <h3>Edit Item Biaya</h3>
            <button onclick="closeModal('editModal')" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="kategori" id="editKategori" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    <option value="PENDAFTARAN">Pendaftaran</option>
                    <option value="DAFTAR_ULANG">Daftar Ulang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                <input type="text" name="nama_item" id="editNamaItem" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pondok</label>
                    <input type="number" name="biaya_pondok" id="editBiayaPondok" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMP</label>
                    <input type="number" name="biaya_smp" id="editBiayaSmp" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MA</label>
                    <input type="number" name="biaya_ma" id="editBiayaMa" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
            </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="urutan" id="editUrutan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editModal')" class="btn btn-cancel">Batal</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal-overlay fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="modal-container bg-white rounded-xl max-w-sm w-full p-6 text-center">
        <div class="delete-modal-icon">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3 class="font-bold text-lg text-gray-800 mb-2">Hapus Item?</h3>
        <p class="text-gray-500 text-sm mb-6">Yakin ingin menghapus <strong id="deleteName"></strong>?</p>
        <form method="POST" class="flex gap-3">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            <button type="button" onclick="closeModal('deleteModal')" class="btn btn-cancel">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    
    function editItem(data) {
        document.getElementById('editId').value = data.id;
        document.getElementById('editKategori').value = data.kategori;
        document.getElementById('editNamaItem').value = data.nama_item;
        document.getElementById('editBiayaPondok').value = data.biaya_pondok;
        document.getElementById('editBiayaSmp').value = data.biaya_smp;
        document.getElementById('editBiayaMa').value = data.biaya_ma;
        document.getElementById('editUrutan').value = data.urutan;
        openModal('editModal');
    }
    
    function deleteItem(id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteName').textContent = name;
        openModal('deleteModal');
    }
</script>
</body>
</html>
