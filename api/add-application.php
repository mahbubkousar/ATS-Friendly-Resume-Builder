<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireApiUser();

requireApiMethod('POST', 'message');

try {
    $input = readValidatedJsonBody();
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
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$conn->begin_transaction();

try {
$stmt = $conn->prepare("INSERT INTO job_applications (
    user_id, company_name, job_title, job_location, job_type, salary_range,
    application_date, status, application_url, notes, contact_person,
    contact_email, priority, interview_date, interview_location, interview_notes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
        if (!$timelineStmt->execute()) {
            throw new RuntimeException('Unable to create application timeline.');
        }
        $timelineStmt->close();
    } else {
        throw new RuntimeException('Unable to prepare application timeline.');
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
            if (!$timelineStmt->execute()) {
                throw new RuntimeException('Unable to create interview timeline.');
            }
            $timelineStmt->close();
        } else {
            throw new RuntimeException('Unable to prepare interview timeline.');
        }
    }

    $activityStmt = $conn->prepare("INSERT INTO application_activity (application_id, activity_type, activity_description) VALUES (?, ?, ?)");
    if ($activityStmt) {
        $activityType = 'created';
        $activityDesc = "Application created for $jobTitle at $companyName";
        $activityStmt->bind_param("iss", $applicationId, $activityType, $activityDesc);
        if (!$activityStmt->execute()) {
            throw new RuntimeException('Unable to create application activity.');
        }
        $activityStmt->close();
    } else {
        throw new RuntimeException('Unable to prepare application activity.');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Application added successfully!',
        'application_id' => $applicationId
    ]);
} else {
    throw new RuntimeException('Unable to create application.');
}

$stmt->close();
} catch (Throwable $exception) {
    $conn->rollback();
    error_log('Application creation transaction failed: ' . $exception->getMessage());
    sendApiError('Failed to add application', 500);
}
?>
