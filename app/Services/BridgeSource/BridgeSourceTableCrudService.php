<?php

namespace App\Services\BridgeSource;

use App\Models\ApiClient;
use App\Models\User;
use App\Support\BridgeSource\BridgeSourceSql;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BridgeSourceTableCrudService
{
    /**
     * @var array<int, string>
     */
    private const API_MANAGED_TABLES = [
        'm_kabkot',
        'm_lintas',
        'm_provinsi',
        'm_stasiun',
        'm_stasiun_280922_ori',
        'm_stasiun_bu300922',
        'm_stasiun_old',
        'm_stasiun_old2',
        'm_wilayah_kerja',
        'm_wilayah_operasi',
    ];

    public function __construct(
        private readonly BridgeSourceSql $bridgeSourceSql,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function catalog(): array
    {
        return array_map(function (string $table): array {
            $meta = $this->bridgeSourceSql->tableMeta($table);

            return [
                'table' => $table,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'row_count' => $this->countRows($table),
                'endpoints' => [
                    'schema' => route('api.v1.bridge-source.tables.schema', ['table' => $table]),
                    'records' => route('api.v1.bridge-source.tables.records.index', ['table' => $table]),
                    'store' => route('api.v1.bridge-source.tables.records.store', ['table' => $table]),
                ],
            ];
        }, $this->managedTables());
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(string $table): array
    {
        $this->assertManagedTable($table);

        $meta = $this->bridgeSourceSql->tableMeta($table);
        $connection = DB::connection($this->connectionName());
        [$columns, $indexes] = $connection->getDriverName() === 'sqlite'
            ? $this->sqliteSchema($connection, $table)
            : $this->mysqlSchema($connection, $table);

        return [
            'table' => $table,
            'label' => $meta['label'],
            'description' => $meta['description'],
            'row_count' => $this->countRows($table),
            'primary_key' => collect($indexes)->firstWhere('name', 'PRIMARY')['columns'] ?? [],
            'unique_keys' => collect($indexes)->filter(fn (array $index): bool => $index['unique'] && $index['name'] !== 'PRIMARY')->values()->all(),
            'required_columns' => $this->requiredColumns($columns),
            'columns' => $columns,
            'indexes' => $indexes,
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

    public function paginate(string $table, array $filters = []): LengthAwarePaginator
    {
        $this->assertManagedTable($table);

        $perPage = min(max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1), config('master-data.pagination.max_per_page'));
        $page = max((int) ($filters['page'] ?? 1), 1);
        $search = trim((string) ($filters['search'] ?? ''));
        $columns = $this->columnNames($table);
        $query = DB::connection($this->connectionName())->table($table);

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
     * @return array<string, mixed>
     */
    public function find(string $table, string $rowKey): array
    {
        $this->assertManagedTable($table);
        $row = $this->findRowByKey($table, $rowKey);

        if ($row === null) {
            throw ValidationException::withMessages([
                'row_key' => ['Record source tidak ditemukan pada tabel '.$table.'.'],
            ]);
        }

        return $this->wrapRow($table, $row, 0);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(string $table, array $payload, mixed $actor = null): array
    {
        $this->assertManagedTable($table);
        $schema = $this->schema($table);
        $columns = $schema['columns'];
        $data = $this->normalizePayload($table, $payload, $columns, true, $actor);
        $this->validateRequiredColumns($data, $schema['required_columns']);

        $connection = DB::connection($this->connectionName());
        $id = $this->insertAndResolveId($connection, $table, $data);
        $row = $id !== null ? $this->findByExactColumn($table, 'id', $id) : $this->findFromPayload($table, $data);

        if ($row === null) {
            throw ValidationException::withMessages([
                'data' => ['Record source berhasil dibuat, tetapi tidak dapat dibaca ulang otomatis.'],
            ]);
        }

        return $this->wrapRow($table, $row, 0);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $table, string $rowKey, array $payload, mixed $actor = null): array
    {
        $this->assertManagedTable($table);
        $current = $this->find($table, $rowKey);
        $schema = $this->schema($table);
        $columns = $schema['columns'];
        $data = $this->normalizePayload($table, $payload, $columns, false, $actor);

        if ($data === []) {
            throw ValidationException::withMessages([
                'data' => ['Tidak ada kolom yang dapat diperbarui pada payload ini.'],
            ]);
        }

        $this->rowLocatorQuery($table, $rowKey)->update($data);

        return $this->find($table, $current['row_key']);
    }

    public function delete(string $table, string $rowKey): void
    {
        $this->assertManagedTable($table);
        $deleted = $this->rowLocatorQuery($table, $rowKey)->delete();

        if ($deleted === 0) {
            throw ValidationException::withMessages([
                'row_key' => ['Record source tidak ditemukan pada tabel '.$table.'.'],
            ]);
        }
    }

    private function connectionName(): string
    {
        return $this->bridgeSourceSql->connectionName();
    }

    /**
     * @return array<int, string>
     */
    private function managedTables(): array
    {
        return array_values(array_filter(
            self::API_MANAGED_TABLES,
            fn (string $table): bool => in_array($table, $this->bridgeSourceSql->sourceTables(), true),
        ));
    }

    private function assertManagedTable(string $table): void
    {
        if (! in_array($table, $this->managedTables(), true)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel source tidak terdaftar untuk API CRUD bridge source.'],
            ]);
        }
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
        return (int) DB::connection($this->connectionName())->table($table)->count();
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, string>
     */
    private function requiredColumns(array $columns): array
    {
        return array_values(array_filter(array_map(function (array $column): ?string {
            $name = (string) ($column['name'] ?? '');
            $nullable = (bool) ($column['nullable'] ?? true);
            $default = $column['default'] ?? null;
            $extra = (string) ($column['extra'] ?? '');

            if ($name === '' || $nullable || $default !== null || str_contains($extra, 'auto_increment')) {
                return null;
            }

            if (in_array($name, ['created_at', 'updated_at', 'created_by', 'updated_by', 'active'], true)) {
                return null;
            }

            return $name;
        }, $columns)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(string $table, array $payload, array $columns, bool $creating, mixed $actor): array
    {
        $allowed = collect($columns)->pluck('name')->filter()->values()->all();
        $columnMap = collect($columns)->keyBy('name');
        $data = [];

        foreach (($payload['data'] ?? []) as $key => $value) {
            if (! is_string($key) || ! in_array($key, $allowed, true)) {
                continue;
            }

            $extra = (string) ($columnMap[$key]['extra'] ?? '');

            if ($creating && str_contains($extra, 'auto_increment')) {
                continue;
            }

            $data[$key] = is_string($value) ? trim($value) : $value;
        }

        $actorName = match (true) {
            $actor instanceof User => Str::limit(trim($actor->name !== '' ? $actor->name : $actor->email), 64, ''),
            $actor instanceof ApiClient => Str::limit(trim($actor->name !== '' ? $actor->name : $actor->code), 64, ''),
            default => 'api',
        };
        $timestamp = now();

        if ($creating && in_array('uniqid', $allowed, true) && blank($data['uniqid'] ?? null)) {
            $data['uniqid'] = uniqid();
        }

        if ($creating && in_array('active', $allowed, true) && ! array_key_exists('active', $data)) {
            $data['active'] = 1;
        }

        if ($creating && in_array('created_by', $allowed, true) && ! array_key_exists('created_by', $data)) {
            $data['created_by'] = $actorName;
        }

        if (in_array('updated_by', $allowed, true)) {
            $data['updated_by'] = $actorName;
        }

        if ($creating && in_array('created_at', $allowed, true) && ! array_key_exists('created_at', $data)) {
            $data['created_at'] = $timestamp;
        }

        if (in_array('updated_at', $allowed, true)) {
            $data['updated_at'] = $timestamp;
        }

        return $data;
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

    private function insertAndResolveId(Connection $connection, string $table, array $data): int|string|null
    {
        $schema = $this->schema($table);
        $primaryKey = $schema['primary_key'][0] ?? null;
        $idColumn = $primaryKey === 'id' ? 'id' : null;

        if ($idColumn !== null) {
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
        foreach (['uniqid', 'kode', 'uuid', 'code', 'id', 'nama'] as $candidate) {
            if (! array_key_exists($candidate, $data) || $data[$candidate] === null || $data[$candidate] === '') {
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
    private function findRowByKey(string $table, string $rowKey): ?array
    {
        foreach ($this->keyColumns($table) as $column) {
            $row = $this->findByExactColumn($table, $column, $rowKey);

            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function keyColumns(string $table): array
    {
        $columns = $this->columnNames($table);

        return array_values(array_filter(
            ['uniqid', 'uuid', 'kode', 'code', 'id', 'nama'],
            fn (string $column): bool => in_array($column, $columns, true),
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findByExactColumn(string $table, string $column, mixed $value): ?array
    {
        $row = DB::connection($this->connectionName())->table($table)->where($column, $value)->first();

        return $row instanceof \stdClass ? (array) $row : null;
    }

    private function rowLocatorQuery(string $table, string $rowKey): \Illuminate\Database\Query\Builder
    {
        $query = DB::connection($this->connectionName())->table($table);
        $row = $this->findRowByKey($table, $rowKey);

        if ($row !== null) {
            foreach (['uniqid', 'uuid', 'kode', 'code', 'id', 'nama'] as $column) {
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
        }

        if (in_array('created_at', $columns, true)) {
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
        foreach (['row_key', 'uniqid', 'uuid', 'kode', 'code', 'id', 'nama'] as $candidate) {
            $value = $row[$candidate] ?? null;

            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return 'row-'.($index + 1);
    }
}
