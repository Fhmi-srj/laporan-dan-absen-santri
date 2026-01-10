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
    $rememberMe = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } elseif (empty($deviceToken)) {
        $error = 'Device token tidak valid. Silakan refresh halaman.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
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

            // Log login activity
            logActivity('LOGIN', 'users', $user['id'], $user['name'], null, null, 'Pengguna berhasil masuk');

            // Generate remember token if checkbox is checked
            if ($rememberMe) {
                generateRememberToken($user['id']);
            }

            header('Location: index.php?fresh_login=1');
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

        /* Mobile Header for Branding */
        .mobile-header {
            display: none;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .mobile-header::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -80px;
            right: -80px;
            animation: float 6s ease-in-out infinite;
        }

        .mobile-header::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -60px;
            left: -60px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .mobile-header img {
            width: 80px;
            height: auto;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .mobile-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .mobile-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 768px) {
            body {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            }

            .login-container {
                flex-direction: column;
                min-height: 100vh;
            }

            .login-left {
                display: none;
            }

            .mobile-header {
                display: block;
            }

            .login-right {
                flex: 1;
                padding: 0;
                background: transparent;
                display: flex;
                align-items: flex-start;
                padding-top: 0;
            }

            .login-form {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 24px 24px 0 0;
                padding: 32px 24px 40px;
                margin-top: -20px;
                box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
                max-width: 100%;
                width: 100%;
                min-height: calc(100vh - 180px);
            }

            .login-form h2 {
                font-size: 1.5rem;
                text-align: center;
                margin-bottom: 0.25rem;
            }

            .login-form>p {
                text-align: center;
                margin-bottom: 1.5rem;
            }

            .form-group input {
                padding: 16px;
                font-size: 1rem;
                border-radius: 12px;
            }

            .btn-login {
                padding: 16px;
                font-size: 1rem;
                border-radius: 12px;
                margin-top: 0.5rem;
            }

            .alert {
                border-radius: 12px;
            }
        }

        /* Extra small devices */
        @media (max-width: 380px) {
            .mobile-header {
                padding: 30px 15px;
            }

            .mobile-header img {
                width: 60px;
            }

            .mobile-header h1 {
                font-size: 1.25rem;
            }

            .login-form {
                padding: 24px 20px 32px;
            }
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 5px;
            font-size: 1rem;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .remember-me {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            cursor: pointer;
            font-size: 0.9rem;
            color: #64748b;
            gap: 10px;
            user-select: none;
            margin-top: -0.5rem;
            margin-bottom: 0;
            text-transform: none;
            letter-spacing: normal;
        }

        .remember-me input[type="checkbox"] {
            display: none;
        }

        .remember-me .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            background: #f8fafc;
            flex-shrink: 0;
        }

        .remember-me .checkmark i {
            font-size: 12px;
            color: white;
            opacity: 0;
            transform: scale(0);
            transition: all 0.2s ease;
        }

        .remember-me input[type="checkbox"]:checked+.checkmark {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .remember-me input[type="checkbox"]:checked+.checkmark i {
            opacity: 1;
            transform: scale(1);
        }

        .remember-me:hover .checkmark {
            border-color: var(--primary-color);
        }

        .remember-me span.label-text {
            font-weight: 500;
            transition: color 0.2s;
        }

        .remember-me:hover span.label-text {
            color: var(--primary-color);
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

        <!-- Mobile Header (visible only on mobile) -->
        <div class="mobile-header">
            <img src="logo-pondok.png" alt="Logo">
            <h1>
                <?= APP_NAME ?>
            </h1>
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
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Masukkan password"
                                required>
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="remember-me">
                            <input type="checkbox" name="remember_me" id="remember_me">
                            <span class="checkmark"><i class="fas fa-check"></i></span>
                            <span class="label-text">Ingat Saya</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>


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

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>