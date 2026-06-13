<?php

return [
    'pagination' => [
        'default_per_page' => (int) env('MASTER_DATA_DEFAULT_PER_PAGE', 25),
        'max_per_page' => (int) env('MASTER_DATA_MAX_PER_PAGE', 100),
    ],
    'import' => [
        'chunk_size' => (int) env('MASTER_DATA_IMPORT_CHUNK_SIZE', 1000),
        'preview_limit' => (int) env('MASTER_DATA_MAPPING_PREVIEW_LIMIT', 10),
    ],
    'cache' => [
        'enabled' => env('MASTER_DATA_CACHE_ENABLED', true),
        'ttl' => (int) env('MASTER_DATA_CACHE_TTL', 300),
    ],
    'api' => [
        'default_rate_limit_per_minute' => (int) env('API_DEFAULT_RATE_LIMIT_PER_MINUTE', 60),
        'default_rate_limit_per_day' => (int) env('API_DEFAULT_RATE_LIMIT_PER_DAY', 10000),
        'log_retention_days' => (int) env('API_LOG_RETENTION_DAYS', 90),
    ],
    'retention' => [
        'import_file_days' => (int) env('IMPORT_FILE_RETENTION_DAYS', 30),
        'import_error_days' => (int) env('IMPORT_ERROR_RETENTION_DAYS', 180),
    ],
    'swagger' => [
        'enabled' => env('SWAGGER_ENABLED', true),
        'require_auth' => env('SWAGGER_REQUIRE_AUTH', true),
    ],
    'validation' => [
        'allowed_rules' => [
            'array',
            'between',
            'boolean',
            'date',
            'email',
            'in',
            'integer',
            'json',
            'max',
            'min',
            'nullable',
            'numeric',
            'regex',
            'required',
            'required_if',
            'sometimes',
            'string',
        ],
    ],
    'mapping' => [
        'allowed_transformations' => [
            'trim',
            'uppercase',
            'lowercase',
            'title_case',
            'nullable_string',
            'integer',
            'nullable_integer',
            'float',
            'nullable_float',
            'boolean',
            'date',
            'datetime',
            'null_if_empty',
            'normalize_whitespace',
            'remove_control_characters',
            'normalize_code',
            'decimal_comma_to_dot',
        ],
    ],
    'bridge_source' => [
        'connection' => env('MASTER_DATA_BRIDGE_SOURCE_CONNECTION', 'bridge'),
        'dump_path' => env('MASTER_DATA_BRIDGE_SOURCE_DUMP_PATH', 'database/data/data_jembatan.sql'),
        'tables' => \App\Support\BridgeSource\BridgeSourceSql::SOURCE_TABLES,
    ],
];
