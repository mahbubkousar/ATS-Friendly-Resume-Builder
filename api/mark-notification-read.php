<?php

require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/request-validation.php';

requireApiMethod('POST');
$user = requireApiUser();

try {
    $input = readValidatedJsonBody(16384);
    $notificationId = positiveIntegerField(
        $input,
        'notification_id',
        'Notification ID'
    );
} catch (RequestValidationException $exception) {
    sendRequestValidationError($exception);
}

$connection = getDBConnection();
if (!$connection) {
    sendApiError('Service temporarily unavailable', 503);
}

$statement = $connection->prepare(
    'UPDATE notifications
     SET is_read = 1
     WHERE notification_id = ? AND user_id = ?'
);
$userId = (int) $user['id'];
$statement->bind_param('ii', $notificationId, $userId);
$statement->execute();

if ($statement->affected_rows === 0) {
    $statement->close();
    sendApiError('Notification not found', 404);
}

$statement->close();
echo json_encode(['success' => true]);
