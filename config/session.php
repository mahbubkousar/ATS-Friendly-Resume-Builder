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

function setUserSession($userId, $fullname, $email) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_fullname'] = $fullname;
    $_SESSION['user_email'] = $email;
}

function destroyUserSession() {
    session_unset();
    session_destroy();
}
?>
