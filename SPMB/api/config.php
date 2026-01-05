<?php
// =============================================
// Database Configuration - Local Development
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spmb_db');

// Create connection
function getConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// Start session with cookie that expires on browser close
session_set_cookie_params([
    'lifetime' => 0, // Expires when browser closes
    'path' => '/',
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// =============================================
// CSRF Protection
// =============================================

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

function validateCsrf()
{
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function requireCsrf()
{
    if (!validateCsrf()) {
        die('Invalid CSRF token. Please refresh the page and try again.');
    }
}

// =============================================
// Auth Functions
// =============================================

function isLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

// =============================================
// Helper Functions
// =============================================

function sanitize($conn, $input)
{
    return mysqli_real_escape_string($conn, trim($input));
}

function jsonResponse($success, $message, $data = null)
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function getSetting($conn, $key)
{
    $stmt = $conn->prepare("SELECT nilai FROM pengaturan WHERE kunci = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['nilai'] : null;
}

function formatRupiah($number)
{
    return 'Rp' . number_format($number, 0, ',', '.');
}

// =============================================
// Activity Log
// =============================================

function logActivity($action, $description = '')
{
    if (!isLoggedIn())
        return;

    $conn = getConnection();
    $adminId = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $conn->prepare("INSERT INTO activity_log (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $adminId, $action, $description, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>