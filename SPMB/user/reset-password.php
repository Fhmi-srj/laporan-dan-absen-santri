<?php
// =============================================
// Reset Password via Token Link
// =============================================

require_once '../api/config.php';

$conn = getConnection();
$error = '';
$success = false;

// Get token from URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $error = 'Link tidak valid.';
}

// Validate token
$user = null;
if (!$error) {
    $stmt = $conn->prepare("SELECT id, nama, no_hp_wali FROM pendaftaran WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $error = 'Link sudah kadaluarsa atau tidak valid.';
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        $error = 'Password harus minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Update password and clear token
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE pendaftaran SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $updateStmt->bind_param("si", $hashedPassword, $user['id']);

        if ($updateStmt->execute()) {
            // Auto login
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Gagal menyimpan password baru.';
        }
        $updateStmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SPMB</title>
    <link href="../images/logo-pondok.png" rel="icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E67E22',
                        'primary-dark': '#D35400',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-orange-50 to-orange-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-key text-primary text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Buat Password Baru</h1>
            <?php if ($user): ?>
                <p class="text-gray-500 mt-2">Halo, <strong><?= htmlspecialchars($user['nama']) ?></strong></p>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-center">
                <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                <?php if (!$user): ?>
                    <p class="mt-2">
                        <a href="index.php" class="text-primary hover:underline">Kembali ke Login</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none"
                            placeholder="Minimal 6 karakter">
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirm" id="password_confirm" required minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none"
                            placeholder="Ulangi password">
                        <button type="button" onclick="togglePassword('password_confirm', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>Simpan Password Baru
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>