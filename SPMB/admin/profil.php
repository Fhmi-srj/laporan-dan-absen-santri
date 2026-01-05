<?php
require_once '../api/config.php';
requireLogin();

$conn = getConnection();
$message = '';
$error = '';

$adminId = $_SESSION['admin_id'];

// Get current admin data
$stmt = $conn->prepare("SELECT id, username, nama FROM admin WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $nama = sanitize($conn, $_POST['nama']);
        $username = sanitize($conn, $_POST['username']);
        
        // Check if username already exists (for other users)
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $adminId);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            $stmt = $conn->prepare("UPDATE admin SET nama = ?, username = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama, $username, $adminId);
            
            if ($stmt->execute()) {
                $_SESSION['admin_nama'] = $nama;
                $_SESSION['admin_username'] = $username;
                $admin['nama'] = $nama;
                $admin['username'] = $username;
                $message = 'Profil berhasil diupdate!';
                logActivity('PROFILE_UPDATE', 'Mengubah profil admin');
            } else {
                $error = 'Gagal mengupdate profil!';
            }
        }
    }
    
    if ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Check current password
        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!password_verify($currentPassword, $result['password'])) {
            $error = 'Password saat ini salah!';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $adminId);
            
            if ($stmt->execute()) {
                $message = 'Password berhasil diubah!';
                logActivity('PASSWORD_CHANGE', 'Mengubah password admin');
            } else {
                $error = 'Gagal mengubah password!';
            }
        }
    }
}

$conn->close();

// Page config
$pageTitle = 'Profil Admin - SPMB';
$currentPage = 'profil';
?>
<?php include 'includes/header.php'; ?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Profil Admin</h2>
            <p class="text-gray-500 text-sm">Kelola informasi akun Anda</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="bg-primary/5 px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-primary"><i class="fas fa-user mr-2"></i>Informasi Profil</h3>
                </div>
                <form method="POST" class="p-4 space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($admin['nama']) ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                    </div>
                    
                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Password Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="bg-yellow-50 px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-yellow-700"><i class="fas fa-key mr-2"></i>Ubah Password</h3>
                </div>
                <form method="POST" class="p-4 space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_password">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                        <div class="relative">
                            <input type="password" name="current_password" required id="currentPw"
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                            <button type="button" onclick="togglePw('currentPw')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <div class="relative">
                            <input type="password" name="new_password" required id="newPw" minlength="6"
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                            <button type="button" onclick="togglePw('newPw')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" required id="confirmPw"
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                            <button type="button" onclick="togglePw('confirmPw')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 rounded-lg transition">
                        <i class="fas fa-key mr-2"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function togglePw(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>
