<?php
/**
 * Konfigurasi Database dan Session
 * Laporan Santri - PHP Murni
 */

// Konfigurasi Error Reporting (Matikan di Production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database Utama
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'diantar2_absen');
define('DB_USER', 'root');
define('DB_PASS', '');

// Konfigurasi Database SPMB
define('SPMB_DB_HOST', '127.0.0.1');
define('SPMB_DB_PORT', '3306');
define('SPMB_DB_NAME', 'spmb_db');
define('SPMB_DB_USER', 'root');
define('SPMB_DB_PASS', '');

// Konfigurasi Aplikasi
define('APP_NAME', 'MAMBAUL-HUDA');
define('APP_URL', 'http://localhost/Laporan Santri/Laporan Santri');
define('KIOSK_PASSWORD', '1234'); // Password untuk ganti jadwal di kiosk
define('BASE_PATH', __DIR__);

// Konfigurasi WhatsApp API
define('WA_API_URL', 'http://serverwa.hello-inv.com/send-message');
define('WA_API_KEY', 'VbM1epmqMKqrztVrWpd1YquAboWWFa');
define('WA_SENDER', '6282131871383');

// Konfigurasi Upload
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Koneksi Database Utama
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Koneksi Database SPMB
function getSPMBDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . SPMB_DB_HOST . ";port=" . SPMB_DB_PORT . ";dbname=" . SPMB_DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, SPMB_DB_USER, SPMB_DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Koneksi database SPMB gagal: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Auth Helper
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser()
{
    if (!isLoggedIn())
        return null;

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin()
{
    requireLogin();
    $user = getCurrentUser();
    if ($user['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

// Flash Message
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
