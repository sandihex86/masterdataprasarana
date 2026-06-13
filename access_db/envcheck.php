<?php

declare(strict_types=1);

$envPath = '/home/sandi/template/prasarana/.env';

header('Content-Type: text/plain; charset=UTF-8');

if (!is_file($envPath)) {
    http_response_code(500);
    exit("ENV file not found\n");
}

if (!is_readable($envPath)) {
    http_response_code(500);
    exit("ENV file is not readable\n");
}

$content = file_get_contents($envPath);

if ($content === false) {
    http_response_code(500);
    exit("ENV file cannot be loaded\n");
}

$required = [
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
];

foreach ($required as $key) {
    if (!preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
        http_response_code(500);
        exit("Missing required key: {$key}\n");
    }
}

echo "ENV configuration is readable and complete.\n";
