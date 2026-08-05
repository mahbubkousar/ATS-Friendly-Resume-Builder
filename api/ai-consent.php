<?php

require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/ai-consent.php';
require_once __DIR__ . '/../includes/request-validation.php';

$user = requireApiUser('error');
$userId = (int) $user['id'];
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    echo json_encode(['success' => true] + getAiConsentStatus($userId));
    exit;
}

if ($method !== 'POST') {
    header('Allow: GET, POST');
    sendApiError('Method not allowed', 405, 'error');
}

try {
    $input = readValidatedJsonBody(16384);
    $action = enumField($input, 'action', 'Action', ['grant', 'revoke']);
} catch (RequestValidationException $exception) {
    sendRequestValidationError($exception);
}

$connection = getDBConnection();
if ($action === 'grant') {
    $statement = $connection->prepare(
        'UPDATE users
         SET ai_processing_consent_at = NOW(), ai_consent_version = ?
         WHERE user_id = ?'
    );
    $version = AI_CONSENT_VERSION;
    $statement->bind_param('si', $version, $userId);
} else {
    $statement = $connection->prepare(
        'UPDATE users
         SET ai_processing_consent_at = NULL, ai_consent_version = NULL
         WHERE user_id = ?'
    );
    $statement->bind_param('i', $userId);
}

$statement->execute();
$statement->close();

echo json_encode(['success' => true] + getAiConsentStatus($userId));
