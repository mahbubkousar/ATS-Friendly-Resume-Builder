<?php

putenv('APP_ENV=test');
putenv('GEMINI_API_KEY=test-key');
putenv('GEMINI_MODEL=test-model');

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../includes/request-validation.php';
require_once __DIR__ . '/../config/gemini.php';
require_once __DIR__ . '/../services/DashboardService.php';
require_once __DIR__ . '/../services/AtsAnalysisService.php';

assertSameValue(
    'Example',
    requiredStringField(['name' => ' Example '], 'name', 'Name', 20),
    'Required strings should be trimmed.'
);
assertSameValue(
    42,
    positiveIntegerField(['id' => '42'], 'id', 'ID'),
    'Positive integer validation should normalize numeric strings.'
);
assertThrowsValidation(
    fn() => positiveIntegerField(['id' => '../1'], 'id', 'ID'),
    'Invalid identifiers must be rejected.'
);
assertThrowsValidation(
    fn() => optionalHttpUrlField(['url' => 'javascript:alert(1)'], 'url', 'URL'),
    'Unsafe URL schemes must be rejected.'
);
assertSameValue(
    '2026-08-05',
    dateField(['date' => '2026-08-05'], 'date', 'Date', true),
    'Valid dates should be accepted.'
);
assertThrowsValidation(
    fn() => dateField(['date' => '2026-02-31'], 'date', 'Date', true),
    'Impossible dates must be rejected.'
);

$geminiResponse = json_encode([
    'candidates' => [[
        'content' => ['parts' => [['text' => '{"score":95}']]],
        'finishReason' => 'STOP',
    ]],
]);
assertSameValue(
    ['success' => true, 'text' => '{"score":95}'],
    parseGeminiResponse($geminiResponse),
    'Gemini text responses should be normalized.'
);
assertSameValue(
    ['score' => 95],
    decodeGeminiJsonObject("```json\n{\"score\":95}\n```", ['score']),
    'Fenced AI JSON should be decoded and schema-checked.'
);
assertSameValue(
    null,
    decodeGeminiJsonObject('{"value":1}', ['required']),
    'AI JSON missing required keys must be rejected.'
);
assertSameValue(
    null,
    decodeGeminiJsonObject('{"items":"not-a-list"}', ['items' => 'list']),
    'AI JSON with invalid field types must be rejected.'
);
assertSameValue(
    null,
    decodeGeminiJsonObject('not json', []),
    'Non-JSON AI output must be rejected.'
);
assertTrueValue(
    str_contains(buildAnalysisPrompt('resume', 'job'), 'keywords_found'),
    'ATS prompts must request the validated response schema.'
);

$dashboard = (new DashboardService(null))->buildViewModel([
    'id' => 7,
    'fullname' => 'Test User',
    'email' => 'test@example.com',
]);
assertSameValue('Test User', $dashboard['userProfile']['full_name'], 'Dashboard fallback name failed.');
assertSameValue(0, $dashboard['applicationStats']['total'], 'Empty dashboard statistics failed.');

$root = dirname(__DIR__);
assertTrueValue(is_file($root . '/resumesync_db_structure.sql'), 'Canonical schema is missing.');
assertTrueValue(is_file($root . '/database_seed.sql'), 'Synthetic seed file is missing.');
assertTrueValue(!is_file($root . '/create_database.sql'), 'Conflicting legacy schema still exists.');
assertTrueValue(is_file($root . '/api/mark-notification-read.php'), 'Notification endpoint is missing.');
assertTrueValue(is_file($root . '/api/ai-consent.php'), 'AI consent endpoint is missing.');
assertTrueValue(!is_file($root . '/config/gemini-config.php'), 'Duplicate AI configuration still exists.');

finishTests();
