<?php
/**
 * Index / Dashboard
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

// Redirect ke halaman aktivitas sebagai halaman utama
header('Location: dashboard.php');
exit;
