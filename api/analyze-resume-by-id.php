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

$resumeId = $_POST['resume_id'] ?? null;

if (!$resumeId) {
    echo json_encode(['success' => false, 'message' => 'Resume ID is required']);
    exit();
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT resume_id, resume_title, personal_details, summary_text FROM resumes WHERE resume_id = ? AND user_id = ?");
$stmt->bind_param("ii", $resumeId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$resume = $result->fetch_assoc();
$stmt->close();

if (!$resume) {
    echo json_encode(['success' => false, 'message' => 'Resume not found']);
    exit();
}

$stmt = $conn->prepare("SELECT section_type, section_title, section_content FROM resume_sections WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order");
$stmt->bind_param("i", $resumeId);
$stmt->execute();
$result = $stmt->get_result();
$sections = [];
while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}
$stmt->close();

$resumeData = [
    'personalInfo' => json_decode($resume['personal_details'], true) ?? [],
    'summary' => $resume['summary_text'] ?? '',
    'experience' => [],
    'education' => [],
    'skills' => [],
    'certifications' => []
];

foreach ($sections as $section) {
    $content = json_decode($section['section_content'], true);

    switch ($section['section_type']) {
        case 'experience':
            if (is_array($content)) {
                $resumeData['experience'] = array_merge($resumeData['experience'], $content);
            }
            break;
        case 'education':
            if (is_array($content)) {
                $resumeData['education'] = array_merge($resumeData['education'], $content);
            }
            break;
        case 'skills':
            if (is_array($content)) {
                $resumeData['skills'] = array_merge($resumeData['skills'], $content);
            }
            break;
        case 'certifications':
            if (is_array($content)) {
                $resumeData['certifications'] = array_merge($resumeData['certifications'], $content);
            }
            break;
    }
}

$resumeText = buildResumeText($resumeData);

$jobDescription = $_POST['job_description'] ?? '';

require_once 'analyze-ats-score.php';
$analysisResult = performATSAnalysis($resumeText, $jobDescription);

if (!$analysisResult['success']) {
    echo json_encode(['success' => false, 'message' => 'Analysis failed: ' . $analysisResult['error']]);
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO ats_scores (
        user_id, resume_id, resume_text, job_description,
        overall_score, formatting_score, keywords_score, content_structure_score,
        contact_info_score, experience_format_score, technical_score,
        improvements, strengths, keywords_found, keywords_missing, file_type
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'json')
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
    'json'
);

$stmt->execute();
$scoreId = $conn->insert_id;
$stmt->close();

echo json_encode([
    'success' => true,
    'score_id' => $scoreId,
    'analysis' => $analysisResult
]);

function buildResumeText($data) {
    $text = '';

    if (isset($data['personalInfo'])) {
        $personal = $data['personalInfo'];
        $text .= ($personal['name'] ?? $personal['fullName'] ?? '') . "\n";
        $text .= ($personal['email'] ?? '') . "\n";
        $text .= ($personal['phone'] ?? '') . "\n";
        $text .= ($personal['location'] ?? '') . "\n";
        if (!empty($personal['linkedin'])) $text .= $personal['linkedin'] . "\n";
        if (!empty($personal['website'])) $text .= $personal['website'] . "\n";
        $text .= "\n";
    }

    if (!empty($data['summary'])) {
        $text .= "PROFESSIONAL SUMMARY\n";
        $text .= $data['summary'] . "\n\n";
    }

    if (isset($data['experience']) && is_array($data['experience'])) {
        $text .= "PROFESSIONAL EXPERIENCE\n";
        foreach ($data['experience'] as $exp) {
            $text .= ($exp['title'] ?? $exp['jobTitle'] ?? '') . "\n";
            $text .= ($exp['company'] ?? '') . " - " . ($exp['location'] ?? '') . "\n";

            if (!empty($exp['dates'])) {
                $text .= $exp['dates'] . "\n";
            } elseif (!empty($exp['startDate']) || !empty($exp['endDate'])) {
                $text .= ($exp['startDate'] ?? '') . " - " . ($exp['endDate'] ?? '') . "\n";
            }

            if (!empty($exp['description'])) {
                $text .= $exp['description'] . "\n";
            }
            $text .= "\n";
        }
    }

    if (isset($data['education']) && is_array($data['education'])) {
        $text .= "EDUCATION\n";
        foreach ($data['education'] as $edu) {
            $text .= ($edu['degree'] ?? '') . "\n";
            $text .= ($edu['school'] ?? '') . "\n";
            $text .= ($edu['year'] ?? $edu['graduationDate'] ?? '') . "\n";
            if (!empty($edu['gpa'])) {
                $text .= "GPA: " . $edu['gpa'] . "\n";
            }
            $text .= "\n";
        }
    }

    if (isset($data['skills']) && is_array($data['skills'])) {
        $text .= "SKILLS\n";
        foreach ($data['skills'] as $skill) {
            $skillName = $skill['name'] ?? $skill['skillName'] ?? '';
            if (is_string($skill)) {
                $skillName = $skill;
            }
            $text .= "• " . $skillName . "\n";
        }
        $text .= "\n";
    }

    if (isset($data['certifications']) && is_array($data['certifications'])) {
        $text .= "CERTIFICATIONS\n";
        foreach ($data['certifications'] as $cert) {
            $certName = $cert['name'] ?? $cert['certName'] ?? '';
            $issuer = $cert['issuer'] ?? $cert['issuingOrg'] ?? '';
            $date = $cert['date'] ?? $cert['issueDate'] ?? '';

            $text .= $certName . ($issuer ? " - " . $issuer : '') . "\n";
            if ($date) $text .= $date . "\n";
            $text .= "\n";
        }
    }

    return $text;
}
?>
