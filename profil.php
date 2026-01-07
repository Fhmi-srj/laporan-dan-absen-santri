<?php
/**
 * Profile Page
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDB();
$flash = getFlash();
$pageTitle = 'Profile';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_data') {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['phone'] ?? null,
            $_POST['address'] ?? null,
            $user['id']
        ]);
        redirectWith('profil.php', 'success', 'Data profil berhasil diperbarui!');
    }

    if ($action === 'update_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            redirectWith('profil.php', 'error', 'Password saat ini salah!');
        }

        // Check confirmation
        if ($newPassword !== $confirmPassword) {
            redirectWith('profil.php', 'error', 'Konfirmasi password tidak cocok!');
        }

        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
        redirectWith('profil.php', 'success', 'Password berhasil diperbarui!');
    }

    if ($action === 'update_foto') {
        if (!empty($_FILES['foto']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                redirectWith('profil.php', 'error', 'Format file tidak didukung!');
            }

            $filename = 'user_' . $user['id'] . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/profiles/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Delete old foto if exists
            if ($user['foto'] && $user['foto'] !== 'profile.jpg') {
                $oldFile = $uploadDir . $user['foto'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                $stmt = $pdo->prepare("UPDATE users SET foto = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$filename, $user['id']]);
                redirectWith('profil.php', 'success', 'Foto profil berhasil diperbarui!');
            } else {
                redirectWith('profil.php', 'error', 'Gagal mengupload foto!');
            }
        }
    }
}

// Refresh user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$user = $stmt->fetch();

$roleLabels = [
    'admin' => 'Administrator',
    'karyawan' => 'Karyawan',
    'pengurus' => 'Pengurus',
    'guru' => 'Guru',
    'keamanan' => 'Keamanan',
    'kesehatan' => 'Kesehatan'
];
?>
<?php include __DIR__ . '/include/header.php'; ?>
<?php include __DIR__ . '/include/sidebar.php'; ?>

<style>
    .profile-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #a78bfa 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.3);
        object-fit: cover;
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .profile-role {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        display: inline-block;
    }
</style>

<div class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <img src="<?= $user['foto'] && $user['foto'] !== 'profile.jpg' ? 'uploads/profiles/' . e($user['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=8659F1&color=fff&size=100' ?>"
                alt="Profile" class="profile-avatar">
            <div>
                <div class="profile-name">
                    <?= e($user['name']) ?>
                </div>
                <div class="opacity-75 mb-2">
                    <?= e($user['email']) ?>
                </div>
                <span class="profile-role">
                    <?= $roleLabels[$user['role']] ?? ucfirst($user['role']) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Edit Profile Form -->
        <div class="col-lg-6">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="update_data">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                        <small class="text-muted">Email tidak dapat diubah</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control"
                            rows="3"><?= e($user['address'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password & Photo -->
        <div class="col-lg-6">
            <!-- Change Photo -->
            <div class="card-custom p-4 mb-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-camera me-2"></i>Ubah Foto</h5>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_foto">
                    <div class="mb-3">
                        <style>
                            .photo-upload-wrapper-profile {
                                border: 2px dashed #e2e8f0;
                                border-radius: 12px;
                                padding: 20px;
                                text-align: center;
                                background: #f8fafc;
                                transition: all 0.3s;
                            }

                            .photo-upload-wrapper-profile:hover {
                                border-color: var(--primary-color);
                                background: #f1f5f9;
                            }

                            .photo-upload-wrapper-profile.has-preview {
                                border-style: solid;
                                border-color: #10b981;
                                background: #ecfdf5;
                            }

                            .photo-upload-buttons-profile {
                                display: flex;
                                gap: 10px;
                                justify-content: center;
                                flex-wrap: wrap;
                            }

                            .btn-photo-upload-profile {
                                display: inline-flex;
                                align-items: center;
                                gap: 8px;
                                padding: 10px 20px;
                                border-radius: 10px;
                                font-weight: 600;
                                font-size: 0.85rem;
                                cursor: pointer;
                                transition: all 0.2s;
                                border: none;
                            }

                            .btn-camera-profile {
                                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                                color: white;
                            }

                            .btn-camera-profile:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
                            }

                            .btn-file-profile {
                                background: white;
                                color: #475569;
                                border: 1px solid #e2e8f0 !important;
                            }

                            .btn-file-profile:hover {
                                background: #f1f5f9;
                            }

                            .photo-preview-container-profile {
                                position: relative;
                                display: inline-block;
                                margin-top: 15px;
                            }

                            .photo-preview-container-profile img {
                                max-width: 100%;
                                max-height: 180px;
                                border-radius: 10px;
                                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                            }

                            .btn-remove-photo-profile {
                                position: absolute;
                                top: -8px;
                                right: -8px;
                                width: 28px;
                                height: 28px;
                                border-radius: 50%;
                                background: #ef4444;
                                color: white;
                                border: 2px solid white;
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                            }

                            @media (max-width: 768px) {
                                .photo-upload-buttons-profile {
                                    flex-direction: column;
                                }

                                .btn-photo-upload-profile {
                                    width: 100%;
                                    justify-content: center;
                                }
                            }
                        </style>
                        <div class="photo-upload-wrapper-profile" id="wrapper_foto_profile">
                            <input type="file" name="foto" id="input_foto_profile" class="d-none" accept="image/*"
                                required>
                            <input type="file" id="camera_foto_profile" class="d-none" accept="image/*"
                                capture="environment">
                            <div class="photo-upload-buttons-profile" id="buttons_foto_profile">
                                <button type="button" class="btn-photo-upload-profile btn-camera-profile"
                                    onclick="document.getElementById('camera_foto_profile').click()">
                                    <i class="fas fa-camera"></i> Ambil Foto
                                </button>
                                <button type="button" class="btn-photo-upload-profile btn-file-profile"
                                    onclick="document.getElementById('input_foto_profile').click()">
                                    <i class="fas fa-folder-open"></i> Pilih File
                                </button>
                            </div>
                            <div class="photo-preview-container-profile d-none" id="container_foto_profile">
                                <img id="preview_foto_profile" alt="Preview">
                                <button type="button" class="btn-remove-photo-profile" onclick="removePhotoProfile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Format: JPG, PNG, GIF. Max 2MB</small>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-upload me-1"></i> Upload Foto
                    </button>
                </form>
                <script>
                    // Handle photo selection for profile
                    document.getElementById('input_foto_profile').addEventListener('change', function () {
                        handlePhotoProfile(this);
                    });
                    document.getElementById('camera_foto_profile').addEventListener('change', function () {
                        // Copy file from camera to main input
                        if (this.files && this.files[0]) {
                            let mainInput = document.getElementById('input_foto_profile');
                            let dataTransfer = new DataTransfer();
                            dataTransfer.items.add(this.files[0]);
                            mainInput.files = dataTransfer.files;
                            handlePhotoProfile(mainInput);
                        }
                    });

                    function handlePhotoProfile(input) {
                        if (input.files && input.files[0]) {
                            let reader = new FileReader();
                            reader.onload = function (e) {
                                document.getElementById('preview_foto_profile').src = e.target.result;
                                document.getElementById('container_foto_profile').classList.remove('d-none');
                                document.getElementById('buttons_foto_profile').classList.add('d-none');
                                document.getElementById('wrapper_foto_profile').classList.add('has-preview');
                            };
                            reader.readAsDataURL(input.files[0]);
                        }
                    }

                    function removePhotoProfile() {
                        document.getElementById('input_foto_profile').value = '';
                        document.getElementById('camera_foto_profile').value = '';
                        document.getElementById('preview_foto_profile').src = '';
                        document.getElementById('container_foto_profile').classList.add('d-none');
                        document.getElementById('buttons_foto_profile').classList.remove('d-none');
                        document.getElementById('wrapper_foto_profile').classList.remove('has-preview');
                    }
                </script>
            </div>

            <!-- Change Password -->
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-key me-2"></i>Ubah Password</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="update_password">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-lock me-1"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>