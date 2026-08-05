<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireApiUser();

requireApiMethod('POST', 'message');

try {
    $input = readValidatedJsonBody(16384);
    $applicationId = positiveIntegerField($input, 'application_id', 'Application ID');
    $newStatus = enumField(
        $input,
        'status',
        'Status',
        ['Applied', 'In Review', 'Interview Scheduled', 'Interview Completed', 'Offer Received', 'Accepted', 'Rejected', 'Withdrawn']
    );
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
$stmt = $conn->prepare("SELECT status, job_title, company_name FROM job_applications WHERE application_id = ? AND user_id = ? FOR UPDATE");
$stmt->bind_param("ii", $applicationId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->rollback();
    sendApiError('Application not found or access denied', 404);
}

$application = $result->fetch_assoc();
$oldStatus = $application['status'];
$jobTitle = $application['job_title'];
$companyName = $application['company_name'];
$stmt->close();

$stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE application_id = ? AND user_id = ?");
$stmt->bind_param("sii", $newStatus, $applicationId, $userId);

if ($stmt->execute()) {
    $timelineStmt = $conn->prepare("INSERT INTO application_timeline (application_id, event_type, event_title, event_description, event_date) VALUES (?, ?, ?, ?, NOW())");
    if ($timelineStmt) {
        $eventType = 'status_changed';
        $eventTitle = "Status Updated: $newStatus";
        $eventDesc = "Application status changed from '$oldStatus' to '$newStatus'";
        $timelineStmt->bind_param("isss", $applicationId, $eventType, $eventTitle, $eventDesc);
        if (!$timelineStmt->execute()) {
            throw new RuntimeException('Unable to create status timeline.');
        }
        $timelineStmt->close();
    } else {
        throw new RuntimeException('Unable to prepare status timeline.');
    }

    $activityStmt = $conn->prepare("INSERT INTO application_activity (application_id, activity_type, activity_description) VALUES (?, ?, ?)");
    if ($activityStmt) {
        $activityType = 'status_changed';
        $activityDesc = "Status changed from '$oldStatus' to '$newStatus' for $jobTitle at $companyName";
        $activityStmt->bind_param("iss", $applicationId, $activityType, $activityDesc);
        if (!$activityStmt->execute()) {
            throw new RuntimeException('Unable to create status activity.');
        }
        $activityStmt->close();
    } else {
        throw new RuntimeException('Unable to prepare status activity.');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Application status updated successfully!',
        'new_status' => $newStatus
    ]);
} else {
    throw new RuntimeException('Unable to update application status.');
}

$stmt->close();
} catch (Throwable $exception) {
    $conn->rollback();
    error_log('Application status transaction failed: ' . $exception->getMessage());
    sendApiError('Failed to update application status', 500);
}
?>
