<?php


header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');

try {
    require_once '../config/session.php';
    require_once '../config/gemini.php';
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}


if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}


$input = json_decode(file_get_contents('php://input'), true);
$resumeData = $input['resumeData'] ?? [];
$jobAnalysis = $input['jobAnalysis'] ?? [];
$template = $input['template'] ?? 'modern';

if (empty($resumeData)) {
    echo json_encode(['success' => false, 'error' => 'Resume data is required']);
    exit;
}

try {
    $optimizedResume = optimizeForATS($resumeData, $jobAnalysis, $template);

    echo json_encode([
        'success' => true,
        'resumeState' => $optimizedResume
    ]);

} catch (Exception $e) {
    error_log("Resume optimization error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to optimize resume: ' . $e->getMessage()
    ]);
}


function optimizeForATS($resumeData, $jobAnalysis, $template) {
    $isAcademic = ($template === 'academic-standard');

    $prompt = "You are an expert resume writer and ATS optimization specialist. ";
    $prompt .= "Optimize the following resume content to be ATS-friendly while maintaining authenticity.\n\n";

    $prompt .= "Original Resume Data:\n" . json_encode($resumeData, JSON_PRETTY_PRINT) . "\n\n";

    $prompt .= "Job Analysis:\n" . json_encode($jobAnalysis, JSON_PRETTY_PRINT) . "\n\n";

    $prompt .= "Template Type: " . $template . "\n\n";

    $prompt .= "Optimization Guidelines:\n";
    $prompt .= "1. Enhance professional summary to highlight relevant skills and experience\n";
    $prompt .= "2. Incorporate key skills and keywords from the job analysis naturally\n";
    $prompt .= "3. Improve experience descriptions with action verbs and quantifiable achievements\n";
    $prompt .= "4. Ensure proper formatting for ATS parsing\n";
    $prompt .= "5. Keep all information truthful - only enhance presentation, don't fabricate\n\n";

    if ($isAcademic) {
        $prompt .= "Academic Template - Return JSON with these fields:\n";
        $prompt .= "{\n";
        $prompt .= "  \"personal_details\": { \"fullName\": \"\", \"email\": \"\", \"phone\": \"\", \"location\": \"\", \"linkedin\": \"\", \"professionalTitle\": \"\" },\n";
        $prompt .= "  \"summary_text\": \"Academic profile summary\",\n";
        $prompt .= "  \"researchInterests\": \"Research areas\",\n";
        $prompt .= "  \"education\": [{\"degree\": \"\", \"institution\": \"\", \"year\": \"\", \"details\": \"thesis/dissertation\"}],\n";
        $prompt .= "  \"experience\": [{\"jobTitle\": \"Position\", \"company\": \"Institution\", \"dates\": \"\", \"description\": \"Responsibilities\"}],\n";
        $prompt .= "  \"publications\": [\"Publication citation strings\"],\n";
        $prompt .= "  \"grants\": [{\"title\": \"\", \"agency\": \"\", \"amount\": \"\", \"year\": \"\"}],\n";
        $prompt .= "  \"teaching\": [{\"course\": \"\", \"institution\": \"\", \"year\": \"\"}],\n";
        $prompt .= "  \"memberships\": \"Professional memberships and service\"\n";
        $prompt .= "}\n";
    } else {
        $prompt .= "Professional Template - Return JSON with these fields:\n";
        $prompt .= "{\n";
        $prompt .= "  \"personal_details\": { \"fullName\": \"\", \"email\": \"\", \"phone\": \"\", \"location\": \"\", \"linkedin\": \"\", \"professionalTitle\": \"\" },\n";
        $prompt .= "  \"summary_text\": \"Professional summary (3-4 sentences)\",\n";
        $prompt .= "  \"experience\": [{\"jobTitle\": \"\", \"company\": \"\", \"dates\": \"\", \"description\": \"Bullet points of achievements\"}],\n";
        $prompt .= "  \"education\": [{\"degree\": \"\", \"institution\": \"\", \"year\": \"\", \"details\": \"\"}],\n";
        $prompt .= "  \"skills\": \"Comma-separated technical and soft skills\",\n";
        $prompt .= "  \"certifications\": [{\"name\": \"\", \"issuer\": \"\", \"year\": \"\"}],\n";
        $prompt .= "  \"languages\": \"Languages with proficiency levels\"\n";
        $prompt .= "}\n";
    }

    $prompt .= "\nReturn ONLY valid JSON with no additional text. Ensure descriptions use bullet points separated by newlines.";

    $systemInstruction = "You are an expert resume optimization specialist. Enhance resume content for ATS compatibility and job matching while maintaining truthfulness. Return ONLY valid JSON.";

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

    $optimizedData = json_decode($jsonText, true);

    if (!$optimizedData) {
        
        error_log("Failed to parse optimized resume, using original data");
        $optimizedData = $resumeData;
    }

    
    $optimizedData['personal_details'] = $optimizedData['personal_details'] ?? $resumeData['personal_details'] ?? [];
    $optimizedData['summary_text'] = $optimizedData['summary_text'] ?? $resumeData['summary_text'] ?? '';
    $optimizedData['experience'] = $optimizedData['experience'] ?? $resumeData['experience'] ?? [];
    $optimizedData['education'] = $optimizedData['education'] ?? $resumeData['education'] ?? [];

    if ($isAcademic) {
        $optimizedData['researchInterests'] = $optimizedData['researchInterests'] ?? '';
        $optimizedData['publications'] = $optimizedData['publications'] ?? [];
        $optimizedData['grants'] = $optimizedData['grants'] ?? [];
        $optimizedData['teaching'] = $optimizedData['teaching'] ?? [];
        $optimizedData['memberships'] = $optimizedData['memberships'] ?? '';
    } else {
        $optimizedData['skills'] = $optimizedData['skills'] ?? $resumeData['skills'] ?? '';
        $optimizedData['certifications'] = $optimizedData['certifications'] ?? $resumeData['certifications'] ?? [];
        $optimizedData['languages'] = $optimizedData['languages'] ?? $resumeData['languages'] ?? '';
    }

    return $optimizedData;
}
?>
