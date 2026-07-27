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
$resumeId = $input['resume_id'] ?? null;

if (!$resumeId) {
    echo json_encode(['success' => false, 'message' => 'Resume ID required']);
    exit();
}

$user = getCurrentUser();
$userId = $user['id'];
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT is_public FROM resumes WHERE resume_id = ? AND user_id = ?");
$stmt->bind_param("ii", $resumeId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resume not found']);
    exit();
}

$resume = $result->fetch_assoc();
$stmt->close();

$newStatus = $resume['is_public'] ? 0 : 1;

$updateStmt = $conn->prepare("UPDATE resumes SET is_public = ? WHERE resume_id = ? AND user_id = ?");
$updateStmt->bind_param("iii", $newStatus, $resumeId, $userId);

if ($updateStmt->execute() && $updateStmt->affected_rows === 1) {
    echo json_encode([
        'success' => true,
        'is_public' => (bool)$newStatus,
        'message' => $newStatus ? 'Resume is now public' : 'Resume is now private'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update resume']);
}

$updateStmt->close();
?>
