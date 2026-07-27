<?php



ini_set('max_execution_time', '75');
ini_set('memory_limit', '128M');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', '1'); 


error_log("Extract resume request received");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

try {
    require_once '../config/session.php';
    require_once '../config/gemini.php';
    require_once '../includes/upload-security.php';
    require_once '../includes/ai-security.php';
} catch (Throwable $e) {
    error_log("Configuration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    error_log("User not authenticated");
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}


if (!isset($_FILES['resume'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

enforceAiRateLimit((int) getCurrentUserId(), 'extract-resume');

try {
    $document = validateDocumentUpload($_FILES['resume'], ['pdf', 'doc', 'docx', 'txt']);
    $base64Content = base64_encode(readValidatedDocument($document));

    error_log("File encoded to base64, length: " . strlen($base64Content));

    $extractedData = extractResumeDataFromFile($base64Content, $document['mime_type']);

    error_log("Successfully extracted resume data");

    echo json_encode([
        'success' => true,
        'data' => $extractedData
    ]);

} catch (UploadValidationException $e) {
    http_response_code($e->getStatusCode());
    error_log("Resume upload rejected: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Resume extraction error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    error_log("Fatal error in resume extraction: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'System error: ' . $e->getMessage()
    ]);
}


function extractResumeDataFromFile($base64Content, $mimeType) {
    $apiKey = GEMINI_API_KEY;

    if ($apiKey === '' || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
        throw new Exception('Gemini API key not configured');
    }

    $prompt = "Extract all information from this resume document and return ONLY valid JSON in this exact format:\n\n";
    $prompt .= '{"personal_details":{"fullName":"","email":"","phone":"","location":"","linkedin":"","professionalTitle":""},"summary_text":"","experience":[{"jobTitle":"","company":"","dates":"","description":""}],"education":[{"degree":"","institution":"","year":"","details":""}],"skills":"","certifications":[{"name":"","issuer":"","year":""}],"languages":""}\n\n';
    $prompt .= "Extract all text from the resume and fill in the JSON fields. Return ONLY the JSON, no markdown, no explanations.";

    $url = GEMINI_API_ENDPOINT . '?key=' . $apiKey;

    $data = [
        'contents' => [
            [
                'parts' => [
                    [
                        'inline_data' => [
                            'mime_type' => $mimeType,
                            'data' => $base64Content
                        ]
                    ],
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.1,
            'maxOutputTokens' => 4096,
        ]
    ];

    error_log("Calling Gemini Vision API for resume extraction...");

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, GEMINI_CONNECT_TIMEOUT);
    curl_setopt($ch, CURLOPT_TIMEOUT, GEMINI_REQUEST_TIMEOUT);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    error_log("Gemini Vision API HTTP Code: " . $httpCode);

    if ($curlError) {
        throw new Exception('CURL error: ' . $curlError);
    }

    if ($httpCode !== 200) {
        $result = json_decode($response, true);
        $errorMsg = 'API error: ' . $httpCode;
        if (isset($result['error']['message'])) {
            $errorMsg .= ' - ' . $result['error']['message'];
        }
        error_log("Gemini Vision API Error: " . $errorMsg);
        error_log("Response: " . substr($response, 0, 500));
        throw new Exception($errorMsg);
    }

    $result = json_decode($response, true);

    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        error_log("Unexpected response format: " . json_encode($result));
        throw new Exception('Unexpected API response format');
    }

    $responseText = $result['candidates'][0]['content']['parts'][0]['text'];
    error_log("Gemini Vision returned text length: " . strlen($responseText));

    
    if (preg_match('/\{[\s\S]*\}/s', $responseText, $matches)) {
        $jsonText = $matches[0];
    } else {
        $jsonText = $responseText;
    }

    
    $jsonText = preg_replace('/^```json\s*/s', '', $jsonText);
    $jsonText = preg_replace('/\s*```$/s', '', $jsonText);

    $data = json_decode($jsonText, true);

    if (!$data) {
        error_log("Failed to parse JSON: " . $jsonText);
        throw new Exception('Failed to parse extracted data as JSON');
    }

    
    $data['personal_details'] = $data['personal_details'] ?? [];
    $data['summary_text'] = $data['summary_text'] ?? '';
    $data['experience'] = $data['experience'] ?? [];
    $data['education'] = $data['education'] ?? [];
    $data['skills'] = $data['skills'] ?? '';
    $data['certifications'] = $data['certifications'] ?? [];
    $data['languages'] = $data['languages'] ?? '';

    return $data;
}
?>
