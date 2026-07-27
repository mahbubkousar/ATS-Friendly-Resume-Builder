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

function detectApplicationBasePath(): string
{
    $configuredDocumentRoot = (string) ($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($configuredDocumentRoot === '') {
        return '';
    }

    $documentRoot = realpath($configuredDocumentRoot);
    $applicationRoot = realpath(__DIR__ . '/..');

    if ($documentRoot === false || $applicationRoot === false) {
        return '';
    }

    $documentRoot = str_replace('\\', '/', $documentRoot);
    if ($documentRoot !== '/') {
        $documentRoot = rtrim($documentRoot, '/');
    }
    $applicationRoot = str_replace('\\', '/', $applicationRoot);
    if ($applicationRoot !== $documentRoot
        && strpos($applicationRoot, $documentRoot . '/') !== 0) {
        return '';
    }

    $relativePath = substr($applicationRoot, strlen($documentRoot));
    return '/' . trim($relativePath, '/');
}

$configuredPath = APP_URL !== ''
    ? (string) (parse_url(APP_URL, PHP_URL_PATH) ?? '')
    : detectApplicationBasePath();
define('APP_BASE_PATH', rtrim($configuredPath, '/'));

function buildApplicationUrl(string $path): string
{
    $path = '/' . ltrim($path, '/');
    if (APP_URL !== '') {
        return APP_URL . $path;
    }
    return APP_BASE_PATH . $path;
}
