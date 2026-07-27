<?php

class RequestValidationException extends InvalidArgumentException
{
}

/** @return array<string, mixed> */
function readValidatedJsonBody(int $maximumBytes = 262144): array
{
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    if ($contentLength > $maximumBytes) {
        throw new RequestValidationException('Request body is too large.');
    }

    $rawBody = file_get_contents('php://input', false, null, 0, $maximumBytes + 1);
    if ($rawBody === false || strlen($rawBody) > $maximumBytes) {
        throw new RequestValidationException('Request body is too large.');
    }

    $input = json_decode($rawBody, true);
    if (!is_array($input)) {
        throw new RequestValidationException('Request body must contain valid JSON.');
    }

    return $input;
}

function requiredStringField(array $input, string $field, string $label, int $maximumBytes): string
{
    if (!isset($input[$field]) || !is_string($input[$field])) {
        throw new RequestValidationException($label . ' is required.');
    }

    $value = trim($input[$field]);
    if ($value === '') {
        throw new RequestValidationException($label . ' is required.');
    }
    validateStringLength($value, $label, $maximumBytes);
    return $value;
}

function optionalStringField(
    array $input,
    string $field,
    string $label,
    int $maximumBytes,
    ?string $default = null
): ?string {
    if (!array_key_exists($field, $input) || $input[$field] === null || $input[$field] === '') {
        return $default;
    }
    if (!is_string($input[$field])) {
        throw new RequestValidationException($label . ' must be text.');
    }

    $value = trim($input[$field]);
    validateStringLength($value, $label, $maximumBytes);
    return $value === '' ? $default : $value;
}

function validateStringLength(string $value, string $label, int $maximumBytes): void
{
    if (strlen($value) > $maximumBytes) {
        throw new RequestValidationException(
            sprintf('%s must be %d characters or fewer.', $label, $maximumBytes)
        );
    }
}

/** @param string[] $allowedValues */
function enumField(
    array $input,
    string $field,
    string $label,
    array $allowedValues,
    ?string $default = null
): string {
    $value = optionalStringField($input, $field, $label, 64, $default);
    if ($value === null || !in_array($value, $allowedValues, true)) {
        throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
    }
    return $value;
}

function positiveIntegerField(array $input, string $field, string $label): int
{
    $value = $input[$field] ?? null;
    $validated = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if ($validated === false) {
        throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
    }
    return (int) $validated;
}

function booleanField(array $input, string $field, string $label, bool $default = false): bool
{
    if (!array_key_exists($field, $input)) {
        return $default;
    }
    if (is_bool($input[$field])) {
        return $input[$field];
    }
    if ($input[$field] === 0 || $input[$field] === 1 || $input[$field] === '0' || $input[$field] === '1') {
        return (bool) $input[$field];
    }
    throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
}

function dateField(
    array $input,
    string $field,
    string $label,
    bool $required = false
): ?string {
    $value = optionalStringField($input, $field, $label, 10);
    if ($value === null) {
        if ($required) {
            throw new RequestValidationException($label . ' is required.');
        }
        return null;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
    }
    return $date->format('Y-m-d');
}

function dateTimeField(array $input, string $field, string $label): ?string
{
    $value = optionalStringField($input, $field, $label, 25);
    if ($value === null) {
        return null;
    }

    foreach (['!Y-m-d\TH:i', '!Y-m-d H:i:s', '!Y-m-d H:i'] as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format('Y-m-d H:i:s');
        }
    }
    throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
}

function monthField(array $input, string $field, string $label): ?string
{
    $value = optionalStringField($input, $field, $label, 7);
    if ($value === null) {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value . '-01');
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
    }
    return $date->format('Y-m');
}

function optionalEmailField(array $input, string $field, string $label = 'Email'): ?string
{
    $value = optionalStringField($input, $field, $label, 255);
    if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
    }
    return $value;
}

function optionalHttpUrlField(array $input, string $field, string $label, int $maximumBytes = 500): ?string
{
    $value = optionalStringField($input, $field, $label, $maximumBytes);
    if ($value === null) {
        return null;
    }

    $parts = parse_url($value);
    if (!filter_var($value, FILTER_VALIDATE_URL)
        || !is_array($parts)
        || !isset($parts['scheme'])
        || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
    }
    return $value;
}

function encodedStructuredField(
    array $input,
    string $field,
    string $label,
    int $maximumBytes,
    string $default = '[]',
    bool $allowPlainText = false
): string {
    if (!array_key_exists($field, $input) || $input[$field] === null || $input[$field] === '') {
        return $default;
    }

    if (is_array($input[$field])) {
        $value = json_encode($input[$field]);
        if ($value === false) {
            throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
        }
    } elseif (is_string($input[$field])) {
        $value = $input[$field];
        if (!$allowPlainText) {
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                throw new RequestValidationException('Invalid ' . strtolower($label) . '.');
            }
        }
    } else {
        throw new RequestValidationException($label . ' has an invalid type.');
    }

    validateStringLength($value, $label, $maximumBytes);
    return $value;
}

function sendRequestValidationError(RequestValidationException $exception): void
{
    $isOversized = stripos($exception->getMessage(), 'too large') !== false;
    http_response_code($isOversized ? 413 : 400);
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
        'error' => $exception->getMessage(),
    ]);
    exit;
}
