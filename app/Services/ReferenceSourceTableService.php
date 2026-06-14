<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReferenceSourceTableService
{
    /**
     * @var array<string, array{label: string, description: string, kind: string, alias: string, code_column: string, ulid_id: bool, preferred: array<int, string>}>
     */
    private const TABLES = [
        'm_prasarana' => [
            'label' => 'Data Prasarana',
            'description' => 'Referensi jenis prasarana dari database/data/m_prasarana.csv.',
            'kind' => 'lookup',
            'alias' => 'prasarana',
            'code_column' => 'kode_prasarana',
            'ulid_id' => true,
            'preferred' => ['id', 'kode_prasarana', 'nama_prasarana', 'active'],
        ],
        'm_lintas' => [
            'label' => 'Data Lintasan',
            'description' => 'Referensi lintasan dari database/data/m_lintas.csv.',
            'kind' => 'lookup',
            'alias' => 'lintas',
            'code_column' => 'kode_lintas',
            'ulid_id' => true,
            'preferred' => ['id', 'kode_lintas', 'nama_lintas', 'stasiun_awal_id', 'stasiun_akhir_id', 'panjang_km', 'id_wilayah_kerja', 'active'],
        ],
        'm_stasiun' => [
            'label' => 'Data Stasiun',
            'description' => 'Referensi stasiun dan koordinat dari database/data/m_stasiun.csv.',
            'kind' => 'lookup',
            'alias' => 'stasiun',
            'code_column' => 'id',
            'ulid_id' => true,
            'preferred' => ['id', 'nama_stasiun', 'wilayah_operasi', 'kecamatan', 'zona', 'lat', 'long'],
        ],
        'm_wilker' => [
            'label' => 'Data Wilker',
            'description' => 'Referensi wilayah kerja dari database/data/m_wilker.csv.',
            'kind' => 'lookup',
            'alias' => 'wilker',
            'code_column' => 'kode_prasarana',
            'ulid_id' => true,
            'preferred' => ['id', 'kode_prasarana', 'nama_prasarana', 'active'],
        ],
        'kabupaten_kota' => [
            'label' => 'Data Kabupaten/Kota',
            'description' => 'Referensi kabupaten dan kota dari database/data/kabupaten_kota.csv.',
            'kind' => 'lookup',
            'alias' => 'kabupaten-kota',
            'code_column' => 'id',
            'ulid_id' => false,
            'preferred' => ['id', 'name'],
        ],
        'kelurahan' => [
            'label' => 'Data Kelurahan',
            'description' => 'Referensi kelurahan dari database/data/kelurahan.csv.',
            'kind' => 'lookup',
            'alias' => 'kelurahan',
            'code_column' => 'id',
            'ulid_id' => false,
            'preferred' => ['id', 'name'],
        ],
        'kecamatan' => [
            'label' => 'Data Kecamatan',
            'description' => 'Referensi kecamatan dari database/data/kecamatan.csv.',
            'kind' => 'lookup',
            'alias' => 'kecamatan',
            'code_column' => 'id',
            'ulid_id' => false,
            'preferred' => ['id', 'name'],
        ],
        'provinsi' => [
            'label' => 'Data Provinsi',
            'description' => 'Referensi provinsi dari database/data/provinsi.csv.',
            'kind' => 'lookup',
            'alias' => 'provinsi',
            'code_column' => 'id',
            'ulid_id' => false,
            'preferred' => ['id', 'name'],
        ],
    ];

    /**
     * @var array<string, array{type: string, table: string, entity: string, label?: string, description?: string, id_column?: string, source_column?: string, name_column?: string}>
     */
    private const ENTITIES = [
        'prasarana' => ['type' => 'table', 'table' => 'm_prasarana', 'entity' => 'prasarana'],
        'lintas' => ['type' => 'table', 'table' => 'm_lintas', 'entity' => 'lintas'],
        'routes' => ['type' => 'table', 'table' => 'm_lintas', 'entity' => 'lintas'],
        'stasiun' => ['type' => 'table', 'table' => 'm_stasiun', 'entity' => 'stasiun'],
        'stations' => ['type' => 'table', 'table' => 'm_stasiun', 'entity' => 'stasiun'],
        'wilker' => ['type' => 'table', 'table' => 'm_wilker', 'entity' => 'wilker'],
        'work-areas' => ['type' => 'table', 'table' => 'm_wilker', 'entity' => 'wilker'],
        'wilops' => [
            'type' => 'distinct_column',
            'table' => 'm_stasiun',
            'entity' => 'wilops',
            'label' => 'Data Wilayah Operasi',
            'description' => 'Referensi wilayah operasi yang diturunkan dari kolom wilayah_operasi pada m_stasiun.',
            'id_column' => 'id',
            'source_column' => 'wilayah_operasi',
            'name_column' => 'nama_wilops',
        ],
        'wilayah-operasi' => [
            'type' => 'distinct_column',
            'table' => 'm_stasiun',
            'entity' => 'wilops',
            'label' => 'Data Wilayah Operasi',
            'description' => 'Referensi wilayah operasi yang diturunkan dari kolom wilayah_operasi pada m_stasiun.',
            'id_column' => 'id',
            'source_column' => 'wilayah_operasi',
            'name_column' => 'nama_wilops',
        ],
        'operation-areas' => [
            'type' => 'distinct_column',
            'table' => 'm_stasiun',
            'entity' => 'wilops',
            'label' => 'Data Wilayah Operasi',
            'description' => 'Referensi wilayah operasi yang diturunkan dari kolom wilayah_operasi pada m_stasiun.',
            'id_column' => 'id',
            'source_column' => 'wilayah_operasi',
            'name_column' => 'nama_wilops',
        ],
        'provinsi' => ['type' => 'table', 'table' => 'provinsi', 'entity' => 'provinsi'],
        'province' => ['type' => 'table', 'table' => 'provinsi', 'entity' => 'provinsi'],
        'provinces' => ['type' => 'table', 'table' => 'provinsi', 'entity' => 'provinsi'],
        'kabupaten-kota' => ['type' => 'table', 'table' => 'kabupaten_kota', 'entity' => 'kabupaten-kota'],
        'kabupatenkota' => ['type' => 'table', 'table' => 'kabupaten_kota', 'entity' => 'kabupaten-kota'],
        'kabkot' => ['type' => 'table', 'table' => 'kabupaten_kota', 'entity' => 'kabupaten-kota'],
        'cities' => ['type' => 'table', 'table' => 'kabupaten_kota', 'entity' => 'kabupaten-kota'],
        'kecamatan' => ['type' => 'table', 'table' => 'kecamatan', 'entity' => 'kecamatan'],
        'kelurahan' => ['type' => 'table', 'table' => 'kelurahan', 'entity' => 'kelurahan'],
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
                'entity' => self::TABLES[$table]['alias'],
                'represented_table' => $table,
                'alias' => self::TABLES[$table]['alias'],
                'code_column' => self::TABLES[$table]['code_column'],
                'href' => route('dashboard.reference-source.tables.show', ['table' => $table]),
                'row_count' => $this->countRows($table),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function entityCatalog(): array
    {
        return collect(self::ENTITIES)
            ->unique(fn (array $config): string => (string) $config['entity'])
            ->map(function (array $config): array {
                if ($config['type'] === 'distinct_column') {
                    return [
                        'entity' => $config['entity'],
                        'aliases' => $this->aliasesForEntity((string) $config['entity']),
                        'label' => $config['label'],
                        'description' => $config['description'],
                        'kind' => 'lookup',
                        'table' => $config['table'],
                        'represented_table' => $config['table'],
                        'source_column' => $config['source_column'],
                        'id_column' => 'id',
                        'code_column' => 'id',
                        'id_strategy' => 'source_value',
                        'row_count' => $this->distinctEntityValues($config)->count(),
                        'endpoints' => $this->lookupEndpoints((string) $config['entity']),
                    ];
                }

                $table = (string) $config['table'];

                return [
                    ...$this->tableMeta($table),
                    'entity' => $config['entity'],
                    'aliases' => $this->aliasesForEntity((string) $config['entity']),
                    'table' => $table,
                    'represented_table' => $table,
                    'id_column' => 'id',
                    'code_column' => self::TABLES[$table]['code_column'],
                    'id_strategy' => self::TABLES[$table]['ulid_id'] ? 'ulid' : 'source_code',
                    'row_count' => $this->countRows($table),
                    'endpoints' => $this->lookupEndpoints((string) $config['entity']),
                ];
            })
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
            'columns' => $this->visibleColumns($table, $schema['columns']),
            'schema_columns' => $schema['columns'],
            'form_columns' => $this->formColumns($schema['columns']),
            'lookup_options' => $this->lookupOptions($schema['columns']),
            'database_name' => config('database.connections.reference.database', 'prasarana_referensi'),
            'list_endpoint' => route('dashboard.reference-source.tables.rows', ['table' => $table]),
            'store_endpoint' => route('dashboard.reference-source.tables.rows.store', ['table' => $table]),
            'update_endpoint' => route('dashboard.reference-source.tables.rows.update', ['table' => $table, 'rowKey' => '__row__']),
            'delete_endpoint' => route('dashboard.reference-source.tables.rows.destroy', ['table' => $table, 'rowKey' => '__row__']),
            'template_endpoint' => route('dashboard.reference-source.tables.template', ['table' => $table]),
            'import_endpoint' => route('dashboard.reference-source.tables.import', ['table' => $table]),
            'export_endpoint' => route('dashboard.reference-source.tables.export', ['table' => $table]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mainPage(): array
    {
        return [
            'records_count' => collect($this->catalog())->sum('row_count'),
            'tables_count' => count($this->managedTables()),
            'tables' => $this->catalog(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(string $table): array
    {
        $schema = $this->schemaWithoutMetadata($table);

        return [
            ...$schema,
            'metadata' => $this->metadata($table),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function schemaWithoutMetadata(string $table): array
    {
        $this->assertManagedTable($table);

        if (! Schema::connection($this->connectionName())->hasTable($table)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel '.$table.' belum tersedia di database referensi.'],
            ]);
        }

        $connection = DB::connection($this->connectionName());
        [$columns, $indexes] = $connection->getDriverName() === 'sqlite'
            ? $this->sqliteSchema($connection, $table)
            : $this->mysqlSchema($connection, $table);

        return [
            ...$this->tableMeta($table),
            'table' => $table,
            'entity' => self::TABLES[$table]['alias'],
            'represented_table' => $table,
            'alias' => self::TABLES[$table]['alias'],
            'code_column' => self::TABLES[$table]['code_column'],
            'row_count' => $this->countRows($table),
            'primary_key' => collect($indexes)->firstWhere('name', 'PRIMARY')['columns'] ?? [],
            'required_columns' => $this->requiredColumns($columns),
            'columns' => $columns,
            'indexes' => $indexes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(string $table): array
    {
        $this->assertManagedTable($table);
        $schema = $this->schemaWithoutMetadata($table);

        return [
            ...$this->tableMeta($table),
            'table' => $table,
            'entity' => self::TABLES[$table]['alias'],
            'represented_table' => $table,
            'alias' => self::TABLES[$table]['alias'],
            'code_column' => self::TABLES[$table]['code_column'],
            'id_column' => 'id',
            'id_strategy' => self::TABLES[$table]['ulid_id'] ? 'ulid' : 'source_code',
            'database_name' => config('database.connections.reference.database', 'prasarana_referensi'),
            'row_count' => $this->countRows($table),
            'columns' => $schema['columns'],
            'fields' => $this->fieldFormats($table, $schema['columns']),
            'visible_columns' => $this->visibleColumns($table, $schema['columns']),
            'required_columns' => $schema['required_columns'],
            'endpoints' => $this->lookupEndpoints(self::TABLES[$table]['alias']),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function aliases(): array
    {
        return collect(self::TABLES)->pluck('alias')->values()->all();
    }

    /**
     * @return array<int, string>
     */
    public function entityAliases(): array
    {
        return array_keys(self::ENTITIES);
    }

    /**
     * @return array<int, string>
     */
    private function aliasesForEntity(string $entity): array
    {
        return collect(self::ENTITIES)
            ->filter(fn (array $config): bool => $config['entity'] === $entity)
            ->keys()
            ->values()
            ->all();
    }

    public function tableForAlias(string $alias): string
    {
        $entity = $this->entityConfig($alias);
        $table = $entity['table'] ?? null;

        if (! is_string($table)) {
            throw ValidationException::withMessages([
                'lookup' => ['Lookup referensi tidak dikenal.'],
            ]);
        }

        return $table;
    }

    public function entityIsVirtual(string $entity): bool
    {
        return $this->entityConfig($entity)['type'] !== 'table';
    }

    /**
     * @return array<string, mixed>
     */
    public function entityMetadata(string $entity): array
    {
        $config = $this->entityConfig($entity);

        if ($config['type'] === 'distinct_column') {
            return $this->distinctEntityMetadata($config);
        }

        return [
            ...$this->metadata((string) $config['table']),
            'entity' => $config['entity'],
            'represented_table' => $config['table'],
            'endpoints' => $this->lookupEndpoints((string) $config['entity']),
        ];
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function entityBatch(string $entity, array $filters = []): array
    {
        $config = $this->entityConfig($entity);

        if ($config['type'] === 'distinct_column') {
            return $this->distinctEntityBatch($config, $filters);
        }

        $result = $this->batch((string) $config['table'], $filters);
        $result['meta']['reference']['entity'] = $config['entity'];
        $result['meta']['reference']['represented_table'] = $config['table'];

        return $result;
    }

    public function entitySearch(string $entity, array $filters = []): LengthAwarePaginator
    {
        $config = $this->entityConfig($entity);

        if ($config['type'] === 'distinct_column') {
            return $this->distinctEntitySearch($config, $filters);
        }

        $search = $filters['q'] ?? $filters['keyword'] ?? $filters['search'] ?? '';

        return $this->paginate((string) $config['table'], [
            ...$filters,
            'search' => $search,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function entityFind(string $entity, string $id): array
    {
        $config = $this->entityConfig($entity);

        if ($config['type'] === 'distinct_column') {
            return $this->distinctEntityFind($config, $id);
        }

        return $this->find((string) $config['table'], $id);
    }

    /**
     * @return array<string, string>
     */
    private function lookupEndpoints(string $alias): array
    {
        return [
            'metadata' => url('/api/v1/references/'.$alias.'/metadata'),
            'batch' => url('/api/v1/references/'.$alias.'/batch'),
            'search' => url('/api/v1/references/'.$alias.'/search?q={keyword}'),
            'by_id' => url('/api/v1/references/'.$alias.'/{id}'),
            'by_code' => url('/api/v1/references/'.$alias.'/kode/{kode}'),
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
            $query->where(function (Builder $builder) use ($columns, $search): void {
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
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function batch(string $table, array $filters = []): array
    {
        $this->assertManagedTable($table);

        $columns = $this->columnNames($table);
        $query = DB::connection($this->connectionName())->table($table);

        if (in_array('deleted_at', $columns, true)) {
            $query->whereNull('deleted_at');
        }

        if (array_key_exists('active', $filters) && in_array('active', $columns, true)) {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (filled($filters['updated_since'] ?? null) && in_array('updated_at', $columns, true)) {
            $query->where('updated_at', '>=', $filters['updated_since']);
        }

        $this->applyDefaultOrdering($query, $columns);

        $rows = $query->get()
            ->map(fn (object $row, int $index): array => $this->wrapRow($table, (array) $row, $index))
            ->values()
            ->all();

        return [
            'data' => $rows,
            'meta' => [
                'reference' => [
                    'table' => $table,
                    'alias' => self::TABLES[$table]['alias'],
                    'code_column' => self::TABLES[$table]['code_column'],
                ],
                'total' => count($rows),
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function findByCode(string $table, string $code): array
    {
        $this->assertManagedTable($table);
        $codeColumn = self::TABLES[$table]['code_column'];
        $columns = $this->columnNames($table);

        if (! in_array($codeColumn, $columns, true)) {
            throw ValidationException::withMessages([
                'kode' => ['Kolom kode tidak tersedia pada tabel '.$table.'.'],
            ]);
        }

        $query = DB::connection($this->connectionName())->table($table)->where($codeColumn, $code);

        if (in_array('deleted_at', $columns, true)) {
            $query->whereNull('deleted_at');
        }

        $row = $query->first();

        if (! $row instanceof \stdClass) {
            throw ValidationException::withMessages([
                'kode' => ['Data referensi dengan kode '.$code.' tidak ditemukan pada tabel '.$table.'.'],
            ]);
        }

        return $this->wrapRow($table, (array) $row, 0);
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
            $id = DB::connection($this->connectionName())->table($table)->insertGetId($data, 'internal_id');
        } catch (QueryException $exception) {
            throw ValidationException::withMessages([
                'data' => [$exception->getPrevious()?->getMessage() ?: $exception->getMessage()],
            ]);
        }

        $row = $this->findByExactColumn($table, 'internal_id', $id);

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
        $this->find($table, $rowKey);
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

        return $this->find($table, $rowKey);
    }

    public function delete(string $table, string $rowKey): void
    {
        $this->assertManagedTable($table);
        $this->find($table, $rowKey);

        $columns = $this->columnNames($table);

        if (in_array('deleted_at', $columns, true)) {
            $payload = ['deleted_at' => now()];

            if (in_array('updated_at', $columns, true)) {
                $payload['updated_at'] = now();
            }

            if (in_array('active', $columns, true)) {
                $payload['active'] = 0;
            }

            $deleted = $this->rowLocatorQuery($table, $rowKey)->update($payload);
        } else {
            $deleted = $this->rowLocatorQuery($table, $rowKey)->delete();
        }

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

        $query->chunk(1000, function ($rows) use ($handle, $columns): void {
            foreach ($rows as $row) {
                $data = (array) $row;
                fputcsv($handle, array_map(
                    fn (string $column): mixed => $this->csvValue($data[$column] ?? null),
                    $columns,
                ));
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function entityConfig(string $entity): array
    {
        $normalized = trim(strtolower($entity));
        $config = self::ENTITIES[$normalized] ?? null;

        if ($config === null) {
            throw ValidationException::withMessages([
                'entity' => ['Entitas referensi tidak dikenal.'],
            ]);
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function distinctEntityMetadata(array $config): array
    {
        $fields = [
            [
                'name' => 'id',
                'label' => 'ID',
                'type' => 'string',
                'format' => 'source_value',
                'nullable' => false,
                'required' => true,
                'example' => 'DAOP 1 JAKARTA',
                'description' => 'Identifier entitas. Untuk wilops nilainya sama dengan nama wilayah operasi dan harus di-URL-encode saat dipakai pada endpoint by_id.',
            ],
            [
                'name' => $config['name_column'],
                'label' => 'Nama Wilops',
                'type' => 'string',
                'format' => 'text',
                'nullable' => false,
                'required' => true,
                'example' => 'DAOP 1 JAKARTA',
                'description' => 'Nama wilayah operasi hasil distinct dari m_stasiun.wilayah_operasi.',
            ],
        ];

        return [
            'label' => $config['label'],
            'description' => $config['description'],
            'kind' => 'lookup',
            'table' => $config['table'],
            'entity' => $config['entity'],
            'represented_table' => $config['table'],
            'source_column' => $config['source_column'],
            'code_column' => 'id',
            'id_column' => 'id',
            'id_strategy' => 'source_value',
            'database_name' => config('database.connections.reference.database', 'prasarana_referensi'),
            'row_count' => $this->distinctEntityValues($config)->count(),
            'columns' => $fields,
            'fields' => $fields,
            'visible_columns' => ['id', $config['name_column']],
            'required_columns' => ['id', $config['name_column']],
            'endpoints' => $this->lookupEndpoints((string) $config['entity']),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    private function distinctEntityBatch(array $config, array $filters = []): array
    {
        $values = $this->distinctEntityValues($config, (string) ($filters['q'] ?? $filters['keyword'] ?? $filters['search'] ?? ''));
        $rows = $values
            ->values()
            ->map(fn (string $value, int $index): array => $this->wrapDistinctEntityRow($config, $value, $index))
            ->all();

        return [
            'data' => $rows,
            'meta' => [
                'reference' => [
                    'entity' => $config['entity'],
                    'table' => $config['table'],
                    'represented_table' => $config['table'],
                    'source_column' => $config['source_column'],
                    'code_column' => 'id',
                ],
                'total' => count($rows),
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $filters
     */
    private function distinctEntitySearch(array $config, array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1), config('master-data.pagination.max_per_page'));
        $page = max((int) ($filters['page'] ?? 1), 1);
        $search = (string) ($filters['q'] ?? $filters['keyword'] ?? $filters['search'] ?? '');
        $values = $this->distinctEntityValues($config, $search)->values();
        $total = $values->count();
        $rows = $values
            ->slice(($page - 1) * $perPage, $perPage)
            ->values()
            ->map(fn (string $value, int $index): array => $this->wrapDistinctEntityRow($config, $value, $index))
            ->all();

        return new Paginator($rows, $total, $perPage, $page, [
            'path' => request()->url(),
            'pageName' => 'page',
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function distinctEntityFind(array $config, string $id): array
    {
        $decodedId = urldecode($id);
        $match = $this->distinctEntityValues($config)
            ->first(fn (string $value): bool => $value === $decodedId);

        if ($match === null) {
            throw ValidationException::withMessages([
                'id' => ['Data referensi dengan id '.$id.' tidak ditemukan pada entitas '.$config['entity'].'.'],
            ]);
        }

        return $this->wrapDistinctEntityRow($config, $match, 0);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function distinctEntityValues(array $config, string $search = ''): \Illuminate\Support\Collection
    {
        $table = (string) $config['table'];
        $sourceColumn = (string) $config['source_column'];

        if (! Schema::connection($this->connectionName())->hasTable($table)
            || ! in_array($sourceColumn, $this->columnNames($table), true)) {
            return collect();
        }

        $query = DB::connection($this->connectionName())
            ->table($table)
            ->select($sourceColumn)
            ->whereNotNull($sourceColumn)
            ->where($sourceColumn, '<>', '');

        if (in_array('deleted_at', $this->columnNames($table), true)) {
            $query->whereNull('deleted_at');
        }

        if ($search !== '') {
            $query->where($sourceColumn, 'like', '%'.$search.'%');
        }

        return $query
            ->distinct()
            ->orderBy($sourceColumn)
            ->pluck($sourceColumn)
            ->map(fn (mixed $value): string => (string) $value)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function wrapDistinctEntityRow(array $config, string $value, int $index): array
    {
        return [
            'table' => $config['table'],
            'entity' => $config['entity'],
            'row_key' => $value !== '' ? $value : 'row-'.$index,
            'data' => [
                'id' => $value,
                $config['name_column'] => $value,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, array<string, mixed>>
     */
    private function fieldFormats(string $table, array $columns): array
    {
        $required = array_fill_keys($this->requiredColumns($columns), true);

        return collect($columns)
            ->filter(fn (array $column): bool => (string) ($column['name'] ?? '') !== '')
            ->map(fn (array $column): array => $this->fieldFormat($table, $column, isset($required[(string) ($column['name'] ?? '')])))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $column
     * @return array<string, mixed>
     */
    private function fieldFormat(string $table, array $column, bool $required): array
    {
        $name = (string) ($column['name'] ?? '');
        $type = strtolower((string) ($column['type'] ?? 'string'));
        [$apiType, $format] = $this->apiTypeAndFormat($table, $name, $type);

        return [
            'name' => $name,
            'label' => Str::headline($name),
            'type' => $apiType,
            'format' => $format,
            'database_type' => $column['type'] ?? null,
            'nullable' => (bool) ($column['nullable'] ?? true),
            'required' => $required,
            'example' => $this->fieldExample($table, $name, $format),
            'description' => $this->fieldDescription($table, $name, $format),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function apiTypeAndFormat(string $table, string $name, string $databaseType): array
    {
        if ($name === 'id' && self::TABLES[$table]['ulid_id']) {
            return ['string', 'ulid'];
        }

        if (in_array($name, ['lat', 'latitude'], true)) {
            return ['number', 'latitude'];
        }

        if (in_array($name, ['long', 'longitude', 'lon'], true)) {
            return ['number', 'longitude'];
        }

        if ($name === 'active' || preg_match('/tinyint\(1\)|boolean|bool/i', $databaseType) === 1) {
            return ['boolean', 'boolean'];
        }

        if (str_ends_with($name, '_at') || preg_match('/timestamp|datetime/i', $databaseType) === 1) {
            return ['string', 'date-time'];
        }

        if (preg_match('/date/i', $databaseType) === 1) {
            return ['string', 'date'];
        }

        if (preg_match('/int/i', $databaseType) === 1) {
            return ['integer', 'integer'];
        }

        if (preg_match('/decimal|double|float/i', $databaseType) === 1) {
            return ['number', 'decimal'];
        }

        if (str_starts_with($name, 'kode_') || in_array($name, ['kode', 'code'], true)) {
            return ['string', 'code'];
        }

        if ($name === 'id') {
            return ['string', 'source_code'];
        }

        return ['string', 'text'];
    }

    private function fieldExample(string $table, string $name, string $format): mixed
    {
        return match ($format) {
            'ulid' => '01JREFEREN98N7269AAH1FXG19',
            'latitude' => -6.188,
            'longitude' => 106.815,
            'boolean' => true,
            'date-time' => '2026-06-14T12:00:00+00:00',
            'date' => '2026-06-14',
            'integer' => 1,
            'decimal' => $name === 'panjang_km' ? 12.345 : 1.25,
            'code' => str_contains($name, 'lintas') ? 'LINTAS-01' : 'PRAS-01',
            'source_code' => in_array($table, ['provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan'], true) ? '11' : 'KODE-01',
            default => str_starts_with($name, 'nama') || $name === 'name' ? 'Contoh Referensi' : 'Contoh',
        };
    }

    private function fieldDescription(string $table, string $name, string $format): string
    {
        if ($name === 'internal_id') {
            return 'ID internal database. Tidak digunakan sebagai ID publik API.';
        }

        if ($format === 'ulid') {
            return 'ID publik format ULID 26 karakter.';
        }

        if ($format === 'source_code') {
            return 'Kode sumber dari CSV yang dipakai sebagai ID publik.';
        }

        if ($format === 'latitude') {
            return 'Koordinat latitude dalam derajat desimal, rentang -90 sampai 90.';
        }

        if ($format === 'longitude') {
            return 'Koordinat longitude dalam derajat desimal, rentang -180 sampai 180.';
        }

        if ($format === 'code') {
            return 'Kode referensi yang dapat dipakai untuk lookup berdasarkan kode.';
        }

        return 'Field '.$name.' pada tabel '.$table.'.';
    }

    private function connectionName(): string
    {
        return 'reference';
    }

    /**
     * @return array<int, string>
     */
    public function managedTables(): array
    {
        return array_keys(self::TABLES);
    }

    private function assertManagedTable(string $table): void
    {
        if (! in_array($table, $this->managedTables(), true)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel tidak terdaftar untuk modul Referensi.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function tableMeta(string $table): array
    {
        $this->assertManagedTable($table);

        return [
            'label' => self::TABLES[$table]['label'],
            'description' => self::TABLES[$table]['description'],
            'kind' => self::TABLES[$table]['kind'],
        ];
    }

    private function tableKind(string $table): string
    {
        return self::TABLES[$table]['kind'];
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<string, array<int, array{value: string, label: string}>>
     */
    private function lookupOptions(array $columns): array
    {
        $columnNames = collect($columns)->pluck('name')->all();

        return in_array('active', $columnNames, true) ? [
            'active' => [
                ['value' => '1', 'label' => 'AKTIF'],
                ['value' => '0', 'label' => 'NONAKTIF'],
            ],
        ] : [];
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
        try {
            if (! Schema::connection($this->connectionName())->hasTable($table)) {
                return 0;
            }

            $query = DB::connection($this->connectionName())->table($table);

            if (in_array('deleted_at', $this->columnNames($table), true)) {
                $query->whereNull('deleted_at');
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, string>
     */
    private function visibleColumns(string $table, array $columns): array
    {
        $available = collect($columns)->pluck('name')->filter()->all();
        $preferred = collect(self::TABLES[$table]['preferred'])
            ->filter(fn (string $column): bool => in_array($column, $available, true))
            ->values();

        if ($preferred->isNotEmpty()) {
            return $preferred->all();
        }

        return collect($columns)
            ->pluck('name')
            ->filter(fn (?string $name): bool => is_string($name) && ! in_array($name, ['internal_id', 'created_at', 'updated_at', 'deleted_at'], true))
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
                    && ! in_array($name, ['internal_id', 'created_at', 'updated_at', 'deleted_at'], true)
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

                if (in_array($name, ['internal_id', 'created_at', 'updated_at', 'deleted_at'], true)) {
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

        if ($creating && self::TABLES[$table]['ulid_id'] && blank($data['id'] ?? null)) {
            $data['id'] = (string) Str::ulid();
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

        if (preg_match('/tinyint\(1\)|boolean|bool/i', $type) === 1) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (int) $value;
        }

        if (preg_match('/decimal|double|float/i', $type) === 1 && is_string($value)) {
            $normalized = str_replace(',', '.', $value);

            return is_numeric($normalized) ? $normalized : null;
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

    private function rowLocatorQuery(string $table, string $rowKey): Builder
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

        return $query->where('internal_id', $rowKey);
    }

    /**
     * @return array<int, string>
     */
    private function keyColumns(string $table): array
    {
        $columns = $this->columnNames($table);

        return array_values(array_filter(
            ['id', 'kode_prasarana', 'kode_lintas', 'internal_id'],
            fn (string $column): bool => in_array($column, $columns, true),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function keyColumnsForRowKey(string $table, string $rowKey): array
    {
        if (ctype_digit($rowKey)) {
            return $this->keyColumns($table);
        }

        return array_values(array_filter(
            $this->keyColumns($table),
            fn (string $column): bool => $column !== 'internal_id',
        ));
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function applyDefaultOrdering(Builder $query, array $columns): void
    {
        if (in_array('updated_at', $columns, true)) {
            $query->orderByDesc('updated_at');
        } elseif (in_array('name', $columns, true)) {
            $query->orderBy('name');
        } elseif (in_array('nama_stasiun', $columns, true)) {
            $query->orderBy('nama_stasiun');
        } elseif (in_array('internal_id', $columns, true)) {
            $query->orderByDesc('internal_id');
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
        foreach (['id', 'kode_prasarana', 'kode_lintas', 'internal_id'] as $candidate) {
            $value = $row[$candidate] ?? null;

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return 'row-'.$index;
    }
}
