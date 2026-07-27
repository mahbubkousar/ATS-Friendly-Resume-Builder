<?php
require_once __DIR__ . '/environment.php';

define('APP_ENV', strtolower((string) environmentValue('APP_ENV', 'production')));
define('DB_HOST', (string) environmentValue('DB_HOST', 'localhost'));
define('DB_PORT', (int) environmentValue('DB_PORT', '3306'));
define('DB_USER', (string) environmentValue('DB_USER', ''));
define('DB_PASS', (string) environmentValue('DB_PASS', ''));
define('DB_NAME', (string) environmentValue('DB_NAME', 'resumesync_db'));

if (!in_array(APP_ENV, ['development', 'test', 'production'], true)) {
    throw new RuntimeException('APP_ENV must be development, test, or production.');
}
if (DB_HOST === '' || DB_NAME === '' || DB_PORT < 1 || DB_PORT > 65535) {
    throw new RuntimeException('Database connection settings are invalid.');
}
if (APP_ENV === 'production' && (DB_USER === '' || DB_USER === 'root' || DB_PASS === '')) {
    throw new RuntimeException(
        'Production database credentials must use a dedicated, password-protected account.'
    );
}

function getDBConnection() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}

function closeDBConnection() {
    $conn = getDBConnection();
    if ($conn) {
        $conn->close();
    }
}
?>
