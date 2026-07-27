<?php


header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');

try {
    require_once '../config/session.php';
    require_once '../config/gemini.php';
    require_once '../includes/ai-security.php';
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}


if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rejectAiRequest('Method not allowed.', 405);
}

$input = readLimitedJsonRequest();
$jobDescription = isset($input['jobDescription']) && is_string($input['jobDescription'])
    ? $input['jobDescription']
    : '';
$resumeData = isset($input['resumeData']) && is_array($input['resumeData'])
    ? $input['resumeData']
    : [];

if (empty($jobDescription)) {
    echo json_encode(['success' => false, 'error' => 'Job description is required']);
    exit;
}

enforceAiRateLimit((int) getCurrentUserId(), 'analyze-job');

try {
    $analysis = analyzeJob($jobDescription, $resumeData);

    echo json_encode([
        'success' => true,
        'analysis' => $analysis
    ]);

} catch (Exception $e) {
    error_log("Job analysis error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to analyze job: ' . $e->getMessage()
    ]);
}


function analyzeJob($jobDescription, $resumeData) {
    $prompt = "You are an expert career advisor and ATS specialist. Analyze the following job description and recommend the best resume template.\n\n";

    $prompt .= "Job Description:\n" . substr($jobDescription, 0, 10000) . "\n\n";

    $prompt .= "Available Templates:\n";
    $prompt .= "1. modern - Clean, modern design for tech, startup, and creative roles\n";
    $prompt .= "2. professional - Traditional professional format for corporate roles\n";
    $prompt .= "3. academic-standard - Academic CV for faculty, research, and academic positions\n";
    $prompt .= "4. executive - Senior leadership and executive positions\n";
    $prompt .= "5. technical - Technical roles (engineers, developers, IT)\n";
    $prompt .= "6. creative - Creative industries (design, marketing, media)\n";
    $prompt .= "7. classic - Traditional conservative format\n\n";

    $prompt .= "Candidate's Current Resume Data:\n" . json_encode($resumeData, JSON_PRETTY_PRINT) . "\n\n";

    $prompt .= "Return ONLY valid JSON in this exact format:\n";
    $prompt .= "{\n";
    $prompt .= "  \"recommendedTemplate\": \"template-name\",\n";
    $prompt .= "  \"reasoning\": \"Brief explanation why this template fits\",\n";
    $prompt .= "  \"jobType\": \"string (e.g., Software Engineer, Research Scientist)\",\n";
    $prompt .= "  \"industry\": \"string (e.g., Technology, Healthcare)\",\n";
    $prompt .= "  \"keySkills\": [\"skill1\", \"skill2\", \"skill3\"],\n";
    $prompt .= "  \"keywords\": [\"keyword1\", \"keyword2\"],\n";
    $prompt .= "  \"experienceLevel\": \"entry|mid|senior|executive\",\n";
    $prompt .= "  \"suggestions\": [\"suggestion1\", \"suggestion2\"]\n";
    $prompt .= "}";

    $systemInstruction = "You are an expert at analyzing job descriptions and matching them with appropriate resume formats. Return ONLY valid JSON with no additional text.";

    $response = callGeminiAPI($prompt, $systemInstruction);

    if (!$response['success']) {
        throw new Exception($response['error'] ?? 'Gemini API call failed');
    }

    $responseText = $response['text'];

    
    if (preg_match('/\{[\s\S]*\}/', $responseText, $matches)) {
        $jsonText = $matches[0];
    } else {
        $jsonText = $responseText;
    }

    $analysis = json_decode($jsonText, true);

    if (!$analysis) {
        throw new Exception('Failed to parse job analysis as JSON');
    }

    
    $validTemplates = ['modern', 'professional', 'academic-standard', 'executive', 'technical', 'creative', 'classic'];

    if (!isset($analysis['recommendedTemplate']) || !in_array($analysis['recommendedTemplate'], $validTemplates)) {
        $analysis['recommendedTemplate'] = 'modern'; 
    }

    
    $analysis['reasoning'] = $analysis['reasoning'] ?? 'Best suited for this role';
    $analysis['jobType'] = $analysis['jobType'] ?? '';
    $analysis['industry'] = $analysis['industry'] ?? '';
    $analysis['keySkills'] = $analysis['keySkills'] ?? [];
    $analysis['keywords'] = $analysis['keywords'] ?? [];
    $analysis['experienceLevel'] = $analysis['experienceLevel'] ?? 'mid';
    $analysis['suggestions'] = $analysis['suggestions'] ?? [];

    return $analysis;
}
?>
