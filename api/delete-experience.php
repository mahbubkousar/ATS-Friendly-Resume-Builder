<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$user = getCurrentUser();
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$experienceId = $_GET['id'] ?? null;

if (!$experienceId) {
    echo json_encode(['success' => false, 'message' => 'Experience ID is required']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    
    $stmt = $conn->prepare("DELETE FROM user_experience WHERE experience_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $experienceId, $userId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Experience deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Experience not found or unauthorized']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete experience']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
