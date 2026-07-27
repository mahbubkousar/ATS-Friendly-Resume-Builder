<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireLogin();

$user = getCurrentUser();
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = readValidatedJsonBody(65536);
    $institution = requiredStringField($input, 'institution', 'Institution', 255);
    $degree = optionalStringField($input, 'degree', 'Degree', 255, '');
    $field = optionalStringField($input, 'field', 'Field of study', 255, '');
    $startDate = dateField($input, 'start_date', 'Start date');
    $endDate = dateField($input, 'end_date', 'End date');
    $gpa = optionalStringField($input, 'gpa', 'GPA', 10);
    if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
        throw new RequestValidationException('End date cannot be before start date.');
    }
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
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
