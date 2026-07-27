<?php

require_once __DIR__ . '/../config/database.php';

const AI_MAX_JSON_REQUEST_BYTES = 200000;
const AI_MAX_MESSAGE_BYTES = 4000;
const AI_BURST_WINDOW_SECONDS = 60;
const AI_BURST_LIMIT = 10;
const AI_DAILY_WINDOW_SECONDS = 86400;
const AI_DAILY_LIMIT = 100;

function rejectAiRequest(string $message, int $statusCode, ?int $retryAfter = null): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    if ($retryAfter !== null) {
        header('Retry-After: ' . max(1, $retryAfter));
    }
    echo json_encode(['success' => false, 'error' => $message, 'message' => $message]);
    exit;
}

/** @return array<string, mixed> */
function readLimitedJsonRequest(int $maximumBytes = AI_MAX_JSON_REQUEST_BYTES): array
{
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    if ($contentLength > $maximumBytes) {
        rejectAiRequest('Request body is too large.', 413);
    }

    $rawBody = file_get_contents('php://input', false, null, 0, $maximumBytes + 1);
    if ($rawBody === false) {
        rejectAiRequest('Unable to read request body.', 400);
    }
    if (strlen($rawBody) > $maximumBytes) {
        rejectAiRequest('Request body is too large.', 413);
    }

    $input = json_decode($rawBody, true);
    if (!is_array($input)) {
        rejectAiRequest('Request body must contain valid JSON.', 400);
    }

    return $input;
}

function enforceAiRateLimit(int $userId, string $action, int $cost = 1): void
{
    if ($userId <= 0 || !preg_match('/^[a-z0-9-]{1,64}$/', $action) || $cost < 1 || $cost > 10) {
        rejectAiRequest('Unable to authorize AI request.', 400);
    }

    $connection = getDBConnection();
    if (!$connection) {
        rejectAiRequest('AI service is temporarily unavailable.', 503);
    }

    $now = time();
    $minuteStart = intdiv($now, AI_BURST_WINDOW_SECONDS) * AI_BURST_WINDOW_SECONDS;
    $dayStart = intdiv($now, AI_DAILY_WINDOW_SECONDS) * AI_DAILY_WINDOW_SECONDS;

    try {
        $actionCounts = incrementAiUsageCounters(
            $connection,
            $userId,
            $action,
            $minuteStart,
            $dayStart,
            $cost
        );

        if ($actionCounts['minute_count'] > AI_BURST_LIMIT) {
            rejectAiRequest(
                'Too many AI requests. Please wait before trying again.',
                429,
                ($minuteStart + AI_BURST_WINDOW_SECONDS) - $now
            );
        }

        $dailyCounts = incrementAiUsageCounters(
            $connection,
            $userId,
            'all-ai-actions',
            $minuteStart,
            $dayStart,
            $cost
        );

        if ($dailyCounts['daily_count'] > AI_DAILY_LIMIT) {
            rejectAiRequest(
                'Daily AI usage limit reached. Please try again tomorrow.',
                429,
                ($dayStart + AI_DAILY_WINDOW_SECONDS) - $now
            );
        }
    } catch (Throwable $e) {
        error_log('AI rate-limit failure: ' . $e->getMessage());
        rejectAiRequest('AI service is temporarily unavailable.', 503);
    }
}

/**
 * @return array{minute_count: int, daily_count: int}
 */
function incrementAiUsageCounters(
    mysqli $connection,
    int $userId,
    string $action,
    int $minuteStart,
    int $dayStart,
    int $cost
): array {
    $statement = $connection->prepare(
        'INSERT INTO ai_rate_limits (
            user_id, action_name, minute_window_start, minute_count,
            daily_window_start, daily_count
        ) VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            minute_count = IF(
                minute_window_start = VALUES(minute_window_start),
                minute_count + VALUES(minute_count),
                VALUES(minute_count)
            ),
            minute_window_start = VALUES(minute_window_start),
            daily_count = IF(
                daily_window_start = VALUES(daily_window_start),
                daily_count + VALUES(daily_count),
                VALUES(daily_count)
            ),
            daily_window_start = VALUES(daily_window_start),
            updated_at = CURRENT_TIMESTAMP'
    );

    if (!$statement) {
        throw new RuntimeException('Unable to prepare AI usage update.');
    }

    $statement->bind_param(
        'isiiii',
        $userId,
        $action,
        $minuteStart,
        $cost,
        $dayStart,
        $cost
    );
    if (!$statement->execute()) {
        $statement->close();
        throw new RuntimeException('Unable to update AI usage.');
    }
    $statement->close();

    $statement = $connection->prepare(
        'SELECT minute_count, daily_count
         FROM ai_rate_limits
         WHERE user_id = ? AND action_name = ?'
    );
    if (!$statement) {
        throw new RuntimeException('Unable to prepare AI usage lookup.');
    }
    $statement->bind_param('is', $userId, $action);
    if (!$statement->execute()) {
        $statement->close();
        throw new RuntimeException('Unable to read AI usage.');
    }

    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $statement->close();

    if (!$row) {
        throw new RuntimeException('AI usage counter was not found.');
    }

    return [
        'minute_count' => (int) $row['minute_count'],
        'daily_count' => (int) $row['daily_count'],
    ];
}
