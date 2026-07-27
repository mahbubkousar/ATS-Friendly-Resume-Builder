<?php

require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Emits the common JSON envelope used by API failures.
 */
function sendApiError(
    string $message,
    int $statusCode = 400,
    string $messageKey = 'message'
): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        $messageKey => $message,
    ]);
    exit;
}

/**
 * Enforces an endpoint's HTTP verb and advertises the accepted method.
 */
function requireApiMethod(string $allowedMethod, string $messageKey = 'message'): void
{
    $allowedMethod = strtoupper($allowedMethod);
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === $allowedMethod) {
        return;
    }

    header('Allow: ' . $allowedMethod);
    sendApiError('Method not allowed', 405, $messageKey);
}

/**
 * Returns the authenticated API user without redirecting JSON clients to HTML.
 */
function requireApiUser(string $messageKey = 'message'): array
{
    if (!function_exists('isLoggedIn') || !isLoggedIn()) {
        sendApiError('User not authenticated', 401, $messageKey);
    }

    return getCurrentUser();
}

set_exception_handler(static function (Throwable $exception): void {
    error_log('Unhandled API error: ' . $exception->getMessage());
    sendApiError('Unexpected server error', 500);
});
