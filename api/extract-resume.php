<?php



ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');

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
} catch (Throwable $e) {
    error_log("Configuration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Configuration error: ' . $e->getMessage()]);
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

if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];

    $errorMsg = $uploadErrors[$_FILES['resume']['error']] ?? 'Unknown upload error';
    error_log("File upload error: " . $errorMsg);
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    exit;
}

$file = $_FILES['resume'];
$filePath = $file['tmp_name'];
$fileName = $file['name'];
$fileType = $file['type'];

try {
    error_log("File details: " . $fileName . " (" . $fileType . "), size: " . $file['size']);

    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    
    $fileContent = file_get_contents($filePath);
    $base64Content = base64_encode($fileContent);

    error_log("File encoded to base64, length: " . strlen($base64Content));

    
    $extractedData = extractResumeDataFromFile($base64Content, $fileExt);

    error_log("Successfully extracted resume data");

    echo json_encode([
        'success' => true,
        'data' => $extractedData
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


function extractResumeDataFromFile($base64Content, $fileExt) {
    $apiKey = GEMINI_API_KEY;

    if ($apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
        throw new Exception('Gemini API key not configured');
    }

    
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain'
    ];

    $mimeType = $mimeTypes[$fileExt] ?? 'application/pdf';

    
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
