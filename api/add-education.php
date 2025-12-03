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


error_log("Add Education Input: " . print_r($input, true));
error_log("User ID: " . $userId);

$institution = trim($input['institution'] ?? '');
$degree = trim($input['degree'] ?? '');
$field = trim($input['field'] ?? '');
$startDate = $input['start_date'] ?? null;
$endDate = $input['end_date'] ?? null;
$gpa = trim($input['gpa'] ?? '');


if (empty($startDate)) $startDate = null;
if (empty($endDate)) $endDate = null;
if (empty($gpa)) $gpa = null;

if (empty($institution)) {
    echo json_encode(['success' => false, 'message' => 'Institution is required']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO user_education (user_id, institution_name, degree, field_of_study, start_date, end_date, gpa) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("issssss", $userId, $institution, $degree, $field, $startDate, $endDate, $gpa);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Education added successfully',
            'id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add education']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
