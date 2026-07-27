<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/environment.php';

define('GEMINI_API_KEY', (string) environmentValue('GEMINI_API_KEY', ''));
define('GEMINI_MODEL_NAME', 'gemini-2.0-flash-exp');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL_NAME . ':generateContent');
define('GEMINI_CONNECT_TIMEOUT', 10);
define('GEMINI_REQUEST_TIMEOUT', 60);
define('GEMINI_MAX_PROMPT_BYTES', 200000);

function callGeminiAPI($prompt, $systemInstruction = '') {
    $apiKey = GEMINI_API_KEY;

    if ($apiKey === '' || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
        return [
            'success' => false,
            'error' => 'AI service is not configured.'
        ];
    }

    if (!is_string($prompt)
        || !is_string($systemInstruction)
        || strlen($prompt) + strlen($systemInstruction) > GEMINI_MAX_PROMPT_BYTES) {
        return [
            'success' => false,
            'error' => 'AI request content is too large.'
        ];
    }

    $url = GEMINI_API_ENDPOINT . '?key=' . $apiKey;

    $fullPrompt = $prompt;
    if ($systemInstruction) {
        $fullPrompt = $systemInstruction . "\n\n" . $prompt;
    }

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $fullPrompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.4,
            'topK' => 32,
            'topP' => 1,
            'maxOutputTokens' => 4096,
        ]
    ];

    $jsonData = json_encode($data);
    if ($jsonData === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        return [
            'success' => false,
            'error' => 'Unable to prepare AI request.'
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, GEMINI_CONNECT_TIMEOUT);
    curl_setopt($ch, CURLOPT_TIMEOUT, GEMINI_REQUEST_TIMEOUT);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Gemini CURL Error: " . $curlError);
        return [
            'success' => false,
            'error' => 'AI service is temporarily unavailable.'
        ];
    }

    if ($httpCode !== 200) {
        $errorMsg = 'API request failed with status: ' . $httpCode;
        $result = json_decode($response, true);
        if (isset($result['error']['message'])) {
            $errorMsg .= ' - ' . $result['error']['message'];
        }
        error_log("Gemini API Error: " . $errorMsg);
        return [
            'success' => false,
            'error' => 'AI service request failed.'
        ];
    }

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'success' => true,
            'text' => $result['candidates'][0]['content']['parts'][0]['text']
        ];
    }

    if (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] !== 'STOP') {
        $reason = $result['candidates'][0]['finishReason'];
        error_log("Gemini blocked/filtered response: " . $reason);
        return [
            'success' => false,
            'error' => 'AI response was blocked by content safety controls.'
        ];
    }

    error_log("Unexpected Gemini response format");
    return [
        'success' => false,
        'error' => 'AI service returned an invalid response.'
    ];
}
?>
