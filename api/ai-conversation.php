<?php


require_once __DIR__ . '/../includes/api-bootstrap.php';

try {
    require_once '../config/session.php';
    require_once '../config/database.php';
    require_once '../config/gemini.php';
    require_once '../includes/ai-security.php';
} catch (Throwable $e) {
    error_log('AI conversation configuration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Service configuration error']);
    exit;
}


requireApiUser('error');

$user = getCurrentUser();

requireApiMethod('POST', 'error');

$input = readLimitedJsonRequest();
$userMessage = isset($input['message']) && is_string($input['message']) ? $input['message'] : '';
$resumeState = isset($input['resumeState']) && is_array($input['resumeState'])
    ? $input['resumeState']
    : [];
$conversationHistory = isset($input['conversationHistory']) && is_array($input['conversationHistory'])
    ? $input['conversationHistory']
    : [];
$templateName = isset($input['templateName']) && is_string($input['templateName'])
    ? $input['templateName']
    : 'modern';

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit;
}
if (strlen($userMessage) > AI_MAX_MESSAGE_BYTES) {
    rejectAiRequest('Message is too long.', 413);
}

enforceAiRateLimit((int) $user['id'], 'ai-conversation');

$systemInstruction = buildSystemInstruction($templateName, $resumeState);


$conversationContext = buildConversationPrompt($userMessage, $conversationHistory, $resumeState);


try {
    $geminiResponse = callGeminiAPI($conversationContext, $systemInstruction);

    if (!$geminiResponse['success']) {
        throw new Exception($geminiResponse['error'] ?? 'API call failed');
    }

    $aiMessage = $geminiResponse['text'];

    
    $extractedData = extractStructuredData($aiMessage, $templateName);

    
    $conversationalResponse = $aiMessage;

    
    if (preg_match('/CONVERSATIONAL_RESPONSE:\s*(.+?)(?=\s*DATA_UPDATES:|$)/s', $aiMessage, $matches)) {
        
        $conversationalResponse = trim($matches[1]);
    } else {
        
        $conversationalResponse = preg_replace('/\s*DATA_UPDATES:\s*```json.*?```/s', '', $aiMessage);
        $conversationalResponse = trim($conversationalResponse);
    }

    echo json_encode([
        'success' => true,
        'response' => $conversationalResponse,
        'updates' => $extractedData
    ]);

} catch (Exception $e) {
    error_log("AI Conversation Error: " . $e->getMessage());
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to process conversation'
    ]);
}


function buildSystemInstruction($templateName, $resumeState) {
    
    $baseInstruction = "You are an expert resume writing assistant helping users build ATS-optimized resumes through conversation. ";
    $baseInstruction .= "Your role is to:\n";
    $baseInstruction .= "1. Ask relevant questions to gather resume information\n";
    $baseInstruction .= "2. Extract and structure the information provided\n";
    $baseInstruction .= "3. Provide helpful suggestions for improvement\n";
    $baseInstruction .= "4. Use professional, friendly language\n\n";

    
    
    
    
    if ($templateName === 'academic-standard') {
        
        $baseInstruction .= "This is an ACADEMIC CV template. Focus on:\n";
        $baseInstruction .= "- Research interests and expertise\n";
        $baseInstruction .= "- Publications (journal articles, conference papers)\n";
        $baseInstruction .= "- Grants and funding\n";
        $baseInstruction .= "- Teaching experience and courses taught\n";
        $baseInstruction .= "- Professional memberships and service\n";
        $baseInstruction .= "- Education with dissertation/thesis details\n\n";
    } else {
        
        $baseInstruction .= "This is a PROFESSIONAL resume template. Focus on:\n";
        $baseInstruction .= "- Professional summary highlighting career achievements\n";
        $baseInstruction .= "- Work experience with quantifiable accomplishments\n";
        $baseInstruction .= "- Technical and soft skills\n";
        $baseInstruction .= "- Education and certifications\n";
        $baseInstruction .= "- Use action verbs and metrics\n\n";
    }

    
    
    
    
    $baseInstruction .= "IMPORTANT: When the user provides information, you MUST respond in this EXACT format:\n\n";
    $baseInstruction .= "CONVERSATIONAL_RESPONSE: [Your friendly response here]\n\n";
    $baseInstruction .= "DATA_UPDATES:\n```json\n{\n  \"field_name\": \"value\"\n}\n```\n\n";

    
    $baseInstruction .= "For personal_details updates, use format:\n";
    $baseInstruction .= '{"personal_details": {"fullName": "John Doe", "professionalTitle": "Software Engineer"}}' . "\n\n";
    $baseInstruction .= "For experience, use array format:\n";
    $baseInstruction .= '{"experience": [{"jobTitle": "...", "company": "...", "dates": "...", "description": "..."}]}' . "\n\n";
    $baseInstruction .= "For education, use array format:\n";
    $baseInstruction .= '{"education": [{"degree": "...", "institution": "...", "year": "...", "details": "..."}]}' . "\n\n";
    $baseInstruction .= "For skills (professional templates), use string:\n";
    $baseInstruction .= '{"skills": "Python, JavaScript, React, Node.js"}' . "\n\n";
    $baseInstruction .= "Always include both CONVERSATIONAL_RESPONSE and DATA_UPDATES sections.\n\n";

    
    
    
    
    
    
    
    
    $baseInstruction .= "Current resume data:\n" . json_encode($resumeState, JSON_PRETTY_PRINT);

    return $baseInstruction;
}


function buildConversationPrompt($userMessage, $conversationHistory, $resumeState) {
    $prompt = "User's latest message: " . $userMessage . "\n\n";

    
    if (!empty($conversationHistory)) {
        $prompt .= "Recent conversation:\n";
        $recentHistory = array_slice($conversationHistory, -5);
        foreach ($recentHistory as $msg) {
            if (!is_array($msg) || !isset($msg['content']) || !is_string($msg['content'])) {
                continue;
            }
            $role = isset($msg['role']) && $msg['role'] === 'user' ? 'User' : 'Assistant';
            $prompt .= "{$role}: " . substr($msg['content'], 0, AI_MAX_MESSAGE_BYTES) . "\n";
        }
        $prompt .= "\n";
    }

    $prompt .= "Respond conversationally to help build their resume. ";
    $prompt .= "If the user provides information (name, experience, education, skills, etc.), acknowledge it and ask a relevant follow-up question.\n";
    $prompt .= "Keep responses concise and friendly.";

    return $prompt;
}


function extractStructuredData($aiMessage, $templateName) {
    $updates = [];

    
    if (preg_match('/DATA_UPDATES:\s*```json\s*(\{.*?\})\s*```/s', $aiMessage, $matches)) {
        $jsonData = $matches[1];  

        
        $parsedData = json_decode($jsonData, true);

        
        if ($parsedData && is_array($parsedData)) {
            $updates = $parsedData;
        }
    }

    return $updates;
}
?>
