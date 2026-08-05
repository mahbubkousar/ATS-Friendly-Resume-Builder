<?php

$testCount = 0;

function assertSameValue($expected, $actual, string $message): void
{
    global $testCount;
    $testCount++;
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . '\nExpected: ' . var_export($expected, true)
            . '\nActual: ' . var_export($actual, true)
        );
    }
}

function assertTrueValue(bool $condition, string $message): void
{
    assertSameValue(true, $condition, $message);
}

function assertThrowsValidation(callable $callback, string $message): void
{
    global $testCount;
    $testCount++;
    try {
        $callback();
    } catch (RequestValidationException $exception) {
        return;
    }
    throw new RuntimeException($message);
}

function finishTests(): void
{
    global $testCount;
    echo "Passed {$testCount} assertions.\n";
}
