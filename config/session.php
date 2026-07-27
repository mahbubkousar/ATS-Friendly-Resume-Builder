<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /ATS/login.php');
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
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_fullname'] = $fullname;
    $_SESSION['user_email'] = $email;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function destroyUserSession() {
    session_unset();
    session_destroy();
}

if (isLoggedIn() && isStateChangingRequest()) {
    requireCsrfToken();
}
?>
