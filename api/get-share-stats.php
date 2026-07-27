<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/app.php';

requireLogin();

$resumeId = $_GET['resume_id'] ?? null;

if (!$resumeId) {
    echo json_encode(['success' => false, 'message' => 'Resume ID required']);
    exit();
}

$user = getCurrentUser();
$userId = $user['id'];
$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT
        share_token,
        is_public,
        shared_at,
        view_count,
        download_count,
        last_viewed_at
    FROM resumes
    WHERE resume_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $resumeId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resume not found']);
    exit();
}

$resume = $result->fetch_assoc();
$stmt->close();

$viewsStmt = $conn->prepare("
    SELECT
        DATE(viewed_at) as date,
        COUNT(*) as count
    FROM resume_views
    WHERE resume_id = ? AND viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(viewed_at)
    ORDER BY date DESC
");
$viewsStmt->bind_param("i", $resumeId);
$viewsStmt->execute();
$viewsResult = $viewsStmt->get_result();
$recentViews = [];
while ($row = $viewsResult->fetch_assoc()) {
    $recentViews[] = $row;
}
$viewsStmt->close();

$shareUrl = null;
if (!empty($resume['share_token'])) {
    $shareUrl = buildApplicationUrl(
        '/view-resume.php?token=' . rawurlencode($resume['share_token'])
    );
}

echo json_encode([
    'success' => true,
    'has_link' => !empty($resume['share_token']),
    'share_url' => $shareUrl,
    'is_public' => (bool)$resume['is_public'],
    'stats' => [
        'view_count' => (int)$resume['view_count'],
        'download_count' => (int)$resume['download_count'],
        'shared_at' => $resume['shared_at'],
        'last_viewed_at' => $resume['last_viewed_at'],
        'recent_views' => $recentViews
    ]
]);
?>
