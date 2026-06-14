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

class WarehouseSourceTableService
{
    /**
     * @var array<string, array{label: string, description: string}>
     */
    private const TABLES = [
        'm_gudang' => [
            'label' => 'Induk Gudang',
            'description' => 'Data utama gudang pada database prasarana_warehouse.',
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
                'kind' => $this->tableKind($table),
                'href' => route('dashboard.warehouse-source.tables.show', ['table' => $table]),
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
            'kind' => $this->tableKind($table),
            'columns' => $this->visibleColumns($schema['columns']),
            'schema_columns' => $schema['columns'],
            'form_columns' => $this->formColumns($schema['columns']),
            'lookup_options' => $this->lookupOptions(),
            'database_name' => config('database.connections.warehouse.database', 'prasarana_warehouse'),
            'list_endpoint' => route('dashboard.warehouse-source.tables.rows', ['table' => $table]),
            'store_endpoint' => route('dashboard.warehouse-source.tables.rows.store', ['table' => $table]),
            'update_endpoint' => route('dashboard.warehouse-source.tables.rows.update', ['table' => $table, 'rowKey' => '__row__']),
            'delete_endpoint' => route('dashboard.warehouse-source.tables.rows.destroy', ['table' => $table, 'rowKey' => '__row__']),
            'template_endpoint' => route('dashboard.warehouse-source.tables.template', ['table' => $table]),
            'import_endpoint' => route('dashboard.warehouse-source.tables.import', ['table' => $table]),
            'export_endpoint' => route('dashboard.warehouse-source.tables.export', ['table' => $table]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mainPage(): array
    {
        return $this->tablePage('m_gudang');
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(string $table): array
    {
        $this->assertManagedTable($table);

        if (! Schema::connection($this->connectionName())->hasTable($table)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel '.$table.' belum tersedia di database warehouse.'],
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
            'required_columns' => $this->requiredColumns($columns),
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
        $data = $this->normalizePayload($payload['data'] ?? [], $schema['columns']);
        $this->validateRequiredColumns($data, $schema['required_columns']);

        try {
            $id = DB::connection($this->connectionName())->table($table)->insertGetId($data);
        } catch (QueryException $exception) {
            throw ValidationException::withMessages([
                'data' => [$exception->getPrevious()?->getMessage() ?: $exception->getMessage()],
            ]);
        }

        $row = $this->findByExactColumn($table, 'id', $id);

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
        $data = $this->normalizePayload($payload['data'] ?? [], $schema['columns'], creating: false);

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

    public function delete(string $table, string $rowKey): void
    {
        $this->assertManagedTable($table);
        $this->find($table, $rowKey);

        $payload = [
            'deleted_at' => now(),
            'updated_at' => now(),
        ];

        if (in_array('active', $this->columnNames($table), true)) {
            $payload['active'] = 0;
        }

        $deleted = $this->rowLocatorQuery($table, $rowKey)->update($payload);

        if ($deleted === 0) {
            throw ValidationException::withMessages([
                'row_key' => ['Record tidak ditemukan pada tabel '.$table.'.'],
            ]);
        }
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
        $rows = $this->csvRows($path);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => ['File CSV tidak dapat dibaca.'],
            ]);
        }

        $created = 0;
        $errors = [];
        $headers = array_shift($rows);

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
        foreach ($rows as $row) {
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
        return 'warehouse';
    }

    /**
     * @return array<int, string>
     */
    private function managedTables(): array
    {
        return array_keys(self::TABLES);
    }

    private function assertManagedTable(string $table): void
    {
        if (! in_array($table, $this->managedTables(), true)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel tidak terdaftar untuk modul Gudang.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function tableMeta(string $table): array
    {
        $this->assertManagedTable($table);

        return self::TABLES[$table];
    }

    private function tableKind(string $table): string
    {
        return $table === 'm_gudang' ? 'master' : 'detail';
    }

    /**
     * @return array<string, array<int, array{value: string, label: string}>>
     */
    private function lookupOptions(): array
    {
        return [
            'active' => [
                ['value' => '1', 'label' => 'AKTIF'],
                ['value' => '0', 'label' => 'NONAKTIF'],
            ],
        ];
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
            ->map(function (object $column): array {
                $type = strtolower((string) $column->type);
                $isPrimary = ((int) $column->pk) > 0;

                return [
                    'name' => $column->name,
                    'type' => $column->type,
                    'collation' => null,
                    'nullable' => ((int) $column->notnull) === 0,
                    'key' => $isPrimary ? 'PRI' : null,
                    'default' => $column->dflt_value,
                    'extra' => $isPrimary && str_contains($type, 'int') ? 'auto_increment' : null,
                    'comment' => null,
                ];
            })
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
        $preferred = [
            'id_gudang',
            'kode_gudang',
            'nama_gudang',
            'tipe_gudang',
            'id_wilker',
            'id_prov',
            'id_kabkot',
            'lat',
            'long',
            'active',
        ];
        $available = collect($columns)->pluck('name')->filter()->all();

        return collect($preferred)
            ->filter(fn (string $column): bool => in_array($column, $available, true))
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
                    && ! in_array($name, ['id', 'id_gudang', 'kode_gudang', 'created_at', 'updated_at', 'deleted_at'], true)
                    && ! str_contains($extra, 'auto_increment');
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, string>
     */
    private function requiredColumns(array $columns): array
    {
        return collect($columns)
            ->map(function (array $column): ?string {
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
    private function normalizePayload(array $payload, array $columns, bool $creating = true): array
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

        if ($creating) {
            $identity = $this->normalizeWarehouseIdentity($data['kode_gudang'] ?? $data['id_gudang'] ?? null);

            if (in_array('id_gudang', $allowed, true)) {
                $data['id_gudang'] = $identity;
            }

            if (in_array('kode_gudang', $allowed, true)) {
                $data['kode_gudang'] = $identity;
            }
        } elseif (array_key_exists('kode_gudang', $data) || array_key_exists('id_gudang', $data)) {
            $identity = $this->normalizeWarehouseIdentity($data['kode_gudang'] ?? $data['id_gudang'] ?? null);

            if (in_array('id_gudang', $allowed, true)) {
                $data['id_gudang'] = $identity;
            }

            if (in_array('kode_gudang', $allowed, true)) {
                $data['kode_gudang'] = $identity;
            }
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

    private function normalizeWarehouseIdentity(mixed $value): string
    {
        $identity = is_string($value) ? trim($value) : (string) ($value ?? '');

        return $identity !== '' ? $identity : (string) Str::ulid();
    }

    private function normalizeValue(mixed $value, string $type): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            return null;
        }

        if (preg_match('/tinyint\(1\)|boolean|bool/i', $type) === 1) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (int) $value;
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

    /**
     * @return array<int, array<int, string|null>>
     */
    private function csvRows(string $path): array
    {
        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\n|\r/', $contents) ?: [])
            ->filter(fn (string $line): bool => trim($line) !== '')
            ->map(fn (string $line): array => str_getcsv($line))
            ->values()
            ->all();
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
            ['id', 'id_gudang', 'kode_gudang', 'nama_gudang'],
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
        foreach (['id_gudang', 'kode_gudang', 'id', 'nama_gudang'] as $candidate) {
            $value = $row[$candidate] ?? null;

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return 'row-'.$index;
    }
}
