<?php
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Method not allowed';
    exit();
}

destroyUserSession();

header('Location: login.php');
exit();
?>
