<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/environment.php';

define('GEMINI_API_KEY', (string) environmentValue('GEMINI_API_KEY', ''));
define('GEMINI_MODEL', 'gemini-2.0-flash-exp');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/');
define('GEMINI_TEMPERATURE', 0.7);
define('GEMINI_MAX_TOKENS', 1024);
define('GEMINI_TOP_K', 40);
define('GEMINI_TOP_P', 0.95);
