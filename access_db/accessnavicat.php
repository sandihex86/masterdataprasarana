<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Navicat HTTP Tunnel Bootstrap
|--------------------------------------------------------------------------
|
| File ini membaca konfigurasi database dari .env Laravel, kemudian
| menjalankan script HTTP tunnel resmi Navicat.
|
| Catatan:
| Script resmi Navicat tetap menerima credential database dari aplikasi
| Navicat. Nilai .env di sini digunakan untuk validasi dan konfigurasi lokal.
|
*/

const PROJECT_ROOT = '/home/sandi/template/prasarana';
const ENV_FILE = PROJECT_ROOT . '/.env';
const NAVICAT_TUNNEL_FILE = __DIR__ . '/ntunnel_mysql.php';

/**
 * Membaca file .env sederhana tanpa menampilkan nilainya ke client.
 *
 * Mendukung:
 * KEY=value
 * KEY="value"
 * KEY='value'
 */
function loadEnvFile(string $path): array
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Environment file is unavailable.');
    }

    $values = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException('Environment file cannot be read.');
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $separatorPosition = strpos($line, '=');

        if ($separatorPosition === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separatorPosition));
        $value = trim(substr($line, $separatorPosition + 1));

        if ($key === '') {
            continue;
        }

        if (
            strlen($value) >= 2 &&
            (
                ($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                ($value[0] === "'" && $value[strlen($value) - 1] === "'")
            )
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;

        // Tersedia bagi script lain melalui getenv(), $_ENV dan $_SERVER.
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    return $values;
}

/**
 * Mengirim respons error tanpa membocorkan detail credential.
 */
function tunnelError(string $message, int $status = 500): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');

    echo $message;
    exit;
}

try {
    $env = loadEnvFile(ENV_FILE);

    $requiredKeys = [
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    foreach ($requiredKeys as $requiredKey) {
        if (!array_key_exists($requiredKey, $env)) {
            throw new RuntimeException(
                sprintf('Required environment key %s is unavailable.', $requiredKey)
            );
        }
    }

    if (!in_array($env['DB_CONNECTION'], ['mysql', 'mariadb'], true)) {
        throw new RuntimeException('Unsupported database connection.');
    }

    if (!is_file(NAVICAT_TUNNEL_FILE)) {
        throw new RuntimeException('Navicat tunnel script is unavailable.');
    }

    if (!is_readable(NAVICAT_TUNNEL_FILE)) {
        throw new RuntimeException('Navicat tunnel script is not readable.');
    }

    /*
     * Jangan menampilkan credential atau isi .env.
     * Jalankan script tunnel resmi Navicat.
     */
    require NAVICAT_TUNNEL_FILE;
} catch (Throwable $exception) {
    error_log(
        sprintf(
            '[Navicat Tunnel] %s in %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        )
    );

    tunnelError('Database tunnel is unavailable.');
}