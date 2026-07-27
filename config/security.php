<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');

if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'");

    $scriptDirectory = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
    if ($scriptDirectory === 'api') {
        header('Cache-Control: no-store, max-age=0');
        header('Pragma: no-cache');
    }

    $httpsEnabled = !empty($_SERVER['HTTPS'])
        && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    if ($httpsEnabled) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
