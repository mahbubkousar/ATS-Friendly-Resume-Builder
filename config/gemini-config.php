<?php
// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? '');
define('GEMINI_MODEL', 'gemini-2.0-flash-exp');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/');
define('GEMINI_TEMPERATURE', 0.7);
define('GEMINI_MAX_TOKENS', 1024);
define('GEMINI_TOP_K', 40);
define('GEMINI_TOP_P', 0.95);
