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
define('GEMINI_MODEL_NAME', 'gemini-2.0-flash-exp');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL_NAME . ':generateContent');

function callGeminiAPI($prompt, $systemInstruction = '') {
    $apiKey = GEMINI_API_KEY;

    if ($apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
        return [
            'success' => false,
            'error' => 'Gemini API key not configured. Please update config/gemini.php'
        ];
    }

    $url = GEMINI_API_ENDPOINT . '?key=' . $apiKey;

    error_log("Prompt length: " . strlen($prompt));
    error_log("System instruction: " . $systemInstruction);

    $fullPrompt = $prompt;
    if ($systemInstruction) {
        $fullPrompt = $systemInstruction . "\n\n" . $prompt;
    }

    error_log("Full prompt length: " . strlen($fullPrompt));

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
            'error' => 'Failed to encode request: ' . json_last_error_msg()
        ];
    }

    error_log("Gemini API Request size: " . strlen($jsonData) . " bytes");

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    error_log("Gemini API HTTP Code: " . $httpCode);
    error_log("Gemini API Response: " . substr($response, 0, 1000));

    if ($curlError) {
        error_log("Gemini CURL Error: " . $curlError);
        return [
            'success' => false,
            'error' => 'CURL error: ' . $curlError,
            'response' => $response
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
            'error' => $errorMsg,
            'response' => $response
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
            'error' => 'Content filtered/blocked: ' . $reason,
            'response' => $response
        ];
    }

    error_log("Unexpected Gemini response format: " . json_encode($result));
    return [
        'success' => false,
        'error' => 'Unexpected API response format',
        'response' => $response
    ];
}
?>
