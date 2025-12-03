<?php
header('Content-Type: application/json');
require_once '../config/session.php';
require_once '../config/database.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'User not authenticated'
    ]);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);

$resumeId = $input['resume_id'] ?? null;
$resumeTitle = $input['resume_title'] ?? 'Untitled Resume';
$templateName = $input['template'] ?? 'classic';
$resumeState = $input['resumeState'] ?? [];

$allowedTemplates = [
    'classic', 'modern', 'professional', 'technical',
    'executive', 'creative', 'academic-standard',
    'research-scientist', 'teaching-faculty'
];

if (!in_array($templateName, $allowedTemplates)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid template'
    ]);
    exit;
}

try {
    $personalDetails = json_encode($resumeState['personal_details'] ?? []);
    $summaryText = $resumeState['summary_text'] ?? '';
    $experience = json_encode($resumeState['experience'] ?? []);
    $education = json_encode($resumeState['education'] ?? []);
    $skills = $resumeState['skills'] ?? '';

    $projects = json_encode($resumeState['projects'] ?? []);
    $achievements = $resumeState['achievements'] ?? '';
    $portfolio = json_encode($resumeState['portfolio'] ?? []);
    $board = json_encode($resumeState['board'] ?? []);
    $researchInterests = $resumeState['research_interests'] ?? '';
    $publications = json_encode($resumeState['publications'] ?? []);
    $grants = json_encode($resumeState['grants'] ?? []);
    $teaching = json_encode($resumeState['teaching'] ?? []);
    $references = json_encode($resumeState['references'] ?? []);

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
} catch (Exception $e) {
    error_log("Save Resume Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save resume',
        'details' => $e->getMessage()
    ]);
}
?>
