<?php
/**
 * Logout Handler
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/config.php';

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect ke login
header('Location: login.php');
exit;
