<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/request-validation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = readValidatedJsonBody(16384);
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
}
$shareToken = isset($input['share_token']) && is_string($input['share_token'])
    ? $input['share_token']
    : '';

if (!preg_match('/^[a-f0-9]{64}$/', $shareToken)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid share token required']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable']);
    exit();
}

$resumeStmt = $conn->prepare(
    'SELECT resume_id FROM resumes WHERE share_token = ? AND is_public = 1'
);
$resumeStmt->bind_param('s', $shareToken);
$resumeStmt->execute();
$resume = $resumeStmt->get_result()->fetch_assoc();
$resumeStmt->close();

if (!$resume) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Shared resume not found']);
    exit();
}

$resumeId = (int) $resume['resume_id'];
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

$conn->begin_transaction();
try {
    $stmt = $conn->prepare(
        'INSERT INTO resume_downloads (resume_id, ip_address)
         SELECT resume_id, ?
         FROM resumes
         WHERE resume_id = ? AND share_token = ? AND is_public = 1'
    );
    $stmt->bind_param('sis', $ipAddress, $resumeId, $shareToken);
    $stmt->execute();
    $inserted = $stmt->affected_rows === 1;
    $stmt->close();

    if (!$inserted) {
        throw new RuntimeException('Share authorization changed.');
    }

    $updateStmt = $conn->prepare(
        'UPDATE resumes
         SET download_count = download_count + 1
         WHERE resume_id = ? AND share_token = ? AND is_public = 1'
    );
    $updateStmt->bind_param('is', $resumeId, $shareToken);
    $updateStmt->execute();
    if ($updateStmt->affected_rows !== 1) {
        $updateStmt->close();
        throw new RuntimeException('Share authorization changed.');
    }
    $updateStmt->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    $conn->rollback();
    error_log('Download tracking authorization failure: ' . $e->getMessage());
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Shared resume not found']);
}
?>
