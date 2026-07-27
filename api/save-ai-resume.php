<?php
header('Content-Type: application/json');
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/request-validation.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'User not authenticated'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

$allowedTemplates = [
    'classic', 'modern', 'professional', 'technical',
    'executive', 'creative', 'academic-standard',
    'research-scientist', 'teaching-faculty'
];

try {
    $input = readValidatedJsonBody(1048576);
    $resumeId = $input['resume_id'] ?? null;
    if ($resumeId !== null && $resumeId !== '') {
        $resumeId = positiveIntegerField($input, 'resume_id', 'Resume ID');
    } else {
        $resumeId = null;
    }
    $resumeTitle = optionalStringField($input, 'resume_title', 'Resume title', 255, 'Untitled Resume');
    $templateName = enumField($input, 'template', 'Template', $allowedTemplates, 'classic');
    $resumeState = $input['resumeState'] ?? [];
    if (!is_array($resumeState)) {
        throw new RequestValidationException('Invalid resume data.');
    }
    $encodedResumeState = json_encode($resumeState);
    if ($encodedResumeState === false || strlen($encodedResumeState) > 900000) {
        throw new RequestValidationException('Resume data is too large.');
    }
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
}

try {
    if (isset($resumeState['personal_details']) && !is_array($resumeState['personal_details'])) {
        throw new RequestValidationException('Invalid personal details.');
    }
    $personalDetails = json_encode($resumeState['personal_details'] ?? []);
    if ($personalDetails === false || strlen($personalDetails) > 20000) {
        throw new RequestValidationException('Personal details are too large.');
    }
    $summaryText = optionalStringField($resumeState, 'summary_text', 'Summary', 50000, '');
    $experience = encodedStructuredField($resumeState, 'experience', 'Experience', 200000);
    $education = encodedStructuredField($resumeState, 'education', 'Education', 200000);
    $skills = encodedStructuredField($resumeState, 'skills', 'Skills', 100000, '', true);
    $projects = encodedStructuredField($resumeState, 'projects', 'Projects', 100000);
    $achievements = optionalStringField($resumeState, 'achievements', 'Achievements', 50000, '');
    $portfolio = encodedStructuredField($resumeState, 'portfolio', 'Portfolio', 100000);
    $board = encodedStructuredField($resumeState, 'board', 'Board experience', 100000);
    $researchInterests = optionalStringField(
        $resumeState,
        'research_interests',
        'Research interests',
        20000,
        ''
    );
    $publications = encodedStructuredField($resumeState, 'publications', 'Publications', 200000);
    $grants = encodedStructuredField($resumeState, 'grants', 'Grants', 100000);
    $teaching = encodedStructuredField($resumeState, 'teaching', 'Teaching', 100000);
    $references = encodedStructuredField($resumeState, 'references', 'References', 100000);

    if ($resumeId) {
        $stmt = $conn->prepare("
            UPDATE resumes
            SET resume_title = ?,
                template_name = ?,
                personal_details = ?,
                summary_text = ?,
                experience_data = ?,
                education_data = ?,
                skills_data = ?,
                projects_data = ?,
                achievements_data = ?,
                portfolio_data = ?,
                board_data = ?,
                research_interests = ?,
                publications_data = ?,
                grants_data = ?,
                teaching_data = ?,
                references_data = ?,
                last_modified = NOW()
            WHERE resume_id = ? AND user_id = ?
        ");

        $stmt->bind_param(
            "ssssssssssssssssii",
            $resumeTitle,
            $templateName,
            $personalDetails,
            $summaryText,
            $experience,
            $education,
            $skills,
            $projects,
            $achievements,
            $portfolio,
            $board,
            $researchInterests,
            $publications,
            $grants,
            $teaching,
            $references,
            $resumeId,
            $user['id']
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Resume updated successfully',
                'resume_id' => $resumeId
            ]);
        } else {
            throw new Exception('Failed to update resume');
        }

        $stmt->close();
    } else {
        $status = 'draft';

        $stmt = $conn->prepare("
            INSERT INTO resumes (
                user_id,
                resume_title,
                template_name,
                personal_details,
                summary_text,
                experience_data,
                education_data,
                skills_data,
                projects_data,
                achievements_data,
                portfolio_data,
                board_data,
                research_interests,
                publications_data,
                grants_data,
                teaching_data,
                references_data,
                status,
                created_at,
                last_modified
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->bind_param(
            "isssssssssssssssss",
            $user['id'],
            $resumeTitle,
            $templateName,
            $personalDetails,
            $summaryText,
            $experience,
            $education,
            $skills,
            $projects,
            $achievements,
            $portfolio,
            $board,
            $researchInterests,
            $publications,
            $grants,
            $teaching,
            $references,
            $status
        );

        if ($stmt->execute()) {
            $newResumeId = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Resume created successfully',
                'resume_id' => $newResumeId
            ]);
        } else {
            throw new Exception('Failed to create resume');
        }

        $stmt->close();
    }
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
} catch (Exception $e) {
    error_log("Save Resume Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save resume',
        'details' => $e->getMessage()
    ]);
}
?>
