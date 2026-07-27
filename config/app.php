<?php

require_once __DIR__ . '/environment.php';

$configuredAppUrl = rtrim((string) environmentValue('APP_URL', ''), '/');
if ($configuredAppUrl !== '') {
    $parts = parse_url($configuredAppUrl);
    $validAppUrl = filter_var($configuredAppUrl, FILTER_VALIDATE_URL)
        && is_array($parts)
        && isset($parts['scheme'], $parts['host'])
        && in_array(strtolower($parts['scheme']), ['http', 'https'], true)
        && !isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment']);

    if (!$validAppUrl) {
        throw new RuntimeException('APP_URL must be an absolute HTTP or HTTPS URL.');
    }
}

define('APP_URL', $configuredAppUrl);
define('APP_FALLBACK_PATH', '/ATS');

function buildApplicationUrl(string $path): string
{
    $path = '/' . ltrim($path, '/');
    if (APP_URL !== '') {
        return APP_URL . $path;
    }
    return APP_FALLBACK_PATH . $path;
}
