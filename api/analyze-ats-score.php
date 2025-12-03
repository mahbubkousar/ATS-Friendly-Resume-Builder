<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/gemini.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$user = getCurrentUser();
$userId = $user['id'];

$resumeText = $_POST['resume_text'] ?? '';
$jobDescription = $_POST['job_description'] ?? '';
$fileType = $_POST['file_type'] ?? '';
$resumeId = $_POST['resume_id'] ?? null;

if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['resume_file'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileType = $fileExt;

    $fileContent = file_get_contents($file['tmp_name']);
    $base64Content = base64_encode($fileContent);

    $extractPrompt = "Extract all text content from this resume document. Return ONLY the plain text content, no formatting, no markdown, no explanations. Just the raw text from the document.";

    $extractResult = callGeminiAPIWithFile($base64Content, $fileExt, $extractPrompt);

    if (!$extractResult['success']) {
        $errorDetails = $extractResult['error'];
        if (isset($extractResult['response'])) {
            $errorDetails .= ' | Response: ' . substr($extractResult['response'], 0, 500);
        }
        echo json_encode(['success' => false, 'message' => 'Failed to extract text from file: ' . $errorDetails]);
        exit();
    }

    $resumeText = $extractResult['text'];
}

if (isset($_FILES['job_description_file']) && $_FILES['job_description_file']['error'] === UPLOAD_ERR_OK) {
    $jobFile = $_FILES['job_description_file'];
    $jobFileExt = strtolower(pathinfo($jobFile['name'], PATHINFO_EXTENSION));

    if ($jobFileExt === 'pdf') {
        $jobFileContent = file_get_contents($jobFile['tmp_name']);
        $jobBase64Content = base64_encode($jobFileContent);

        $jobExtractPrompt = "Extract all text content from this job description document. Return ONLY the plain text content, no formatting, no markdown, no explanations. Just the raw text from the document.";

        $jobExtractResult = callGeminiAPIWithFile($jobBase64Content, $jobFileExt, $jobExtractPrompt);

        if ($jobExtractResult['success']) {
            $jobDescription = $jobExtractResult['text'];
        }
    }
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

function callGeminiAPIWithFile($base64Content, $fileType, $prompt) {
    $apiKey = GEMINI_API_KEY;

    if ($apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
        return ['success' => false, 'error' => 'API key not configured'];
    }

    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $mimeType = $mimeTypes[$fileType] ?? 'application/pdf';
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
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => 'API error: ' . $httpCode . ($curlError ? ' - ' . $curlError : ''),
            'response' => $response
        ];
    }

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return ['success' => true, 'text' => $result['candidates'][0]['content']['parts'][0]['text']];
    }

    return [
        'success' => false,
        'error' => 'Unexpected response format',
        'response' => $response
    ];
}

function performATSAnalysis($resumeText, $jobDescription = '') {
    $prompt = buildAnalysisPrompt($resumeText, $jobDescription);

    $result = callGeminiAPI($prompt);

    if (!$result['success']) {
        return $result;
    }

    $responseText = $result['text'];
    $responseText = preg_replace('/```json\s*/s', '', $responseText);
    $responseText = preg_replace('/```\s*$/s', '', $responseText);
    $responseText = trim($responseText);

    $analysis = json_decode($responseText, true);

    if (!$analysis) {
        return [
            'success' => false,
            'error' => 'Failed to parse analysis results',
            'raw_response' => substr($result['text'], 0, 500)
        ];
    }

    return [
        'success' => true,
        'overall_score' => $analysis['overall_score'],
        'formatting_score' => $analysis['formatting_score'],
        'keywords_score' => $analysis['keywords_score'],
        'content_structure_score' => $analysis['content_structure_score'],
        'contact_info_score' => $analysis['contact_info_score'],
        'experience_format_score' => $analysis['experience_format_score'],
        'technical_score' => $analysis['technical_score'],
        'improvements' => $analysis['improvements'],
        'strengths' => $analysis['strengths'],
        'keywords_found' => $analysis['keywords_found'] ?? [],
        'keywords_missing' => $analysis['keywords_missing'] ?? []
    ];
}

function buildAnalysisPrompt($resumeText, $jobDescription) {
    $hasJobDesc = !empty($jobDescription);

    $prompt = "You are an expert ATS (Applicant Tracking System) analyzer. Analyze the following resume and provide a detailed scoring report.

RESUME TEXT:
{$resumeText}
";

    if ($hasJobDesc) {
        $prompt .= "
JOB DESCRIPTION:
{$jobDescription}
";
    }

    $prompt .= "

Analyze this resume based on ATS compatibility criteria and provide scores for each category:

1. FORMATTING SCORE (0-25 points):
   - Single column layout (no tables/columns)
   - Standard fonts (Arial, Georgia, Calibri, Tahoma)
   - No special characters or accents
   - No headers/footers for contact info
   - Proper use of ALL CAPS for section headers
   - No underlining
   - Consistent formatting

2. KEYWORDS SCORE (0-25 points):
   " . ($hasJobDesc ? "- Keywords from job description present" : "- Industry-relevant keywords present") . "
   - Both full terms and acronyms used (e.g., 'Certified Public Accountant (CPA)')
   - Keywords used in context, not just listed
   - Specific vs general keywords balance

3. CONTENT STRUCTURE SCORE (0-20 points):
   - Standard section headings (SUMMARY, EXPERIENCE, EDUCATION, SKILLS)
   - Reverse chronological order
   - Achievement-oriented bullet points
   - Professional summary present
   - Complete contact information

4. CONTACT INFORMATION SCORE (0-10 points):
   - Contact info in body (not header/footer)
   - Name on top line only (no credentials)
   - No special punctuation in name
   - Email, phone properly formatted

5. EXPERIENCE FORMAT SCORE (0-10 points):
   - Dates include months (MM/YYYY format)
   - Dates on the right after text
   - Job title, company, location, dates present
   - Consistent presentation order

6. TECHNICAL SCORE (0-10 points):
   - No spelling errors
   - Proper capitalization and punctuation
   - No complex formatting (condensed/expanded text)
   - Would work well in .doc format

Return your analysis in the following JSON format ONLY (no markdown, no code blocks, just pure JSON):

{
  \"overall_score\": <sum of all scores, 0-100>,
  \"formatting_score\": <0-25>,
  \"keywords_score\": <0-25>,
  \"content_structure_score\": <0-20>,
  \"contact_info_score\": <0-10>,
  \"experience_format_score\": <0-10>,
  \"technical_score\": <0-10>,
  \"strengths\": [\"strength 1\", \"strength 2\", \"strength 3\"],
  \"improvements\": [
    {\"category\": \"Formatting\", \"issue\": \"description\", \"suggestion\": \"how to fix\"},
    {\"category\": \"Keywords\", \"issue\": \"description\", \"suggestion\": \"how to fix\"}
  ]," . ($hasJobDesc ? "
  \"keywords_found\": [\"keyword1\", \"keyword2\"],
  \"keywords_missing\": [\"keyword1\", \"keyword2\"]" : "
  \"keywords_found\": [],
  \"keywords_missing\": []") . "
}

Be thorough and specific in your analysis. Focus on actionable improvements.";

    return $prompt;
}
?>
