<?php
/**
 * Logout Handler
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Get user ID before destroying session
$userId = $_SESSION['user_id'] ?? null;

// Clear remember token
if ($userId) {
    clearRememberToken($userId);
}

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect ke login
header('Location: masuk.php');
exit;

