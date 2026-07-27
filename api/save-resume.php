<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireApiUser();

$user = getCurrentUser();
$userId = $user['id'];

requireApiMethod('POST', 'message');


$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
try {
    if (strpos($contentType, 'application/json') !== false) {
        $input = readValidatedJsonBody(1048576);
    } else {
        if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 1048576) {
            throw new RequestValidationException('Request body is too large.');
        }
        $input = $_POST;
        if (isset($input['personal_details']) && is_string($input['personal_details'])) {
            $decodedPersonalDetails = json_decode($input['personal_details'], true);
            if (!is_array($decodedPersonalDetails)) {
                throw new RequestValidationException('Invalid personal details.');
            }
            $input['personal_details'] = $decodedPersonalDetails;
        }
    }

    $resumeId = $input['resume_id'] ?? null;
    if ($resumeId !== null && $resumeId !== '') {
        $resumeId = positiveIntegerField($input, 'resume_id', 'Resume ID');
    } else {
        $resumeId = null;
    }
    $resumeTitle = requiredStringField($input, 'resume_title', 'Resume title', 255);
    $templateName = enumField(
        $input,
        'template_name',
        'Template',
        ['classic', 'modern', 'professional', 'technical', 'executive', 'creative', 'academic-standard', 'research-scientist', 'teaching-faculty'],
        'classic'
    );
    if (isset($input['personal_details']) && !is_array($input['personal_details'])) {
        throw new RequestValidationException('Invalid personal details.');
    }
    $personalDetails = json_encode($input['personal_details'] ?? []);
    if ($personalDetails === false || strlen($personalDetails) > 20000) {
        throw new RequestValidationException('Personal details are too large.');
    }
    $summaryText = optionalStringField($input, 'summary_text', 'Summary', 50000, '');
    $status = enumField($input, 'status', 'Status', ['draft', 'published', 'archived'], 'draft');

    $experience = encodedStructuredField($input, 'experience', 'Experience', 200000);
    $education = encodedStructuredField($input, 'education', 'Education', 200000);
    $skills = encodedStructuredField($input, 'skills', 'Skills', 100000, '[]', true);
    $certifications = encodedStructuredField($input, 'certifications', 'Certifications', 100000);
    $languages = optionalStringField($input, 'languages', 'Languages', 20000, '');
    $affiliations = optionalStringField($input, 'affiliations', 'Affiliations', 20000, '');
    $researchInterests = optionalStringField($input, 'researchInterests', 'Research interests', 20000, '');
    $publications = encodedStructuredField($input, 'publications', 'Publications', 200000);
    $grants = encodedStructuredField($input, 'grants', 'Grants', 100000);
    $teaching = encodedStructuredField($input, 'teaching', 'Teaching', 100000);
    $memberships = optionalStringField($input, 'memberships', 'Memberships', 20000, '');
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
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
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create resume']);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Exception saving resume: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save resume']);
}

$conn->close();
?>
