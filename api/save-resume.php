<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$user = getCurrentUser();
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}


$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    
    $input = $_POST;
    
    if (isset($input['personal_details']) && is_string($input['personal_details'])) {
        $input['personal_details'] = json_decode($input['personal_details'], true);
    }
}

$resumeId = $input['resume_id'] ?? null;
$resumeTitle = trim($input['resume_title'] ?? '');
$templateName = $input['template_name'] ?? 'classic';
$personalDetails = json_encode($input['personal_details'] ?? []);
$summaryText = $input['summary_text'] ?? '';
$status = $input['status'] ?? 'draft';


$experience = isset($input['experience']) ? (is_string($input['experience']) ? $input['experience'] : json_encode($input['experience'])) : '[]';
$education = isset($input['education']) ? (is_string($input['education']) ? $input['education'] : json_encode($input['education'])) : '[]';
$skills = isset($input['skills']) ? (is_string($input['skills']) ? $input['skills'] : json_encode($input['skills'])) : '[]';
$certifications = isset($input['certifications']) ? (is_string($input['certifications']) ? $input['certifications'] : json_encode($input['certifications'])) : '[]';
$languages = $input['languages'] ?? '';
$affiliations = $input['affiliations'] ?? '';


$researchInterests = $input['researchInterests'] ?? '';
$publications = isset($input['publications']) ? (is_string($input['publications']) ? $input['publications'] : json_encode($input['publications'])) : '[]';
$grants = isset($input['grants']) ? (is_string($input['grants']) ? $input['grants'] : json_encode($input['grants'])) : '[]';
$teaching = isset($input['teaching']) ? (is_string($input['teaching']) ? $input['teaching'] : json_encode($input['teaching'])) : '[]';
$memberships = $input['memberships'] ?? '';

if (empty($resumeTitle)) {
    echo json_encode(['success' => false, 'message' => 'Resume title is required']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    if ($resumeId) {
        
        $stmt = $conn->prepare("UPDATE resumes SET resume_title = ?, template_name = ?, personal_details = ?, summary_text = ?, experience = ?, education = ?, skills = ?, certifications = ?, languages = ?, affiliations = ?, research_interests = ?, publications = ?, grants = ?, teaching = ?, memberships = ?, status = ?, updated_at = NOW() WHERE resume_id = ? AND user_id = ?");
        $stmt->bind_param("ssssssssssssssssii", $resumeTitle, $templateName, $personalDetails, $summaryText, $experience, $education, $skills, $certifications, $languages, $affiliations, $researchInterests, $publications, $grants, $teaching, $memberships, $status, $resumeId, $userId);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Resume updated successfully',
                'resume_id' => $resumeId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update resume']);
        }
        $stmt->close();
    } else {
        
        $stmt = $conn->prepare("INSERT INTO resumes (user_id, resume_title, template_name, personal_details, summary_text, experience, education, skills, certifications, languages, affiliations, research_interests, publications, grants, teaching, memberships, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssssssssss", $userId, $resumeTitle, $templateName, $personalDetails, $summaryText, $experience, $education, $skills, $certifications, $languages, $affiliations, $researchInterests, $publications, $grants, $teaching, $memberships, $status);

        if ($stmt->execute()) {
            $newResumeId = $stmt->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Resume created successfully',
                'resume_id' => $newResumeId
            ]);
        } else {
            $error = $stmt->error;
            error_log("SQL Error creating resume: " . $error);
            error_log("Resume data: " . json_encode([
                'userId' => $userId,
                'resumeTitle' => $resumeTitle,
                'templateName' => $templateName,
                'experience' => substr($experience, 0, 100),
                'education' => substr($education, 0, 100)
            ]));
            echo json_encode(['success' => false, 'message' => 'Failed to create resume: ' . $error]);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Exception saving resume: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
