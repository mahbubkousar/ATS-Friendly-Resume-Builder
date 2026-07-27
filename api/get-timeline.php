<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';

requireApiUser();
requireApiMethod('GET');

$applicationId = $_GET['application_id'] ?? null;

if (!$applicationId) {
    echo json_encode(['success' => false, 'message' => 'Application ID is required']);
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
    echo json_encode(['success' => false, 'message' => 'Application not found or access denied']);
    $stmt->close();
    exit();
}

$application = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("
    SELECT * FROM application_timeline
    WHERE application_id = ?
    ORDER BY event_date DESC, created_at DESC
");

$stmt->bind_param("i", $applicationId);
$stmt->execute();
$result = $stmt->get_result();

$timeline = [];
while ($row = $result->fetch_assoc()) {
    $timeline[] = $row;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'application' => $application,
    'timeline' => $timeline
]);
?>
