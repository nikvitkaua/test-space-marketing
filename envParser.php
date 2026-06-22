<?php
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;

        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

$token = $_ENV['API_TOKEN'] ?? getenv('API_TOKEN') ?: '';
$apiUrl = $_ENV['API_URL'] ?? getenv('API_URL') ?: '';