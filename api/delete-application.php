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
    $input = readValidatedJsonBody(16384);
    $applicationId = positiveIntegerField($input, 'application_id', 'Application ID');
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

$stmt = $conn->prepare("DELETE FROM job_applications WHERE application_id = ? AND user_id = ?");
$stmt->bind_param("ii", $applicationId, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Application deleted successfully!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Application not found or access denied']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete application']);
}

$stmt->close();
?>
