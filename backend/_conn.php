<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Opens (and caches) the shared MySQL connection.
 *
 * Credentials come from `_conn.local.php` when present, otherwise from the
 * UTS_DB_* environment variables.
 */
function uts_db_connect(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $localConfigPath = __DIR__ . '/_conn.local.php';
    $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];

    $databaseConfig = [
        'host' => $localConfig['host'] ?? getenv('UTS_DB_HOST') ?: 'localhost',
        'user' => $localConfig['user'] ?? getenv('UTS_DB_USER') ?: '',
        'password' => $localConfig['password'] ?? getenv('UTS_DB_PASSWORD') ?: '',
        'name' => $localConfig['name'] ?? getenv('UTS_DB_NAME') ?: '',
    ];

    if ($databaseConfig['user'] === '' || $databaseConfig['name'] === '') {
        throw new RuntimeException('Database configuration is missing.');
    }

    $connection = new mysqli(
        $databaseConfig['host'],
        $databaseConfig['user'],
        $databaseConfig['password'],
        $databaseConfig['name']
    );
    $connection->set_charset('utf8mb4');

    return $connection;
}

/** Backwards-compatible handle for the original scripts. */
$conn = uts_db_connect();
