<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';

requireApiUser();

$user = getCurrentUser();
$userId = $user['id'];

requireApiMethod('GET', 'message');

$resumeId = $_GET['id'] ?? null;

if (!$resumeId) {
    echo json_encode(['success' => false, 'message' => 'Resume ID is required']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT resume_id, resume_title, template_name, personal_details, summary_text, status, created_at, updated_at FROM resumes WHERE resume_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resumeId, $userId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $resume = $result->fetch_assoc();

            $resume['personal_details'] = json_decode($resume['personal_details'], true);

            echo json_encode([
                'success' => true,
                'resume' => $resume
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Resume not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to load resume']);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Resume lookup failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load resume']);
}
?>
