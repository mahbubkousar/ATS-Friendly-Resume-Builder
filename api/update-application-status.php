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

if (empty($input['application_id']) || empty($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Application ID and status are required']);
    exit();
}

$user = getCurrentUser();
$userId = $user['id'];
$applicationId = $input['application_id'];
$newStatus = $input['status'];

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("SELECT status, job_title, company_name FROM job_applications WHERE application_id = ? AND user_id = ?");
$stmt->bind_param("ii", $applicationId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Application not found or access denied']);
    $stmt->close();
    exit();
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
        $timelineStmt->execute();
        $timelineStmt->close();
    }

    $activityStmt = $conn->prepare("INSERT INTO application_activity (application_id, activity_type, activity_description) VALUES (?, ?, ?)");
    if ($activityStmt) {
        $activityType = 'status_changed';
        $activityDesc = "Status changed from '$oldStatus' to '$newStatus' for $jobTitle at $companyName";
        $activityStmt->bind_param("iss", $applicationId, $activityType, $activityDesc);
        $activityStmt->execute();
        $activityStmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Application status updated successfully!',
        'new_status' => $newStatus
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update application status']);
}

$stmt->close();
?>
