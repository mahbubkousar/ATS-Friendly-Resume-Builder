<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/app.php';
require_once '../includes/request-validation.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = readValidatedJsonBody(16384);
    $resumeId = positiveIntegerField($input, 'resume_id', 'Resume ID');
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
}

$user = getCurrentUser();
$userId = $user['id'];
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT share_token, is_public, shared_at, view_count, download_count FROM resumes WHERE resume_id = ? AND user_id = ?");
$stmt->bind_param("ii", $resumeId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resume not found']);
    exit();
}

$resume = $result->fetch_assoc();
$stmt->close();

$shareToken = $resume['share_token'];
$isNewLink = false;

if (empty($shareToken)) {
    $shareToken = bin2hex(random_bytes(32));
    $isNewLink = true;

    $updateStmt = $conn->prepare("UPDATE resumes SET share_token = ?, is_public = 1, shared_at = NOW() WHERE resume_id = ? AND user_id = ?");
    $updateStmt->bind_param("sii", $shareToken, $resumeId, $userId);
    $updateStmt->execute();
    if ($updateStmt->affected_rows !== 1) {
        $updateStmt->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Resume not found']);
        exit();
    }
    $updateStmt->close();

    $isPublic = true;
} else {
    $isPublic = (bool)$resume['is_public'];
}

$shareUrl = buildApplicationUrl('/view-resume.php?token=' . rawurlencode($shareToken));

echo json_encode([
    'success' => true,
    'share_url' => $shareUrl,
    'token' => $shareToken,
    'is_public' => $isPublic,
    'is_new_link' => $isNewLink,
    'stats' => [
        'view_count' => (int)$resume['view_count'],
        'download_count' => (int)$resume['download_count'],
        'shared_at' => $resume['shared_at']
    ]
]);
?>
