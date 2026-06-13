<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TunnelSourceTableService
{
    /**
     * @var array<string, array{label: string, description: string}>
     */
    private const TABLES = [
        'm_tunnels' => [
            'label' => 'Induk Terowongan',
            'description' => 'Data utama terowongan pada database prasarana_tunnel.',
        ],
        'm_tunnel_structures' => [
            'label' => 'Struktur Terowongan',
            'description' => 'Detail struktur, material, konstruksi, dan rehabilitasi terowongan.',
        ],
        'm_tunnel_specs' => [
            'label' => 'Spesifikasi Terowongan',
            'description' => 'Spesifikasi jalur, clearance, dimensi, gradien, dan catatan teknis.',
        ],
        'm_tunnel_docs' => [
            'label' => 'Dokumen Terowongan',
            'description' => 'Nomor dan metadata dokumen teknis terowongan.',
        ],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function catalog(): array
    {
        return collect($this->managedTables())
            ->map(fn (string $table): array => [
                ...$this->tableMeta($table),
                'table' => $table,
                'href' => route('dashboard.tunnel-source.tables.show', ['table' => $table]),
                'row_count' => $this->countRows($table),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function tablePage(string $table): array
    {
        $schema = $this->schema($table);

        return [
            ...$schema,
            'columns' => $this->visibleColumns($schema['columns']),
            'schema_columns' => $schema['columns'],
            'form_columns' => $this->formColumns($schema['columns']),
            'list_endpoint' => route('dashboard.tunnel-source.tables.rows', ['table' => $table]),
            'store_endpoint' => route('dashboard.tunnel-source.tables.rows.store', ['table' => $table]),
            'update_endpoint' => route('dashboard.tunnel-source.tables.rows.update', ['table' => $table, 'rowKey' => '__row__']),
            'template_endpoint' => route('dashboard.tunnel-source.tables.template', ['table' => $table]),
            'import_endpoint' => route('dashboard.tunnel-source.tables.import', ['table' => $table]),
            'export_endpoint' => route('dashboard.tunnel-source.tables.export', ['table' => $table]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(string $table): array
    {
        $this->assertManagedTable($table);

        if (! Schema::connection($this->connectionName())->hasTable($table)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel '.$table.' belum tersedia di database tunnel.'],
            ]);
        }

        $connection = DB::connection($this->connectionName());
        [$columns, $indexes] = $connection->getDriverName() === 'sqlite'
            ? $this->sqliteSchema($connection, $table)
            : $this->mysqlSchema($connection, $table);

        return [
            ...$this->tableMeta($table),
            'table' => $table,
            'row_count' => $this->countRows($table),
            'primary_key' => collect($indexes)->firstWhere('name', 'PRIMARY')['columns'] ?? [],
            'required_columns' => $this->requiredColumns($table, $columns),
            'columns' => $columns,
            'indexes' => $indexes,
        ];
    }

    public function paginate(string $table, array $filters = []): LengthAwarePaginator
    {
        $this->assertManagedTable($table);

        $perPage = min(max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1), config('master-data.pagination.max_per_page'));
        $page = max((int) ($filters['page'] ?? 1), 1);
        $search = trim((string) ($filters['search'] ?? ''));
        $columns = $this->columnNames($table);
        $query = DB::connection($this->connectionName())->table($table);

        if (in_array('deleted_at', $columns, true)) {
            $query->whereNull('deleted_at');
        }

        if ($search !== '' && $columns !== []) {
            $query->where(function ($builder) use ($columns, $search): void {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        $total = (clone $query)->count();
        $this->applyDefaultOrdering($query, $columns);

        $rows = $query->forPage($page, $perPage)->get()
            ->map(fn (object $row, int $index): array => $this->wrapRow($table, (array) $row, $index))
            ->all();

        return new Paginator($rows, $total, $perPage, $page, [
            'path' => request()->url(),
            'pageName' => 'page',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(string $table, array $payload): array
    {
        $schema = $this->schema($table);
        $data = $this->normalizePayload($table, $payload['data'] ?? [], $schema['columns']);
        $this->validateRequiredColumns($data, $schema['required_columns']);

        try {
            $id = $this->insertAndResolveId($table, $data, $schema);
        } catch (QueryException $exception) {
            throw ValidationException::withMessages([
                'data' => [$exception->getPrevious()?->getMessage() ?: $exception->getMessage()],
            ]);
        }

        $row = $id !== null
            ? $this->findByExactColumn($table, 'id', $id)
            : $this->findFromPayload($table, $data);

        if ($row === null) {
            throw ValidationException::withMessages([
                'data' => ['Record berhasil dibuat, tetapi tidak dapat dibaca ulang otomatis.'],
            ]);
        }

        return $this->wrapRow($table, $row, 0);
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $table, string $rowKey): array
    {
        $this->assertManagedTable($table);
        $row = $this->findRowByKey($table, $rowKey);

        if ($row === null) {
            throw ValidationException::withMessages([
                'row_key' => ['Record tidak ditemukan pada tabel '.$table.'.'],
            ]);
        }

        return $this->wrapRow($table, $row, 0);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $table, string $rowKey, array $payload): array
    {
        $schema = $this->schema($table);
        $current = $this->find($table, $rowKey);
        $data = $this->normalizePayload($table, $payload['data'] ?? [], $schema['columns'], creating: false);

        if ($data === []) {
            throw ValidationException::withMessages([
                'data' => ['Tidak ada kolom yang dapat diperbarui.'],
            ]);
        }

        try {
            $updated = $this->rowLocatorQuery($table, $rowKey)->update($data);
        } catch (QueryException $exception) {
            throw ValidationException::withMessages([
                'data' => [$exception->getPrevious()?->getMessage() ?: $exception->getMessage()],
            ]);
        }

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'row_key' => ['Record tidak ditemukan pada tabel '.$table.'.'],
            ]);
        }

        return $this->find($table, $current['row_key']);
    }

    /**
     * @return array<int, string>
     */
    public function csvColumns(string $table): array
    {
        $schema = $this->schema($table);

        return collect($this->formColumns($schema['columns']))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{created: int, errors: array<int, array<string, mixed>>}
     */
    public function importCsv(string $table, string $path): array
    {
        $allowedColumns = array_fill_keys($this->csvColumns($table), true);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => ['File CSV tidak dapat dibaca.'],
            ]);
        }

        $created = 0;
        $errors = [];

        try {
            $headers = fgetcsv($handle);

            if (! is_array($headers) || $headers === []) {
                throw ValidationException::withMessages([
                    'file' => ['Header CSV tidak ditemukan.'],
                ]);
            }

            $headers = array_map(
                fn (string $header): string => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header),
                $headers,
            );

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($this->csvRowIsEmpty($row)) {
                    continue;
                }

                $data = [];

                foreach ($headers as $index => $header) {
                    if (! isset($allowedColumns[$header])) {
                        continue;
                    }

                    $value = $row[$index] ?? null;
                    $data[$header] = is_string($value) ? trim($value) : $value;
                }

                try {
                    $this->create($table, ['data' => $data]);
                    $created++;
                } catch (ValidationException $exception) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'messages' => $exception->errors(),
                    ];
                }
            }
        } finally {
            fclose($handle);
        }

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }

    /**
     * @param  resource  $handle
     */
    public function streamCsv(string $table, mixed $handle, bool $includeRows = true): void
    {
        $columns = $this->csvColumns($table);

        fputcsv($handle, $columns);

        if (! $includeRows) {
            return;
        }

        $query = DB::connection($this->connectionName())->table($table);
        $tableColumns = $this->columnNames($table);

        if (in_array('deleted_at', $tableColumns, true)) {
            $query->whereNull('deleted_at');
        }

        $this->applyDefaultOrdering($query, $tableColumns);

        $query->chunk(500, function ($rows) use ($handle, $columns): void {
            foreach ($rows as $row) {
                $data = (array) $row;
                fputcsv($handle, array_map(
                    fn (string $column): mixed => $this->csvValue($data[$column] ?? null),
                    $columns,
                ));
            }
        });
    }

    private function connectionName(): string
    {
        return 'tunnel';
    }

    /**
     * @return array<int, string>
     */
    public function managedTables(): array
    {
        $tables = $this->actualTunnelTables();

        return $tables === [] ? array_keys(self::TABLES) : $tables;
    }

    private function assertManagedTable(string $table): void
    {
        if (! in_array($table, $this->managedTables(), true)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel tidak terdaftar untuk modul Terowongan.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function tableMeta(string $table): array
    {
        $this->assertManagedTable($table);

        if (isset(self::TABLES[$table])) {
            return self::TABLES[$table];
        }

        $label = (string) Str::of($table)
            ->replaceFirst('m_', '')
            ->replace('_', ' ')
            ->title();

        return [
            'label' => $label,
            'description' => 'Tabel source modul terowongan pada database prasarana_tunnel.',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function actualTunnelTables(): array
    {
        $connection = DB::connection($this->connectionName());

        $tables = $connection->getDriverName() === 'sqlite'
            ? collect($connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
                ->pluck('name')
            : collect($connection->select('SELECT table_name AS table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name'))
                ->pluck('table_name');

        $sortOrder = array_flip(array_keys(self::TABLES));

        return $tables
            ->filter(fn (mixed $table): bool => is_string($table) && preg_match('/^m_tunnels?$|^m_tunnel_/', $table) === 1)
            ->sortBy(fn (string $table): string => sprintf('%03d-%s', $sortOrder[$table] ?? 999, $table))
            ->values()
            ->all();
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function mysqlSchema(Connection $connection, string $table): array
    {
        $columns = collect($connection->select('SHOW FULL COLUMNS FROM `'.$table.'`'))
            ->map(function (object $column): array {
                $row = (array) $column;

                return [
                    'name' => $row['Field'] ?? null,
                    'type' => $row['Type'] ?? null,
                    'collation' => $row['Collation'] ?? null,
                    'nullable' => ($row['Null'] ?? 'YES') === 'YES',
                    'key' => $row['Key'] ?? null,
                    'default' => $row['Default'] ?? null,
                    'extra' => $row['Extra'] ?? null,
                    'comment' => $row['Comment'] ?? null,
                ];
            })
            ->values()
            ->all();
        $indexes = collect($connection->select('SHOW INDEX FROM `'.$table.'`'))
            ->map(fn (object $index): array => (array) $index)
            ->groupBy('Key_name')
            ->map(function ($group, string $name): array {
                $columns = collect($group)->sortBy('Seq_in_index')->pluck('Column_name')->values()->all();
                $first = $group->first();

                return [
                    'name' => $name,
                    'unique' => ((int) ($first['Non_unique'] ?? 1)) === 0,
                    'columns' => $columns,
                    'type' => $first['Index_type'] ?? null,
                ];
            })
            ->values()
            ->all();

        return [$columns, $indexes];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function sqliteSchema(Connection $connection, string $table): array
    {
        $columns = collect($connection->select('PRAGMA table_info(`'.$table.'`)'))
            ->map(fn (object $column): array => [
                'name' => $column->name,
                'type' => $column->type,
                'collation' => null,
                'nullable' => ((int) $column->notnull) === 0,
                'key' => ((int) $column->pk) > 0 ? 'PRI' : null,
                'default' => $column->dflt_value,
                'extra' => ((int) $column->pk) > 0 ? 'auto_increment' : null,
                'comment' => null,
            ])
            ->values()
            ->all();
        $primary = collect($columns)
            ->filter(fn (array $column): bool => $column['key'] === 'PRI')
            ->pluck('name')
            ->values()
            ->all();
        $indexes = $primary === [] ? [] : [[
            'name' => 'PRIMARY',
            'unique' => true,
            'columns' => $primary,
            'type' => null,
        ]];

        return [$columns, $indexes];
    }

    /**
     * @return array<int, string>
     */
    private function columnNames(string $table): array
    {
        return DB::connection($this->connectionName())->getSchemaBuilder()->getColumnListing($table);
    }

    private function countRows(string $table): int
    {
        if (! Schema::connection($this->connectionName())->hasTable($table)) {
            return 0;
        }

        $query = DB::connection($this->connectionName())->table($table);

        if (in_array('deleted_at', $this->columnNames($table), true)) {
            $query->whereNull('deleted_at');
        }

        return (int) $query->count();
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, string>
     */
    private function visibleColumns(array $columns): array
    {
        $hidden = ['id', 'deleted_at'];

        return collect($columns)
            ->pluck('name')
            ->filter(fn (?string $name): bool => is_string($name) && ! in_array($name, $hidden, true))
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, array<string, mixed>>
     */
    private function formColumns(array $columns): array
    {
        return collect($columns)
            ->filter(function (array $column): bool {
                $name = (string) ($column['name'] ?? '');
                $extra = (string) ($column['extra'] ?? '');

                return $name !== ''
                    && ! in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true)
                    && ! str_contains($extra, 'auto_increment');
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, string>
     */
    private function requiredColumns(string $table, array $columns): array
    {
        return collect($columns)
            ->map(function (array $column) use ($table): ?string {
                $name = (string) ($column['name'] ?? '');
                $nullable = (bool) ($column['nullable'] ?? true);
                $default = $column['default'] ?? null;
                $extra = (string) ($column['extra'] ?? '');

                if ($name === '' || $nullable || $default !== null || str_contains($extra, 'auto_increment')) {
                    return null;
                }

                if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                    return null;
                }

                if ($table === 'm_tunnels' && $name === 'tunnel_id') {
                    return null;
                }

                return $name;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<string, mixed>
     */
    private function normalizePayload(string $table, array $payload, array $columns, bool $creating = true): array
    {
        $columnMap = collect($columns)->keyBy('name');
        $allowed = $columnMap->keys()->all();
        $data = [];

        foreach ($payload as $key => $value) {
            if (! is_string($key) || ! in_array($key, $allowed, true)) {
                continue;
            }

            $column = $columnMap[$key] ?? [];
            $extra = (string) ($column['extra'] ?? '');

            if (str_contains($extra, 'auto_increment')) {
                continue;
            }

            $data[$key] = $this->normalizeValue($value, (string) ($column['type'] ?? ''));
        }

        if ($creating && $table === 'm_tunnels' && blank($data['tunnel_id'] ?? null)) {
            $data['tunnel_id'] = (string) Str::ulid();
        }

        $timestamp = now();

        if ($creating && in_array('created_at', $allowed, true) && ! array_key_exists('created_at', $data)) {
            $data['created_at'] = $timestamp;
        }

        if (in_array('updated_at', $allowed, true) && ! array_key_exists('updated_at', $data)) {
            $data['updated_at'] = $timestamp;
        }

        return array_filter($data, fn (mixed $value): bool => $value !== '');
    }

    private function normalizeValue(mixed $value, string $type): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            return null;
        }

        if (str_contains(strtolower($type), 'json') && is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

        return $value;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function csvRowIsEmpty(array $row): bool
    {
        return collect($row)
            ->every(fn (mixed $value): bool => trim((string) $value) === '');
    }

    private function csvValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $requiredColumns
     */
    private function validateRequiredColumns(array $data, array $requiredColumns): void
    {
        $missing = [];

        foreach ($requiredColumns as $column) {
            if (! array_key_exists($column, $data) || $data[$column] === null || $data[$column] === '') {
                $missing[] = $column;
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'data' => ['Kolom wajib belum lengkap: '.implode(', ', $missing).'.'],
            ]);
        }
    }

    private function insertAndResolveId(string $table, array $data, array $schema): int|string|null
    {
        $primaryKey = $schema['primary_key'][0] ?? null;
        $connection = DB::connection($this->connectionName());

        if ($primaryKey === 'id') {
            return $connection->table($table)->insertGetId($data);
        }

        $connection->table($table)->insert($data);

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function findFromPayload(string $table, array $data): ?array
    {
        foreach (['tunnel_id', 'kode_aset', 'id'] as $candidate) {
            if (($data[$candidate] ?? null) === null || $data[$candidate] === '') {
                continue;
            }

            $row = $this->findByExactColumn($table, $candidate, $data[$candidate]);

            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findByExactColumn(string $table, string $column, mixed $value): ?array
    {
        if (! in_array($column, $this->columnNames($table), true)) {
            return null;
        }

        $row = DB::connection($this->connectionName())->table($table)->where($column, $value)->first();

        return $row instanceof \stdClass ? (array) $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findRowByKey(string $table, string $rowKey): ?array
    {
        foreach ($this->keyColumnsForRowKey($table, $rowKey) as $column) {
            $row = $this->findByExactColumn($table, $column, $rowKey);

            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    private function rowLocatorQuery(string $table, string $rowKey): \Illuminate\Database\Query\Builder
    {
        $query = DB::connection($this->connectionName())->table($table);
        $row = $this->findRowByKey($table, $rowKey);

        if ($row !== null) {
            foreach ($this->keyColumns($table) as $column) {
                $value = $row[$column] ?? null;

                if ($value !== null && $value !== '') {
                    return $query->where($column, $value);
                }
            }
        }

        return $query->where(function ($builder) use ($table, $rowKey): void {
            foreach ($this->keyColumnsForRowKey($table, $rowKey) as $index => $column) {
                if ($index === 0) {
                    $builder->where($column, $rowKey);

                    continue;
                }

                $builder->orWhere($column, $rowKey);
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function keyColumns(string $table): array
    {
        $columns = $this->columnNames($table);

        return array_values(array_filter(
            ['id', 'tunnel_id', 'kode_aset'],
            fn (string $column): bool => in_array($column, $columns, true),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function keyColumnsForRowKey(string $table, string $rowKey): array
    {
        return array_values(array_filter(
            $this->keyColumns($table),
            fn (string $column): bool => $column !== 'id' || ctype_digit($rowKey),
        ));
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function applyDefaultOrdering(\Illuminate\Database\Query\Builder $query, array $columns): void
    {
        if (in_array('updated_at', $columns, true)) {
            $query->orderByDesc('updated_at');
        } elseif (in_array('created_at', $columns, true)) {
            $query->orderByDesc('created_at');
        } elseif (in_array('id', $columns, true)) {
            $query->orderByDesc('id');
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function wrapRow(string $table, array $row, int $index): array
    {
        return [
            'table' => $table,
            'row_key' => $this->resolveRowKey($row, $index),
            'data' => $row,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveRowKey(array $row, int $index): string
    {
        foreach (['id', 'tunnel_id', 'kode_aset'] as $candidate) {
            $value = $row[$candidate] ?? null;

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return 'row-'.$index;
    }
}
