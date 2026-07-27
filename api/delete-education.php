<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireLogin();

$user = getCurrentUser();
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $educationId = positiveIntegerField($_GET, 'id', 'Education ID');
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    
    $stmt = $conn->prepare("DELETE FROM user_education WHERE education_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $educationId, $userId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Education deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Education not found or unauthorized']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete education']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
