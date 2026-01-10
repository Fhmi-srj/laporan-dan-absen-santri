<?php
/**
 * QZ Tray Signing API
 * Signs print requests using demo private key
 */

// Allow CORS for localhost
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain');

// Path to private key
$privateKeyPath = __DIR__ . '/../assets/certs/private-key.pem';

// Get the data to sign from request
$toSign = file_get_contents('php://input');

if (empty($toSign)) {
    http_response_code(400);
    echo 'No data to sign';
    exit;
}

// Check if private key exists
if (!file_exists($privateKeyPath)) {
    http_response_code(500);
    echo 'Private key not found';
    exit;
}

// Read private key
$privateKey = file_get_contents($privateKeyPath);

if (!$privateKey) {
    http_response_code(500);
    echo 'Cannot read private key';
    exit;
}

// Create signature using SHA512 with RSA
$pkeyId = openssl_pkey_get_private($privateKey);

if (!$pkeyId) {
    http_response_code(500);
    echo 'Invalid private key: ' . openssl_error_string();
    exit;
}

$signature = '';
$success = openssl_sign($toSign, $signature, $pkeyId, OPENSSL_ALGO_SHA512);

if (!$success) {
    http_response_code(500);
    echo 'Signing failed: ' . openssl_error_string();
    exit;
}

// Return base64 encoded signature
echo base64_encode($signature);
