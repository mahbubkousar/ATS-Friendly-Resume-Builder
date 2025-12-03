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

$user = getCurrentUser();
$userId = $user['id'];

$input = json_decode(file_get_contents('php://input'), true);
$notificationId = $input['notification_id'] ?? null;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit();
}

$conn = getDBConnection();

$stmt = $conn->prepare("UPDATE notifications SET is_read = 0 WHERE notification_id = ? AND user_id = ?");
$stmt->bind_param("ii", $notificationId, $userId);
$stmt->execute();
$affectedRows = $stmt->affected_rows;
$stmt->close();

if ($affectedRows > 0) {
    echo json_encode(['success' => true, 'message' => 'Notification marked as unread']);
} else {
    echo json_encode(['success' => false, 'message' => 'Notification not found or already unread']);
}
?>
