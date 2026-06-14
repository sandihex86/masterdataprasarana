<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

$blockedNonTestingDatabases = ['prasarana_test', 'prasarana_testing'];
$assertNotTestingDatabase = static function (?string $database, string $envKey) use ($blockedNonTestingDatabases): void {
    if (env('APP_ENV') === 'testing' || $database === null || $database === '') {
        return;
    }

    if (in_array(Str::lower($database), $blockedNonTestingDatabases, true)) {
        throw new RuntimeException("Database [{$database}] dari [{$envKey}] hanya boleh dipakai saat APP_ENV=testing.");
    }
};

$assertNotTestingDatabase(env('DB_DATABASE'), 'DB_DATABASE');
$assertNotTestingDatabase(env('CORE_DB_DATABASE'), 'CORE_DB_DATABASE');
$assertNotTestingDatabase(env('REFERENCE_DB_DATABASE'), 'REFERENCE_DB_DATABASE');
$assertNotTestingDatabase(env('BRIDGE_DB_DATABASE'), 'BRIDGE_DB_DATABASE');
$assertNotTestingDatabase(env('TUNNEL_DB_DATABASE'), 'TUNNEL_DB_DATABASE');
$assertNotTestingDatabase(env('WAREHOUSE_DB_DATABASE'), 'WAREHOUSE_DB_DATABASE');

$mysqlConnection = static function (string $prefix, ?string $fallbackPrefix = null): array {
    $databaseDefault = $prefix === 'DB' ? 'laravel' : '';
    $usernameDefault = $prefix === 'DB' ? 'root' : '';

    $env = static function (string $suffix, mixed $default = null) use ($prefix, $fallbackPrefix): mixed {
        $key = $prefix.'_'.$suffix;

        if ($fallbackPrefix !== null) {
            return env($key, env($fallbackPrefix.'_'.$suffix, $default));
        }

        return env($key, $default);
    };

    return [
        'driver' => $env('CONNECTION', 'mysql'),
        'url' => $env('URL'),
        'host' => $env('HOST', '127.0.0.1'),
        'port' => $env('PORT', '3306'),
        'database' => $env('DATABASE', $databaseDefault),
        'username' => $env('USERNAME', $usernameDefault),
        'password' => $env('PASSWORD', ''),
        'unix_socket' => $env('SOCKET', ''),
        'charset' => $env('CHARSET', 'utf8mb4'),
        'collation' => $env('COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            Mysql::ATTR_SSL_CA => $env('SSL_CA'),
        ]) : [],
    ];
};

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => array_merge($mysqlConnection('DB'), [
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA', env('DB_SSL_CA')),
            ]) : [],
        ]),

        'core' => $mysqlConnection('CORE_DB', 'DB'),

        'reference' => $mysqlConnection('REFERENCE_DB', 'CORE_DB'),

        'bridge' => $mysqlConnection('BRIDGE_DB'),

        'tunnel' => $mysqlConnection('TUNNEL_DB'),

        'track' => $mysqlConnection('TRACK_DB'),

        'operational_facility' => $mysqlConnection('OPERATIONAL_FACILITY_DB'),

        'certificate' => $mysqlConnection('CERTIFICATE_DB'),

        'warehouse' => $mysqlConnection('WAREHOUSE_DB'),

        'reporting' => $mysqlConnection('REPORTING_DB'),

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
