<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$flash = getFlash();

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $deviceToken = $_POST['device_token'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } elseif (empty($deviceToken)) {
        $error = 'Device token tidak valid. Silakan refresh halaman.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Cek device fingerprint
            $stmtDevice = $pdo->prepare("SELECT * FROM user_devices WHERE user_id = ?");
            $stmtDevice->execute([$user['id']]);
            $userDevice = $stmtDevice->fetch();

            if (!$userDevice) {
                // Pertama kali login, daftarkan device
                $stmtInsert = $pdo->prepare("INSERT INTO user_devices (user_id, device_fingerprint, device_name, last_used_at, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW(), NOW())");
                $stmtInsert->execute([$user['id'], $deviceToken, $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown']);
            } else {
                // Update device fingerprint
                $stmtUpdate = $pdo->prepare("UPDATE user_devices SET device_fingerprint = ?, device_name = ?, last_used_at = NOW(), updated_at = NOW() WHERE user_id = ?");
                $stmtUpdate->execute([$deviceToken, $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', $user['id']]);
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['device_token'] = $deviceToken;

            header('Location: index.php');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="logo-pondok.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@aspect/fingerprint@0.3.0/dist/fingerprint.min.js"></script>
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        }

        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
        }

        .login-left h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
            z-index: 1;
        }

        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            text-align: center;
            z-index: 1;
        }

        .login-left .logo-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            z-index: 1;
        }

        .login-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            padding: 40px;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
        }

        .login-form h2 {
            color: #334155;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-form p {
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(134, 89, 241, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(134, 89, 241, 0.4);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        @media (max-width: 768px) {
            .login-left {
                display: none;
            }

            .login-right {
                padding: 20px;
            }
        }

        .quick-login-btn {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .quick-login-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-left">
            <img src="logo-pondok.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 1.5rem;">
            <h1><?= APP_NAME ?></h1>
            <p>Sistem Monitoring Aktivitas Santri</p>
        </div>
        <div class="login-right">
            <div class="login-form">
                <h2>Selamat Datang!</h2>
                <p>Silakan login untuk melanjutkan</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <input type="hidden" name="device_token" id="device_token" value="">

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Masukkan email" required
                            value="<?= e($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>

                <!-- Quick Login Buttons (Development) -->
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed #e2e8f0;">
                    <p
                        style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; text-align: center;">
                        <i class="fas fa-bolt me-1"></i> Quick Login (Demo)
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                        <button type="button" class="quick-login-btn" onclick="autoFill('admin')">
                            <i class="fas fa-user-shield"></i> Admin
                        </button>
                        <button type="button" class="quick-login-btn" onclick="autoFill('karyawan')">
                            <i class="fas fa-user-tie"></i> Karyawan
                        </button>
                        <button type="button" class="quick-login-btn" onclick="autoFill('pengurus')">
                            <i class="fas fa-user-cog"></i> Pengurus
                        </button>
                        <button type="button" class="quick-login-btn" onclick="autoFill('guru')">
                            <i class="fas fa-chalkboard-teacher"></i> Guru
                        </button>
                        <button type="button" class="quick-login-btn" onclick="autoFill('keamanan')">
                            <i class="fas fa-shield-alt"></i> Keamanan
                        </button>
                        <button type="button" class="quick-login-btn" onclick="autoFill('kesehatan')">
                            <i class="fas fa-heartbeat"></i> Kesehatan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Generate device fingerprint
            function generateFingerprint() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('fingerprint', 2, 2);

                const data = [
                    navigator.userAgent,
                    navigator.language,
                    screen.width + 'x' + screen.height,
                    new Date().getTimezoneOffset(),
                    canvas.toDataURL()
                ].join('|');

                // Simple hash
                let hash = 0;
                for (let i = 0; i < data.length; i++) {
                    const char = data.charCodeAt(i);
                    hash = ((hash << 5) - hash) + char;
                    hash = hash & hash;
                }

                // Format as UUID-like string
                const hexHash = Math.abs(hash).toString(16).padStart(8, '0');
                return hexHash.slice(0, 8) + '-' +
                    hexHash.slice(0, 4) + '-' +
                    '4' + hexHash.slice(1, 4) + '-' +
                    'a' + hexHash.slice(1, 4) + '-' +
                    hexHash.slice(0, 12).padEnd(12, '0');
            }

            document.getElementById('device_token').value = generateFingerprint();
        });

        // Auto-fill login credentials for quick testing
        function autoFill(role) {
            const credentials = {
                'admin': { email: 'admin@mambaul-huda.com', password: 'password' },
                'karyawan': { email: 'karyawan@mambaul-huda.com', password: 'password' },
                'pengurus': { email: 'pengurus@mambaul-huda.com', password: 'password' },
                'guru': { email: 'guru@mambaul-huda.com', password: 'password' },
                'keamanan': { email: 'keamanan@mambaul-huda.com', password: 'password' },
                'kesehatan': { email: 'kesehatan@mambaul-huda.com', password: 'password' }
            };

            if (credentials[role]) {
                document.getElementById('email').value = credentials[role].email;
                document.getElementById('password').value = credentials[role].password;
            }
        }
    </script>
</body>

</html>