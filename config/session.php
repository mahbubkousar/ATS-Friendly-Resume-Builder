<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/app.php';

const SESSION_IDLE_TIMEOUT = 1800;
const SESSION_ABSOLUTE_TIMEOUT = 43200;
const SESSION_REGENERATION_INTERVAL = 900;

function isHttpsRequest() {
    return (
        (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ||
        (int)($_SERVER['SERVER_PORT'] ?? 0) === 443
    );
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string)SESSION_ABSOLUTE_TIMEOUT);

    session_name('resumesync_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => APP_BASE_PATH . '/',
        'domain' => '',
        'secure' => isHttpsRequest(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . buildApplicationUrl('login.php'));
        exit();
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'fullname' => $_SESSION['user_fullname'] ?? null,
        'email' => $_SESSION['user_email'] ?? null
    ];
}

function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function isStateChangingRequest() {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    return in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);
}

function requireCsrfToken() {
    $providedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($providedToken) ||
        !is_string($sessionToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $providedToken)
    ) {
        http_response_code(403);

        $scriptDirectory = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
        if ($scriptDirectory === 'api') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or missing CSRF token'
            ]);
        } else {
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Invalid or missing CSRF token';
        }

        exit();
    }
}

function setUserSession($userId, $fullname, $email) {
    session_regenerate_id(true);

    $now = time();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_fullname'] = $fullname;
    $_SESSION['user_email'] = $email;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['session_created_at'] = $now;
    $_SESSION['last_activity_at'] = $now;
    $_SESSION['last_regenerated_at'] = $now;
}

function destroyUserSession() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax'
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function enforceSessionLifetime() {
    if (!isLoggedIn()) {
        return;
    }

    $now = time();

    if (
        empty($_SESSION['session_created_at']) ||
        empty($_SESSION['last_activity_at']) ||
        empty($_SESSION['last_regenerated_at'])
    ) {
        session_regenerate_id(true);
        $_SESSION['session_created_at'] = $now;
        $_SESSION['last_activity_at'] = $now;
        $_SESSION['last_regenerated_at'] = $now;
        return;
    }

    $idleTime = $now - (int)$_SESSION['last_activity_at'];
    $sessionAge = $now - (int)$_SESSION['session_created_at'];

    if ($idleTime > SESSION_IDLE_TIMEOUT || $sessionAge > SESSION_ABSOLUTE_TIMEOUT) {
        destroyUserSession();
        return;
    }

    if (($now - (int)$_SESSION['last_regenerated_at']) >= SESSION_REGENERATION_INTERVAL) {
        session_regenerate_id(true);
        $_SESSION['last_regenerated_at'] = $now;
    }

    $_SESSION['last_activity_at'] = $now;
}

enforceSessionLifetime();

if (isLoggedIn() && isStateChangingRequest()) {
    requireCsrfToken();
}
?>
