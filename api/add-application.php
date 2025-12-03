<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['company_name']) || empty($input['job_title']) || empty($input['application_date'])) {
    echo json_encode(['success' => false, 'message' => 'Company name, job title, and application date are required']);
    exit();
}

$user = getCurrentUser();
$userId = $user['id'];

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO job_applications (
    user_id, company_name, job_title, job_location, job_type, salary_range,
    application_date, status, application_url, notes, contact_person,
    contact_email, priority, interview_date, interview_location, interview_notes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$companyName = $input['company_name'];
$jobTitle = $input['job_title'];
$jobLocation = $input['job_location'] ?? null;
$jobType = $input['job_type'] ?? 'Full-time';
$salaryRange = $input['salary_range'] ?? null;
$applicationDate = $input['application_date'];
$status = $input['status'] ?? 'Applied';
$applicationUrl = $input['application_url'] ?? null;
$notes = $input['notes'] ?? null;
$contactPerson = $input['contact_person'] ?? null;
$contactEmail = $input['contact_email'] ?? null;
$priority = $input['priority'] ?? 'Medium';
$interviewDate = $input['interview_date'] ?? null;
$interviewLocation = $input['interview_location'] ?? null;
$interviewNotes = $input['interview_notes'] ?? null;

$stmt->bind_param(
    "isssssssssssssss",
    $userId,
    $companyName,
    $jobTitle,
    $jobLocation,
    $jobType,
    $salaryRange,
    $applicationDate,
    $status,
    $applicationUrl,
    $notes,
    $contactPerson,
    $contactEmail,
    $priority,
    $interviewDate,
    $interviewLocation,
    $interviewNotes
);

if ($stmt->execute()) {
    $applicationId = $stmt->insert_id;

    $timelineStmt = $conn->prepare("INSERT INTO application_timeline (application_id, event_type, event_title, event_description, event_date) VALUES (?, ?, ?, ?, ?)");
    if ($timelineStmt) {
        $eventType = 'application_submitted';
        $eventTitle = "Applied to $jobTitle";
        $eventDesc = "Application submitted to $companyName for $jobTitle position";
        $eventDate = $applicationDate . ' 00:00:00';
        $timelineStmt->bind_param("issss", $applicationId, $eventType, $eventTitle, $eventDesc, $eventDate);
        $timelineStmt->execute();
        $timelineStmt->close();
    }

    if ($interviewDate) {
        $timelineStmt = $conn->prepare("INSERT INTO application_timeline (application_id, event_type, event_title, event_description, event_date) VALUES (?, ?, ?, ?, ?)");
        if ($timelineStmt) {
            $eventType = 'interview_scheduled';
            $eventTitle = "Interview Scheduled";
            $eventDesc = "Interview scheduled for $jobTitle at $companyName";
            if ($interviewLocation) {
                $eventDesc .= " at $interviewLocation";
            }
            $timelineStmt->bind_param("issss", $applicationId, $eventType, $eventTitle, $eventDesc, $interviewDate);
            $timelineStmt->execute();
            $timelineStmt->close();
        }
    }

    $activityStmt = $conn->prepare("INSERT INTO application_activity (application_id, activity_type, activity_description) VALUES (?, ?, ?)");
    if ($activityStmt) {
        $activityType = 'created';
        $activityDesc = "Application created for $jobTitle at $companyName";
        $activityStmt->bind_param("iss", $applicationId, $activityType, $activityDesc);
        $activityStmt->execute();
        $activityStmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Application added successfully!',
        'application_id' => $applicationId
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add application']);
}

$stmt->close();
?>
