<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireLogin();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = readValidatedJsonBody();
    $applicationId = positiveIntegerField($input, 'application_id', 'Application ID');
    $companyName = requiredStringField($input, 'company_name', 'Company name', 255);
    $jobTitle = requiredStringField($input, 'job_title', 'Job title', 255);
    $jobLocation = optionalStringField($input, 'job_location', 'Job location', 255);
    $jobType = enumField(
        $input,
        'job_type',
        'Job type',
        ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'],
        'Full-time'
    );
    $salaryRange = optionalStringField($input, 'salary_range', 'Salary range', 100);
    $applicationDate = dateField($input, 'application_date', 'Application date', true);
    $status = enumField(
        $input,
        'status',
        'Status',
        ['Applied', 'In Review', 'Interview Scheduled', 'Interview Completed', 'Offer Received', 'Accepted', 'Rejected', 'Withdrawn'],
        'Applied'
    );
    $applicationUrl = optionalHttpUrlField($input, 'application_url', 'Application URL');
    $notes = optionalStringField($input, 'notes', 'Notes', 10000);
    $contactPerson = optionalStringField($input, 'contact_person', 'Contact person', 255);
    $contactEmail = optionalEmailField($input, 'contact_email', 'Contact email');
    $priority = enumField($input, 'priority', 'Priority', ['Low', 'Medium', 'High'], 'Medium');
    $interviewDate = dateTimeField($input, 'interview_date', 'Interview date');
    $interviewLocation = optionalStringField($input, 'interview_location', 'Interview location', 255);
    $interviewNotes = optionalStringField($input, 'interview_notes', 'Interview notes', 10000);
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
}

$user = getCurrentUser();
$userId = $user['id'];
$conn = getDBConnection();

$stmt = $conn->prepare("UPDATE job_applications SET
    company_name = ?, job_title = ?, job_location = ?, job_type = ?,
    salary_range = ?, application_date = ?, status = ?, application_url = ?,
    notes = ?, contact_person = ?, contact_email = ?, priority = ?,
    interview_date = ?, interview_location = ?, interview_notes = ?
    WHERE application_id = ? AND user_id = ?");

$stmt->bind_param("sssssssssssssssii",
    $companyName, $jobTitle, $jobLocation,
    $jobType, $salaryRange, $applicationDate,
    $status, $applicationUrl, $notes,
    $contactPerson, $contactEmail, $priority,
    $interviewDate, $interviewLocation, $interviewNotes,
    $applicationId, $userId
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update application']);
}

$stmt->close();
?>
