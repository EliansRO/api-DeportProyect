<?php

function env(string $key, $default = null): ?string {
    $path = __DIR__ . '/../../.env';

    if (!file_exists($path)) return $default;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;

        [$envKey, $envValue] = explode('=', $line, 2);

        if (trim($envKey) === $key) {
            return trim($envValue);
        }
    }

    return $default;
}
