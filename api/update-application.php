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

if (empty($input['application_id'])) {
    echo json_encode(['success' => false, 'message' => 'Application ID required']);
    exit();
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
    $input['company_name'], $input['job_title'], $input['job_location'],
    $input['job_type'], $input['salary_range'], $input['application_date'],
    $input['status'], $input['application_url'], $input['notes'],
    $input['contact_person'], $input['contact_email'], $input['priority'],
    $input['interview_date'], $input['interview_location'], $input['interview_notes'],
    $input['application_id'], $userId
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update application']);
}

$stmt->close();
?>
