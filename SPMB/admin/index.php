<?php
require_once '../api/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password, nama FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_nama'] = $row['nama'];
            $_SESSION['admin_username'] = $row['username'];

            logActivity('LOGIN', 'Login berhasil');

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }

    $stmt->close();
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SPMB</title>
    <link href="../images/logo-pondok.png" rel="icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E67E22',
                        'primary-dark': '#D35400',
                        'primary-light': '#F39C12',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-primary-light p-6 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user-shield text-3xl text-white"></i>
                </div>
                <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                <p class="text-white/80 text-sm">SPMB Mambaul Huda</p>
            </div>

            <div class="p-6">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username" required
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                                placeholder="Masukkan username">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" required id="password"
                                class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                                placeholder="Masukkan password">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-lg transition transform hover:scale-[1.02] active:scale-100">
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="../index.php" class="text-sm text-gray-500 hover:text-primary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Website
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center text-gray-400 text-xs mt-4">
            © 2025 SPMB Mambaul Huda Pajomblangan
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
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