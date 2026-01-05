<?php
require_once '../api/config.php';

// Redirect if already logged in as user
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $phoneInput = sanitize($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Normalize phone number - try multiple formats
    $phone = $phoneInput;

    // Build possible phone formats to search
    $phoneFormats = [];

    // If starts with 0, also try +62 version
    if (str_starts_with($phone, '0')) {
        $phoneFormats[] = $phone; // 081234567xxx
        $phoneFormats[] = '+62' . substr($phone, 1); // +6281234567xxx
    }
    // If starts with +62, also try 0 version
    elseif (str_starts_with($phone, '+62')) {
        $phoneFormats[] = $phone; // +6281234567xxx
        $phoneFormats[] = '0' . substr($phone, 3); // 081234567xxx
    }
    // Raw number (no 0 or +62), try both
    else {
        $phoneFormats[] = $phone; // 81234567xxx
        $phoneFormats[] = '0' . $phone; // 081234567xxx
        $phoneFormats[] = '+62' . $phone; // +6281234567xxx
    }

    // Search for any matching format
    $row = null;
    foreach ($phoneFormats as $tryPhone) {
        $stmt = $conn->prepare("SELECT id, nama, no_hp_wali, password, status FROM pendaftaran WHERE no_hp_wali = ?");
        $stmt->bind_param("s", $tryPhone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($found = $result->fetch_assoc()) {
            $row = $found;
            break;
        }
        $stmt->close();
    }

    if ($row) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_nama'] = $row['nama'];
            $_SESSION['user_phone'] = $row['no_hp_wali'];
            $_SESSION['user_status'] = $row['status'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Nomor WA tidak terdaftar!';
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pendaftar - SPMB</title>
    <link href="../images/logo-ma.png" rel="icon">
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
                    <i class="fas fa-user-graduate text-3xl text-white"></i>
                </div>
                <h1 class="text-xl font-bold text-white">Portal Pendaftar</h1>
                <p class="text-white/80 text-sm">Login untuk melihat status pendaftaran</p>
            </div>

            <div class="p-6">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Nomor WhatsApp</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <div class="flex">
                                <span
                                    class="bg-gray-100 border border-r-0 border-gray-300 pl-10 pr-2 py-3 rounded-l-lg text-gray-600 text-sm font-medium flex items-center">
                                    +62
                                </span>
                                <input type="text" name="phone" required placeholder="8xxxxxxxxxx" minlength="9"
                                    maxlength="13"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')">
                            </div>
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

                <div class="mt-4 text-center">
                    <button onclick="openLupaPasswordModal()" class="text-primary text-sm font-medium hover:underline">
                        <i class="fas fa-key mr-1"></i>Lupa Password?
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-gray-500 text-sm">
                        Belum punya akun?
                        <a href="../pendaftaran.php" class="text-primary font-medium hover:underline">Daftar di sini</a>
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 p-4 text-center border-t">
                <a href="../index.php" class="text-sm text-gray-500 hover:text-primary">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Website
                </a>
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

        // Lupa Password Modal
        function openLupaPasswordModal() {
            document.getElementById('lupaPasswordModal').classList.remove('hidden');
        }

        function closeLupaPasswordModal() {
            document.getElementById('lupaPasswordModal').classList.add('hidden');
            document.getElementById('lupaPasswordForm').reset();
            document.getElementById('lupaPasswordResult').innerHTML = '';
        }

        async function submitLupaPassword(event) {
            event.preventDefault();
            const form = document.getElementById('lupaPasswordForm');
            const btn = form.querySelector('button[type="submit"]');
            const resultDiv = document.getElementById('lupaPasswordResult');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
            resultDiv.innerHTML = '';

            const formData = new FormData(form);
            
            try {
                const response = await fetch('../api/lupa-password.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm">
                            <i class="fas fa-check-circle mr-2"></i>${data.message}
                        </div>
                    `;
                    form.reset();
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <i class="fas fa-exclamation-circle mr-2"></i>${data.message}
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i>Terjadi kesalahan. Silakan coba lagi.
                    </div>
                `;
            }
            
            btn.disabled = false;
            btn.innerHTML = '<i class="fab fa-whatsapp mr-2"></i>Kirim via WhatsApp';
        }
    </script>

    <!-- Lupa Password Modal -->
    <div id="lupaPasswordModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-primary-light p-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white"><i class="fas fa-key mr-2"></i>Lupa Password</h3>
                <button onclick="closeLupaPasswordModal()" class="text-white/80 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <p class="text-gray-600 text-sm mb-4">
                    Masukkan nomor WhatsApp yang terdaftar. Password baru akan dikirim melalui WhatsApp.
                </p>
                <form id="lupaPasswordForm" onsubmit="submitLupaPassword(event)" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Nomor WhatsApp</label>
                        <div class="flex">
                            <span class="bg-gray-100 border border-r-0 border-gray-300 px-3 py-3 rounded-l-lg text-gray-600 text-sm font-medium flex items-center">
                                +62
                            </span>
                            <input type="text" name="no_hp" required placeholder="8xxxxxxxxxx" minlength="9" maxlength="13"
                                class="w-full px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')">
                        </div>
                    </div>
                    <div id="lupaPasswordResult"></div>
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition">
                        <i class="fab fa-whatsapp mr-2"></i>Kirim via WhatsApp
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>