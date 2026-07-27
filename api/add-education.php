<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/request-validation.php';

requireApiUser();

$user = getCurrentUser();
$userId = $user['id'];

requireApiMethod('POST', 'message');

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
        error_log('Unable to prepare education insert: ' . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to save education']);
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
    error_log('Education insert failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save education']);
}
?>
