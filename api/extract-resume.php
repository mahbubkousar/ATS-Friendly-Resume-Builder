<?php



ini_set('max_execution_time', '75');
ini_set('memory_limit', '128M');

require_once __DIR__ . '/../includes/api-bootstrap.php';

try {
    require_once '../config/session.php';
    require_once '../config/gemini.php';
    require_once '../includes/upload-security.php';
    require_once '../includes/ai-security.php';
    require_once '../includes/ai-consent.php';
} catch (Throwable $e) {
    error_log("Configuration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Service configuration error']);
    exit;
}

requireApiMethod('POST', 'error');
requireApiUser('error');
requireAiProcessingConsent((int) getCurrentUserId());


if (!isset($_FILES['resume'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

enforceAiRateLimit((int) getCurrentUserId(), 'extract-resume');

try {
    $document = validateDocumentUpload($_FILES['resume'], ['pdf', 'doc', 'docx', 'txt']);
    $base64Content = base64_encode(readValidatedDocument($document));

    $extractedData = extractResumeDataFromFile($base64Content, $document['mime_type']);

    echo json_encode([
        'success' => true,
        'data' => $extractedData
    ]);

} catch (UploadValidationException $e) {
    http_response_code($e->getStatusCode());
    error_log("Resume upload rejected: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getStatusCode() >= 500
            ? 'Unable to validate uploaded document'
            : $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Resume extraction error: " . $e->getMessage());
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to extract resume data'
    ]);
} catch (Throwable $e) {
    error_log("Fatal error in resume extraction: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to process resume'
    ]);
}


function extractResumeDataFromFile($base64Content, $mimeType) {
    $prompt = "Extract all information from this resume document and return ONLY valid JSON in this exact format:\n\n";
    $prompt .= '{"personal_details":{"fullName":"","email":"","phone":"","location":"","linkedin":"","professionalTitle":""},"summary_text":"","experience":[{"jobTitle":"","company":"","dates":"","description":""}],"education":[{"degree":"","institution":"","year":"","details":""}],"skills":"","certifications":[{"name":"","issuer":"","year":""}],"languages":""}\n\n';
    $prompt .= "Extract all text from the resume and fill in the JSON fields. Return ONLY the JSON, no markdown, no explanations.";

    $response = callGeminiParts(
        [
            [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $base64Content,
                ],
            ],
            ['text' => $prompt],
        ],
        [
            'temperature' => 0.1,
            'maxOutputTokens' => 4096,
        ]
    );
    if (!$response['success']) {
        throw new Exception($response['error']);
    }

    $data = decodeGeminiJsonObject($response['text'], [
        'personal_details' => 'object',
        'summary_text' => 'string',
        'experience' => 'list',
        'education' => 'list',
    ]);

    if (!$data) {
        error_log("Failed to parse resume extraction response as JSON");
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
