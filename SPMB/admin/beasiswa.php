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
        $jenis = sanitize($conn, $_POST['jenis']);
        $kategori = sanitize($conn, $_POST['kategori']);
        $syarat = sanitize($conn, $_POST['syarat']);
        $benefit = sanitize($conn, $_POST['benefit']);
        $urutan = intval($_POST['urutan'] ?? 0);
        
        $stmt = $conn->prepare("INSERT INTO beasiswa (jenis, kategori, syarat, benefit, urutan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $jenis, $kategori, $syarat, $benefit, $urutan);
        
        if ($stmt->execute()) {
            $message = 'Data beasiswa berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan data!';
        }
    }
    
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $jenis = sanitize($conn, $_POST['jenis']);
        $kategori = sanitize($conn, $_POST['kategori']);
        $syarat = sanitize($conn, $_POST['syarat']);
        $benefit = sanitize($conn, $_POST['benefit']);
        $urutan = intval($_POST['urutan'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE beasiswa SET jenis=?, kategori=?, syarat=?, benefit=?, urutan=? WHERE id=?");
        $stmt->bind_param("ssssii", $jenis, $kategori, $syarat, $benefit, $urutan, $id);
        
        if ($stmt->execute()) {
            $message = 'Data beasiswa berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate data!';
        }
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM beasiswa WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Data beasiswa berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus data!';
        }
    }
}

// Get all beasiswa grouped by jenis
$result = $conn->query("SELECT * FROM beasiswa ORDER BY urutan ASC");
$beasiswaList = [];
while ($row = $result->fetch_assoc()) {
    $beasiswaList[] = $row;
}

$conn->close();

// Page config
$pageTitle = 'Kelola Beasiswa - Admin SPMB';
$currentPage = 'beasiswa';
?>
<?php include 'includes/header.php'; ?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Beasiswa</h2>
                <p class="text-gray-500 text-sm">Atur jenis dan ketentuan beasiswa</p>
            </div>
            <button onclick="openModal('addModal')" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-2"></i>Tambah Beasiswa
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Syarat</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Benefit</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php 
                        $no = 1;
                        foreach ($beasiswaList as $row): 
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500"><?= $no++ ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-primary/10 text-primary rounded-full text-xs font-medium"><?= htmlspecialchars($row['jenis']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($row['kategori']) ?></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800"><?= htmlspecialchars($row['syarat']) ?></td>
                            <td class="px-4 py-3 text-sm text-green-600 font-medium"><?= htmlspecialchars($row['benefit']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="editItem(<?= htmlspecialchars(json_encode($row)) ?>)" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteItem(<?= $row['id'] ?>, '<?= htmlspecialchars($row['syarat']) ?>')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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
            <h3>Tambah Beasiswa</h3>
            <button onclick="closeModal('addModal')" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body space-y-4">
                <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Beasiswa</label>
                <input type="text" name="jenis" required placeholder="Contoh: Tahfidz, Akademik" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <input type="text" name="kategori" required placeholder="Contoh: Penghafal Al-Quran" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Syarat</label>
                <input type="text" name="syarat" required placeholder="Contoh: Hafal 1-5 Juz" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Benefit</label>
                <input type="text" name="benefit" required placeholder="Contoh: Gratis SPP 1 Bulan" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
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
            <h3>Edit Beasiswa</h3>
            <button onclick="closeModal('editModal')" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Beasiswa</label>
                <input type="text" name="jenis" id="editJenis" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <input type="text" name="kategori" id="editKategori" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Syarat</label>
                <input type="text" name="syarat" id="editSyarat" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Benefit</label>
                <input type="text" name="benefit" id="editBenefit" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
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
        <h3 class="font-bold text-lg text-gray-800 mb-2">Hapus Beasiswa?</h3>
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
        document.getElementById('editJenis').value = data.jenis;
        document.getElementById('editKategori').value = data.kategori;
        document.getElementById('editSyarat').value = data.syarat;
        document.getElementById('editBenefit').value = data.benefit;
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
