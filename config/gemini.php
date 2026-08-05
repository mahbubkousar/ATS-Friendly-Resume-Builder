<?php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/environment.php';

define('GEMINI_API_KEY', (string) environmentValue('GEMINI_API_KEY', ''));
define('GEMINI_MODEL_NAME', (string) environmentValue('GEMINI_MODEL', ''));
define('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta/models/');
define('GEMINI_CONNECT_TIMEOUT', 10);
define('GEMINI_REQUEST_TIMEOUT', 60);
define('GEMINI_MAX_PROMPT_BYTES', 200000);
define('GEMINI_MAX_REQUEST_BYTES', 8000000);
define('GEMINI_MAX_RETRIES', 2);

function geminiIsConfigured(): bool
{
    return GEMINI_API_KEY !== ''
        && GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE'
        && preg_match('/^[A-Za-z0-9._-]{1,100}$/', GEMINI_MODEL_NAME) === 1;
}

function callGeminiAPI($prompt, $systemInstruction = ''): array
{
    if (!is_string($prompt) || !is_string($systemInstruction)) {
        return ['success' => false, 'error' => 'Invalid AI request content.'];
    }
    if (strlen($prompt) + strlen($systemInstruction) > GEMINI_MAX_PROMPT_BYTES) {
        return ['success' => false, 'error' => 'AI request content is too large.'];
    }

    $fullPrompt = $systemInstruction !== ''
        ? $systemInstruction . "\n\n" . $prompt
        : $prompt;

    return callGeminiParts(
        [['text' => $fullPrompt]],
        [
            'temperature' => 0.4,
            'topK' => 32,
            'topP' => 1,
            'maxOutputTokens' => 4096,
        ]
    );
}

function callGeminiParts(array $parts, array $generationConfig = []): array
{
    if (!geminiIsConfigured()) {
        return ['success' => false, 'error' => 'AI service is not configured.'];
    }

    $payload = [
        'contents' => [['parts' => $parts]],
        'generationConfig' => $generationConfig,
    ];
    $jsonPayload = json_encode($payload);
    if ($jsonPayload === false || strlen($jsonPayload) > GEMINI_MAX_REQUEST_BYTES) {
        return ['success' => false, 'error' => 'Unable to prepare AI request.'];
    }

    $url = GEMINI_API_BASE . rawurlencode(GEMINI_MODEL_NAME)
        . ':generateContent?key=' . rawurlencode(GEMINI_API_KEY);

    for ($attempt = 0; $attempt <= GEMINI_MAX_RETRIES; $attempt++) {
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_CONNECTTIMEOUT => GEMINI_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => GEMINI_REQUEST_TIMEOUT,
        ]);

        $response = curl_exec($handle);
        $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $transportError = curl_error($handle);
        curl_close($handle);

        if ($transportError === '' && $httpCode === 200 && is_string($response)) {
            return parseGeminiResponse($response);
        }

        $retryable = $transportError !== '' || $httpCode === 429 || $httpCode >= 500;
        error_log(
            'Gemini request failed (attempt ' . ($attempt + 1) . ', status '
            . $httpCode . ')' . ($transportError !== '' ? ': ' . $transportError : '')
        );
        if (!$retryable || $attempt === GEMINI_MAX_RETRIES) {
            break;
        }

        usleep(200000 * (2 ** $attempt));
    }

    return ['success' => false, 'error' => 'AI service is temporarily unavailable.'];
}

function parseGeminiResponse(string $response): array
{
    $result = json_decode($response, true);
    if (!is_array($result)) {
        return ['success' => false, 'error' => 'AI service returned an invalid response.'];
    }

    $parts = $result['candidates'][0]['content']['parts'] ?? [];
    $textParts = [];
    if (is_array($parts)) {
        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $textParts[] = $part['text'];
            }
        }
    }

    if ($textParts !== []) {
        return ['success' => true, 'text' => implode("\n", $textParts)];
    }

    $finishReason = $result['candidates'][0]['finishReason'] ?? null;
    if (is_string($finishReason) && $finishReason !== 'STOP') {
        error_log('Gemini response stopped with reason: ' . $finishReason);
        return [
            'success' => false,
            'error' => 'AI response was blocked by content safety controls.',
        ];
    }

    return ['success' => false, 'error' => 'AI service returned an invalid response.'];
}

function decodeGeminiJsonObject(string $responseText, array $requiredKeys = []): ?array
{
    $cleaned = trim($responseText);
    $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
    $cleaned = preg_replace('/\s*```$/', '', $cleaned);

    $firstBrace = strpos($cleaned, '{');
    $lastBrace = strrpos($cleaned, '}');
    if ($firstBrace === false || $lastBrace === false || $lastBrace < $firstBrace) {
        return null;
    }

    $decoded = json_decode(substr($cleaned, $firstBrace, $lastBrace - $firstBrace + 1), true);
    if (!is_array($decoded)) {
        return null;
    }

    foreach ($requiredKeys as $key => $expectedType) {
        if (is_int($key)) {
            $key = $expectedType;
            $expectedType = null;
        }
        if (!is_string($key) || !array_key_exists($key, $decoded)) {
            return null;
        }
        if (is_string($expectedType)
            && !geminiValueMatchesType($decoded[$key], $expectedType)) {
            return null;
        }
    }

    return $decoded;
}

function geminiValueMatchesType($value, string $expectedType): bool
{
    switch ($expectedType) {
        case 'string':
            return is_string($value);
        case 'number':
            return is_int($value) || is_float($value);
        case 'list':
            return is_array($value) && geminiArrayIsList($value);
        case 'object':
            return is_array($value) && !geminiArrayIsList($value);
        case 'array':
            return is_array($value);
        case 'boolean':
            return is_bool($value);
        default:
            return false;
    }
}

function geminiArrayIsList(array $value): bool
{
    if ($value === []) {
        return true;
    }
    return array_keys($value) === range(0, count($value) - 1);
}
