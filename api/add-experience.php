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
    $title = requiredStringField($input, 'title', 'Title', 255);
    $company = requiredStringField($input, 'company', 'Company', 255);
    $location = optionalStringField($input, 'location', 'Location', 255);
    $startDate = dateField($input, 'start_date', 'Start date');
    $endDate = dateField($input, 'end_date', 'End date');
    $isCurrent = booleanField($input, 'is_current', 'Current position');
    $description = optionalStringField($input, 'description', 'Description', 10000, '');
    if (!$isCurrent && $startDate !== null && $endDate !== null && $endDate < $startDate) {
        throw new RequestValidationException('End date cannot be before start date.');
    }
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
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
        error_log('Unable to prepare experience insert: ' . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to save experience']);
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
    error_log('Experience insert failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save experience']);
}
?>
