<?php
/**
 * Index / Dashboard
 * Laporan Santri - PHP Murni
 */

require_once __DIR__ . '/functions.php';
requireLogin();

// Redirect ke halaman beranda sebagai halaman utama
header('Location: beranda.php');
exit;
