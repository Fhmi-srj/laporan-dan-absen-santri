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
        $lembaga = sanitize($conn, $_POST['lembaga']);
        $nama = sanitize($conn, $_POST['nama']);
        $no_whatsapp = sanitize($conn, $_POST['no_whatsapp']);
        $link_wa = sanitize($conn, $_POST['link_wa']);
        
        $stmt = $conn->prepare("INSERT INTO kontak (lembaga, nama, no_whatsapp, link_wa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $lembaga, $nama, $no_whatsapp, $link_wa);
        
        if ($stmt->execute()) {
            $message = 'Kontak berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan kontak!';
        }
    }
    
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $lembaga = sanitize($conn, $_POST['lembaga']);
        $nama = sanitize($conn, $_POST['nama']);
        $no_whatsapp = sanitize($conn, $_POST['no_whatsapp']);
        $link_wa = sanitize($conn, $_POST['link_wa']);
        
        $stmt = $conn->prepare("UPDATE kontak SET lembaga=?, nama=?, no_whatsapp=?, link_wa=? WHERE id=?");
        $stmt->bind_param("ssssi", $lembaga, $nama, $no_whatsapp, $link_wa, $id);
        
        if ($stmt->execute()) {
            $message = 'Kontak berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate kontak!';
        }
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM kontak WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Kontak berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus kontak!';
        }
    }
}

// Get all kontak
$result = $conn->query("SELECT * FROM kontak ORDER BY id ASC");
$kontakList = [];
while ($row = $result->fetch_assoc()) {
    $kontakList[] = $row;
}

$conn->close();

// Page config
$pageTitle = 'Kelola Kontak - Admin SPMB';
$currentPage = 'kontak';
?>
<?php include 'includes/header.php'; ?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Kontak</h2>
                <p class="text-gray-500 text-sm">Atur kontak WhatsApp per lembaga</p>
            </div>
            <button onclick="openModal('addModal')" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-2"></i>Tambah Kontak
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($kontakList as $row): ?>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fab fa-whatsapp text-green-600 text-2xl"></i>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="editItem(<?= htmlspecialchars(json_encode($row)) ?>)" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteItem(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama']) ?>')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <span class="px-2 py-1 bg-primary/10 text-primary rounded-full text-xs font-medium"><?= htmlspecialchars($row['lembaga']) ?></span>
                <h3 class="font-semibold text-gray-800 mt-2"><?= htmlspecialchars($row['nama']) ?></h3>
                <p class="text-gray-500 text-sm"><?= htmlspecialchars($row['no_whatsapp']) ?></p>
                <?php if ($row['link_wa']): ?>
                <a href="<?= htmlspecialchars($row['link_wa']) ?>" target="_blank" class="inline-flex items-center gap-1 text-green-600 text-xs mt-2 hover:underline"><i class="fas fa-external-link-alt"></i>Buka Link</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="modal-container bg-white rounded-xl max-w-md w-full">
        <div class="modal-header flex items-center justify-between">
            <h3>Tambah Kontak</h3>
            <button onclick="closeModal('addModal')" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body space-y-4">
                <input type="hidden" name="action" value="add">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lembaga</label>
                <input type="text" name="lembaga" required placeholder="SMP/MA/PONPES" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="nama" required placeholder="Nama kontak" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No WhatsApp</label>
                <input type="text" name="no_whatsapp" required placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
                <div class="form-group">
                    <label>Link WA (opsional)</label>
                    <input type="url" name="link_wa" placeholder="https://wa.link/xxx">
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
            <h3>Edit Kontak</h3>
            <button onclick="closeModal('editModal')" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lembaga</label>
                <input type="text" name="lembaga" id="editLembaga" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="nama" id="editNama" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No WhatsApp</label>
                <input type="text" name="no_whatsapp" id="editNoWa" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </div>
                <div class="form-group">
                    <label>Link WA</label>
                    <input type="url" name="link_wa" id="editLinkWa">
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
        <h3 class="font-bold text-lg text-gray-800 mb-2">Hapus Kontak?</h3>
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
        document.getElementById('editLembaga').value = data.lembaga;
        document.getElementById('editNama').value = data.nama;
        document.getElementById('editNoWa').value = data.no_whatsapp;
        document.getElementById('editLinkWa').value = data.link_wa || '';
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
