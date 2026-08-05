<?php

require_once __DIR__ . '/../config/database.php';

const AI_CONSENT_VERSION = '2026-08-05';

function getAiConsentStatus(int $userId): array
{
    $connection = getDBConnection();
    if (!$connection) {
        throw new RuntimeException('Database service is unavailable.');
    }

    $statement = $connection->prepare(
        'SELECT ai_processing_consent_at, ai_consent_version
         FROM users
         WHERE user_id = ?'
    );
    $statement->bind_param('i', $userId);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();

    $granted = is_array($row)
        && !empty($row['ai_processing_consent_at'])
        && hash_equals(AI_CONSENT_VERSION, (string) ($row['ai_consent_version'] ?? ''));

    return [
        'granted' => $granted,
        'version' => AI_CONSENT_VERSION,
        'granted_at' => $granted ? $row['ai_processing_consent_at'] : null,
    ];
}

function requireAiProcessingConsent(int $userId): void
{
    if (!getAiConsentStatus($userId)['granted']) {
        sendApiError(
            'AI processing consent is required before sending resume data.',
            428,
            'error'
        );
    }
}
