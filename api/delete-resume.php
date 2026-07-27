<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireApiUser();

$user = getCurrentUser();
$userId = $user['id'];

requireApiMethod('POST', 'message');


try {
    $input = readValidatedJsonBody(16384);
    $resumeId = positiveIntegerField($input, 'resume_id', 'Resume ID');
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    
    $stmt = $conn->prepare("SELECT resume_id, resume_title FROM resumes WHERE resume_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resumeId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Resume not found or access denied']);
        $stmt->close();
        $conn->close();
        exit();
    }

    $resume = $result->fetch_assoc();
    $stmt->close();

    
    $stmt = $conn->prepare("DELETE FROM resumes WHERE resume_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resumeId, $userId);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Resume deleted successfully',
            'resume_title' => $resume['resume_title']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete resume']);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log('Resume deletion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete resume']);
}

$conn->close();
?>
