<?php
header('Content-Type: application/json');
require_once '../config/database.php';

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

$conn = getDBConnection();

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

$stmt = $conn->prepare("INSERT INTO resume_downloads (resume_id, ip_address) VALUES (?, ?)");
$stmt->bind_param("is", $resumeId, $ipAddress);
$stmt->execute();
$stmt->close();

$updateStmt = $conn->prepare("UPDATE resumes SET download_count = download_count + 1 WHERE resume_id = ?");
$updateStmt->bind_param("i", $resumeId);
$updateStmt->execute();
$updateStmt->close();

echo json_encode(['success' => true]);
?>
