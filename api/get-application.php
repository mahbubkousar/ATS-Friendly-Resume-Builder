<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$applicationId = $_GET['id'] ?? null;

if (!$applicationId) {
    echo json_encode(['success' => false, 'message' => 'Application ID required']);
    exit();
}


$user = getCurrentUser();
$userId = $user['id'];
$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM job_applications WHERE application_id = ? AND user_id = ?");
$stmt->bind_param("ii", $applicationId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Application not found']);
} else {
    $application = $result->fetch_assoc();
    echo json_encode(['success' => true, 'application' => $application]);
}

$stmt->close();
?>
