<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/gemini.php';
require_once '../includes/upload-security.php';
require_once '../includes/ai-security.php';
require_once '../includes/ai-consent.php';
require_once '../services/AtsAnalysisService.php';

requireApiUser();

requireApiMethod('POST', 'message');

$user = getCurrentUser();
$userId = $user['id'];
requireAiProcessingConsent((int) $userId);

$resumeText = isset($_POST['resume_text']) && is_string($_POST['resume_text'])
    ? $_POST['resume_text']
    : '';
$jobDescription = isset($_POST['job_description']) && is_string($_POST['job_description'])
    ? $_POST['job_description']
    : '';
$fileType = isset($_POST['file_type']) && is_string($_POST['file_type'])
    ? $_POST['file_type']
    : '';
$resumeId = $_POST['resume_id'] ?? null;
if ($resumeId !== null && $resumeId !== '') {
    $resumeId = filter_var($resumeId, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if ($resumeId === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid resume ID']);
        exit();
    }

    $ownershipConnection = getDBConnection();
    if (!$ownershipConnection) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable']);
        exit();
    }
    $ownershipStatement = $ownershipConnection->prepare(
        'SELECT 1 FROM resumes WHERE resume_id = ? AND user_id = ?'
    );
    $ownershipStatement->bind_param('ii', $resumeId, $userId);
    $ownershipStatement->execute();
    $ownedResume = $ownershipStatement->get_result()->fetch_row();
    $ownershipStatement->close();

    if (!$ownedResume) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Resume not found']);
        exit();
    }
} else {
    $resumeId = null;
}

$aiRequestCost = 1;
if (isset($_FILES['resume_file']) && ($_FILES['resume_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $aiRequestCost++;
}
if (isset($_FILES['job_description_file'])
    && ($_FILES['job_description_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $aiRequestCost++;
}
enforceAiRateLimit((int) $userId, 'analyze-ats-score', $aiRequestCost);

try {
    validateDocumentTextLength($resumeText, 'Resume text', MAX_RESUME_TEXT_BYTES);
    validateDocumentTextLength($jobDescription, 'Job description', MAX_JOB_DESCRIPTION_TEXT_BYTES);

    if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $document = validateDocumentUpload($_FILES['resume_file'], ['pdf', 'doc', 'docx']);
        $fileType = $document['extension'];
        $base64Content = base64_encode(readValidatedDocument($document));

        $extractPrompt = "Extract all text content from this resume document. Return ONLY the plain text content, no formatting, no markdown, no explanations. Just the raw text from the document.";

        $extractResult = callGeminiAPIWithFile($base64Content, $document['mime_type'], $extractPrompt);

        if (!$extractResult['success']) {
            echo json_encode(['success' => false, 'message' => 'Failed to extract text from file']);
            exit();
        }

        $resumeText = $extractResult['text'];
        validateDocumentTextLength($resumeText, 'Extracted resume text', MAX_RESUME_TEXT_BYTES);
    }

    if (isset($_FILES['job_description_file'])
        && $_FILES['job_description_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $jobDocument = validateDocumentUpload($_FILES['job_description_file'], ['pdf']);
        $jobBase64Content = base64_encode(readValidatedDocument($jobDocument));

        $jobExtractPrompt = "Extract all text content from this job description document. Return ONLY the plain text content, no formatting, no markdown, no explanations. Just the raw text from the document.";

        $jobExtractResult = callGeminiAPIWithFile(
            $jobBase64Content,
            $jobDocument['mime_type'],
            $jobExtractPrompt
        );

        if (!$jobExtractResult['success']) {
            echo json_encode(['success' => false, 'message' => 'Failed to extract job description']);
            exit();
        }

        $jobDescription = $jobExtractResult['text'];
        validateDocumentTextLength(
            $jobDescription,
            'Extracted job description',
            MAX_JOB_DESCRIPTION_TEXT_BYTES
        );
    }
} catch (UploadValidationException $e) {
    http_response_code($e->getStatusCode());
    if ($e->getStatusCode() >= 500) {
        error_log('Upload validation service error: ' . $e->getMessage());
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getStatusCode() >= 500
            ? 'Unable to validate uploaded document'
            : $e->getMessage(),
    ]);
    exit();
}

if (empty($resumeText)) {
    echo json_encode(['success' => false, 'message' => 'Resume text is required']);
    exit();
}

$analysisResult = performATSAnalysis($resumeText, $jobDescription);

if (!$analysisResult['success']) {
    echo json_encode(['success' => false, 'message' => 'Analysis failed: ' . $analysisResult['error']]);
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    INSERT INTO ats_scores (
        user_id, resume_id, resume_text, job_description,
        overall_score, formatting_score, keywords_score, content_structure_score,
        contact_info_score, experience_format_score, technical_score,
        improvements, strengths, keywords_found, keywords_missing, file_type
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$improvements = json_encode($analysisResult['improvements']);
$strengths = json_encode($analysisResult['strengths']);
$keywordsFound = json_encode($analysisResult['keywords_found']);
$keywordsMissing = json_encode($analysisResult['keywords_missing']);

$stmt->bind_param(
    "iissiiiiiiiissss",
    $userId,
    $resumeId,
    $resumeText,
    $jobDescription,
    $analysisResult['overall_score'],
    $analysisResult['formatting_score'],
    $analysisResult['keywords_score'],
    $analysisResult['content_structure_score'],
    $analysisResult['contact_info_score'],
    $analysisResult['experience_format_score'],
    $analysisResult['technical_score'],
    $improvements,
    $strengths,
    $keywordsFound,
    $keywordsMissing,
    $fileType
);

$stmt->execute();
$scoreId = $conn->insert_id;
$stmt->close();

echo json_encode([
    'success' => true,
    'score_id' => $scoreId,
    'analysis' => $analysisResult
]);

function callGeminiAPIWithFile($base64Content, $mimeType, $prompt) {
    return callGeminiParts([
        [
            'inline_data' => [
                'mime_type' => $mimeType,
                'data' => $base64Content,
            ],
        ],
        ['text' => $prompt],
    ]);
}

?>
