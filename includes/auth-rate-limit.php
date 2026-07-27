<?php

require_once __DIR__ . '/../config/database.php';

const LOGIN_ACCOUNT_LIMIT = 5;
const LOGIN_IP_LIMIT = 20;
const LOGIN_WINDOW_SECONDS = 900;
const REGISTRATION_IP_LIMIT = 5;
const REGISTRATION_WINDOW_SECONDS = 3600;

function requestIpAddress(): string
{
    $address = $_SERVER['REMOTE_ADDR'] ?? '';
    return is_string($address) && filter_var($address, FILTER_VALIDATE_IP)
        ? $address
        : 'unknown';
}

function consumeLoginRateLimit(string $email): int
{
    $normalizedEmail = strtolower(trim($email));
    $accountKey = hash('sha256', 'account:' . $normalizedEmail);
    $ipKey = hash('sha256', 'ip:' . requestIpAddress());

    try {
        $accountRetry = consumeAuthRateLimit(
            'login-account',
            $accountKey,
            LOGIN_WINDOW_SECONDS,
            LOGIN_ACCOUNT_LIMIT
        );
        $ipRetry = consumeAuthRateLimit(
            'login-ip',
            $ipKey,
            LOGIN_WINDOW_SECONDS,
            LOGIN_IP_LIMIT
        );
    } catch (Throwable $e) {
        error_log('Login rate-limit failure: ' . $e->getMessage());
        return LOGIN_WINDOW_SECONDS;
    }

    return max($accountRetry, $ipRetry);
}

function clearSuccessfulLoginLimit(string $email): void
{
    $connection = getDBConnection();
    if (!$connection) {
        return;
    }

    try {
        $action = 'login-account';
        $key = hash('sha256', 'account:' . strtolower(trim($email)));
        $statement = $connection->prepare(
            'DELETE FROM auth_rate_limits WHERE action_name = ? AND limit_key = ?'
        );
        if ($statement) {
            $statement->bind_param('ss', $action, $key);
            $statement->execute();
            $statement->close();
        }
    } catch (Throwable $e) {
        error_log('Unable to clear successful login limit: ' . $e->getMessage());
    }
}

function consumeRegistrationRateLimit(): int
{
    try {
        return consumeAuthRateLimit(
            'registration-ip',
            hash('sha256', 'ip:' . requestIpAddress()),
            REGISTRATION_WINDOW_SECONDS,
            REGISTRATION_IP_LIMIT
        );
    } catch (Throwable $e) {
        error_log('Registration rate-limit failure: ' . $e->getMessage());
        return REGISTRATION_WINDOW_SECONDS;
    }
}

/**
 * Consume one attempt and return zero when allowed or retry-after seconds when blocked.
 */
function consumeAuthRateLimit(
    string $action,
    string $limitKey,
    int $windowSeconds,
    int $maximumAttempts
): int {
    $connection = getDBConnection();
    if (!$connection) {
        return $windowSeconds;
    }

    $now = time();
    $windowStart = intdiv($now, $windowSeconds) * $windowSeconds;
    $statement = $connection->prepare(
        'INSERT INTO auth_rate_limits (action_name, limit_key, window_start, attempt_count)
         VALUES (?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
            attempt_count = IF(
                window_start = VALUES(window_start),
                attempt_count + 1,
                1
            ),
            window_start = VALUES(window_start),
            updated_at = CURRENT_TIMESTAMP'
    );
    if (!$statement) {
        error_log('Unable to prepare authentication rate-limit update.');
        return $windowSeconds;
    }

    $statement->bind_param('ssi', $action, $limitKey, $windowStart);
    if (!$statement->execute()) {
        error_log('Unable to update authentication rate limit.');
        $statement->close();
        return $windowSeconds;
    }
    $statement->close();

    $statement = $connection->prepare(
        'SELECT attempt_count FROM auth_rate_limits
         WHERE action_name = ? AND limit_key = ?'
    );
    if (!$statement) {
        return $windowSeconds;
    }
    $statement->bind_param('ss', $action, $limitKey);
    if (!$statement->execute()) {
        $statement->close();
        return $windowSeconds;
    }
    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $statement->close();

    if (!$row || (int) $row['attempt_count'] > $maximumAttempts) {
        return ($windowStart + $windowSeconds) - $now;
    }

    return 0;
}
