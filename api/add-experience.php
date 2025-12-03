<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$user = getCurrentUser();
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$title = trim($input['title'] ?? '');
$company = trim($input['company'] ?? '');
$location = trim($input['location'] ?? '');
$startDate = $input['start_date'] ?? null;
$endDate = $input['end_date'] ?? null;
$isCurrent = $input['is_current'] ?? false;
$description = trim($input['description'] ?? '');

if (empty($title) || empty($company)) {
    echo json_encode(['success' => false, 'message' => 'Title and company are required']);
    exit();
}


if ($isCurrent) {
    $endDate = null;
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO user_experience (user_id, job_title, company_name, location, start_date, end_date, current_position, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("isssssis", $userId, $title, $company, $location, $startDate, $endDate, $isCurrent, $description);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Experience added successfully',
            'id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add experience']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
