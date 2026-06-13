<?php

namespace App\Services\BridgeSource;

use App\Models\ApiClient;
use App\Models\User;
use App\Support\BridgeSource\BridgeSourceSql;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BridgeSourceCrudService
{
    private const MAIN_TABLE = 'm_jembatan';

    private const PROFILE_TABLE = 'm_jembatan_profil';

    private const SPAN_TABLE = 'm_jembatan_bentang';

    private const SUBSTRUCTURE_TABLE = 'm_jembatan_bawah';

    private const PROTECTION_TABLE = 'm_jembatan_detil_3';

    private const ASSESSMENT_TOTAL_TABLE = 'm_jembatan_nilai_total';

    public function __construct(
        private readonly BridgeSourceSql $bridgeSourceSql,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function relationMap(): array
    {
        return [
            [
                'table' => self::MAIN_TABLE,
                'type' => 'root',
                'relation' => 'Induk data jembatan',
                'key' => 'uniqid',
                'target' => null,
            ],
            [
                'table' => self::PROFILE_TABLE,
                'type' => 'one_to_one',
                'relation' => 'Profil struktur',
                'key' => 'id_jembatan',
                'target' => self::MAIN_TABLE.'.uniqid',
            ],
            [
                'table' => self::SPAN_TABLE,
                'type' => 'one_to_many',
                'relation' => 'Daftar bentang',
                'key' => 'id_jembatan',
                'target' => self::MAIN_TABLE.'.uniqid',
            ],
            [
                'table' => self::SUBSTRUCTURE_TABLE,
                'type' => 'one_to_many',
                'relation' => 'Struktur bawah',
                'key' => 'id_jembatan',
                'target' => self::MAIN_TABLE.'.uniqid',
            ],
            [
                'table' => self::PROTECTION_TABLE,
                'type' => 'one_to_one',
                'relation' => 'Detail pelindung',
                'key' => 'id_jembatan',
                'target' => self::MAIN_TABLE.'.uniqid',
            ],
            [
                'table' => self::ASSESSMENT_TOTAL_TABLE,
                'type' => 'one_to_one',
                'relation' => 'Nilai total asesmen',
                'key' => 'id_jembatan',
                'target' => self::MAIN_TABLE.'.uniqid',
            ],
            [
                'table' => 'm_wilayah_kerja',
                'type' => 'lookup',
                'relation' => 'Lookup wilayah kerja',
                'key' => 'uniqid/kode/id/nama',
                'target' => self::MAIN_TABLE.'.wil_ker',
            ],
            [
                'table' => 'm_wilayah_operasi',
                'type' => 'lookup',
                'relation' => 'Lookup wilayah operasi',
                'key' => 'uniqid/kode/id/nama',
                'target' => self::MAIN_TABLE.'.wil_op',
            ],
            [
                'table' => 'm_provinsi',
                'type' => 'lookup',
                'relation' => 'Lookup provinsi',
                'key' => 'uniqid/kode/id',
                'target' => self::MAIN_TABLE.'.id_prov',
            ],
            [
                'table' => 'm_kabkot',
                'type' => 'lookup',
                'relation' => 'Lookup kabupaten/kota',
                'key' => 'uniqid/kode/id',
                'target' => self::MAIN_TABLE.'.id_kabkot',
            ],
            [
                'table' => 'm_lintas',
                'type' => 'lookup',
                'relation' => 'Lookup lintas',
                'key' => 'uniqid/kode/id/nama',
                'target' => self::MAIN_TABLE.'.lintas',
            ],
            [
                'table' => 'm_stasiun',
                'type' => 'lookup',
                'relation' => 'Lookup stasiun awal/akhir',
                'key' => 'uniqid/kode/id/nama',
                'target' => self::MAIN_TABLE.'.stasiun1 / stasiun2',
            ],
        ];
    }

    public function count(): int
    {
        try {
            return $this->baseQuery()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function isDatabaseSourceAvailable(): bool
    {
        try {
            return DB::connection($this->connectionName())
                ->getSchemaBuilder()
                ->hasTable(self::MAIN_TABLE);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tableCatalog(): array
    {
        $tables = array_values(array_filter(
            $this->allowedTables(),
            fn (string $table): bool => $this->tableExists($table),
        ));

        return array_values(array_map(function (string $table): array {
            $meta = $this->bridgeSourceSql->tableMeta($table);

            return [
                'key' => $table,
                'table' => $table,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'row_count' => $this->tableCount($table),
                'href' => route('dashboard.bridge-source.tables.show', ['table' => $table]),
            ];
        }, $tables));
    }

    /**
     * @return array<string, mixed>
     */
    public function tablePage(string $table): array
    {
        $this->assertAllowedTable($table);

        $meta = $this->bridgeSourceSql->tableMeta($table);
        $columns = DB::connection($this->connectionName())
            ->getSchemaBuilder()
            ->getColumnListing($table);

        return [
            'table' => $table,
            'label' => $meta['label'],
            'description' => $meta['description'],
            'row_count' => $this->tableCount($table),
            'columns' => $columns === [] ? ['row_key'] : $columns,
            'list_endpoint' => route('dashboard.bridge-source.tables.rows', ['table' => $table]),
        ];
    }

    public function paginateTable(string $table, array $filters = []): LengthAwarePaginator
    {
        $this->assertAllowedTable($table);

        $perPage = min(
            max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1),
            config('master-data.pagination.max_per_page'),
        );
        $page = max((int) ($filters['page'] ?? 1), 1);
        $search = trim((string) ($filters['search'] ?? ''));
        $columns = DB::connection($this->connectionName())
            ->getSchemaBuilder()
            ->getColumnListing($table);

        $query = DB::connection($this->connectionName())->table($table);

        if ($search !== '' && $columns !== []) {
            $query->where(function ($builder) use ($columns, $search): void {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        $total = (clone $query)->count();

        if (in_array('updated_at', $columns, true)) {
            $query->orderByDesc('updated_at');
        }

        if (in_array('created_at', $columns, true)) {
            $query->orderByDesc('created_at');
        } elseif (in_array('id', $columns, true)) {
            $query->orderByDesc('id');
        }

        $items = $query
            ->forPage($page, $perPage)
            ->get()
            ->map(function (object $row, int $index) use ($table): array {
                $item = (array) $row;

                return [
                    ...$item,
                    'row_key' => $this->resolveRawTableRowKey($table, $item, $index),
                ];
            })
            ->all();

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ],
        );
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1),
            config('master-data.pagination.max_per_page'),
        );
        $page = max((int) ($filters['page'] ?? 1), 1);
        $search = trim((string) ($filters['search'] ?? ''));

        $query = $this->baseQuery();
        $this->applySearch($query, $search);

        $total = (clone $query)->count();
        $bridges = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->forPage($page, $perPage)
            ->get()
            ->all();
        $snapshots = $this->loadRelationSnapshots(array_map(
            static fn (object $row): string => (string) $row->uniqid,
            $bridges,
        ));
        $rows = array_map(function (object $row) use ($snapshots): array {
            $bridge = (array) $row;

            return $this->decorateBridge(
                $bridge,
                false,
                $snapshots[(string) ($bridge['uniqid'] ?? '')] ?? [],
            );
        }, $bridges);

        return new Paginator(
            $rows,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ],
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $uniqid): ?array
    {
        $bridge = $this->baseQuery()->where('uniqid', $uniqid)->first();

        if (! $bridge instanceof \stdClass) {
            return null;
        }

        $bridgeData = (array) $bridge;
        $snapshots = $this->loadRelationSnapshots([(string) $bridgeData['uniqid']]);

        return $this->decorateBridge(
            $bridgeData,
            true,
            $snapshots[(string) $bridgeData['uniqid']] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload, mixed $actor): array
    {
        $uniqid = $this->normalizeString($payload['uniqid'] ?? null) ?? uniqid();
        $payload['uniqid'] = $uniqid;
        $this->ensureBridgeDoesNotExist($uniqid);

        $connection = DB::connection($this->connectionName());
        $timestamp = now();
        $actorName = $this->actorName($actor);

        $connection->transaction(function () use ($payload, $uniqid, $timestamp, $actorName, $connection): void {
            $connection->table(self::MAIN_TABLE)->insert($this->mainTablePayload(
                $payload,
                $uniqid,
                $actorName,
                $timestamp,
                true,
            ));

            $this->syncProfile($connection, $uniqid, $payload['profile'] ?? null, $actorName, $timestamp, true);
            $this->replaceRows($connection, self::SPAN_TABLE, $uniqid, $payload['spans'] ?? [], $actorName, $timestamp, 'span');
            $this->replaceRows($connection, self::SUBSTRUCTURE_TABLE, $uniqid, $payload['substructures'] ?? [], $actorName, $timestamp, 'substructure');
            $this->syncProtection($connection, $uniqid, $payload['protection'] ?? null, $actorName, $timestamp, true);
            $this->syncAssessmentTotal($connection, $uniqid, $payload['assessment'] ?? null, $actorName, $timestamp, true);
        });

        return $this->findOrFail($uniqid);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $uniqid, array $payload, mixed $actor): array
    {
        $current = $this->findOrFail($uniqid);
        $payload['uniqid'] = $uniqid;

        $connection = DB::connection($this->connectionName());
        $timestamp = now();
        $actorName = $this->actorName($actor);

        $connection->transaction(function () use ($payload, $uniqid, $timestamp, $actorName, $connection, $current): void {
            $connection->table(self::MAIN_TABLE)
                ->where('uniqid', $uniqid)
                ->update($this->mainTablePayload(
                    array_merge($current, $payload),
                    $uniqid,
                    $actorName,
                    $timestamp,
                    false,
                ));

            $this->syncProfile(
                $connection,
                $uniqid,
                $payload['profile'] ?? ($current['profile'] ?? null),
                $actorName,
                $timestamp,
                false,
            );
            $this->replaceRows(
                $connection,
                self::SPAN_TABLE,
                $uniqid,
                $payload['spans'] ?? ($current['spans'] ?? []),
                $actorName,
                $timestamp,
                'span',
            );
            $this->replaceRows(
                $connection,
                self::SUBSTRUCTURE_TABLE,
                $uniqid,
                $payload['substructures'] ?? ($current['substructures'] ?? []),
                $actorName,
                $timestamp,
                'substructure',
            );
            $this->syncProtection(
                $connection,
                $uniqid,
                $payload['protection'] ?? ($current['protection'] ?? null),
                $actorName,
                $timestamp,
                false,
            );
            $this->syncAssessmentTotal(
                $connection,
                $uniqid,
                $payload['assessment'] ?? ($current['assessment'] ?? null),
                $actorName,
                $timestamp,
                false,
            );
        });

        return $this->findOrFail($uniqid);
    }

    public function delete(string $uniqid, mixed $actor): void
    {
        $this->findOrFail($uniqid);

        $timestamp = now();
        $actorName = $this->actorName($actor);
        $connection = DB::connection($this->connectionName());

        $connection->transaction(function () use ($connection, $uniqid, $timestamp, $actorName): void {
            $connection->table(self::MAIN_TABLE)
                ->where('uniqid', $uniqid)
                ->update([
                    'active' => 0,
                    'status' => 0,
                    'statusdata' => 0,
                    'updated_by' => $actorName,
                    'updated_at' => $timestamp,
                    'deleted_at' => $timestamp,
                ]);

            $connection->table(self::PROFILE_TABLE)
                ->where('id_jembatan', $uniqid)
                ->update([
                    'active' => 0,
                    'updated_by' => $actorName,
                    'updated_at' => $timestamp,
                ]);

            $connection->table(self::SPAN_TABLE)
                ->where('id_jembatan', $uniqid)
                ->update([
                    'active' => 0,
                    'updated_by' => $actorName,
                    'updated_at' => $timestamp,
                ]);
        });
    }

    /**
     * @param  array<string, mixed>  $bridge
     * @return array<string, mixed>
     */
    private function decorateBridge(array $bridge, bool $withRelations = false, array $snapshot = []): array
    {
        $lookups = $this->referenceLookups();
        $profile = $snapshot['profile'] ?? null;
        $spans = $snapshot['spans'] ?? [];
        $substructures = $snapshot['substructures'] ?? [];
        $protection = $snapshot['protection'] ?? null;
        $assessment = $snapshot['assessment'] ?? null;

        $decorated = [
            ...$bridge,
            'wil_ker_name' => $this->resolveReferenceLabel($lookups['m_wilayah_kerja'], $bridge['wil_ker'] ?? null),
            'province_name' => $this->resolveReferenceLabel($lookups['m_provinsi'], $bridge['id_prov'] ?? null),
            'city_name' => $this->resolveReferenceLabel($lookups['m_kabkot'], $bridge['id_kabkot'] ?? null),
            'wil_op_name' => $this->resolveReferenceLabel($lookups['m_wilayah_operasi'], $bridge['wil_op'] ?? null),
            'lintas_name' => $this->resolveReferenceLabel($lookups['m_lintas'], $bridge['lintas'] ?? null),
            'stasiun1_name' => $this->resolveReferenceLabel($lookups['m_stasiun'], $bridge['stasiun1'] ?? null),
            'stasiun2_name' => $this->resolveReferenceLabel($lookups['m_stasiun'], $bridge['stasiun2'] ?? null),
            'bridge_identity' => $this->compactJoin([
                $this->normalizeString($bridge['no_bh'] ?? null),
                $this->normalizeString($bridge['nama'] ?? null),
                $this->normalizeString($bridge['jenis'] ?? null),
            ]),
            'location_summary' => $this->compactJoin([
                $this->normalizeString($bridge['km_hm'] ?? null) !== null
                    ? 'KM/HM '.$this->normalizeString($bridge['km_hm'] ?? null)
                    : null,
                $this->compactJoin([
                    $this->resolveReferenceLabel($lookups['m_provinsi'], $bridge['id_prov'] ?? null),
                    $this->resolveReferenceLabel($lookups['m_kabkot'], $bridge['id_kabkot'] ?? null),
                ], ', '),
            ]),
            'route_summary' => $this->compactJoin([
                $this->compactJoin([
                    $this->resolveReferenceLabel($lookups['m_stasiun'], $bridge['stasiun1'] ?? null),
                    $this->resolveReferenceLabel($lookups['m_stasiun'], $bridge['stasiun2'] ?? null),
                ], ' -> '),
                $this->resolveReferenceLabel($lookups['m_lintas'], $bridge['lintas'] ?? null),
            ]),
            'wilayah_summary' => $this->compactJoin([
                $this->resolveReferenceLabel($lookups['m_wilayah_kerja'], $bridge['wil_ker'] ?? null),
                $this->resolveReferenceLabel($lookups['m_wilayah_operasi'], $bridge['wil_op'] ?? null),
            ]),
            'profile_summary' => $this->buildProfileSummary($profile),
            'span_summary' => $this->buildSpanSummary($spans),
            'substructure_summary' => $this->buildSubstructureSummary($substructures),
            'protection_summary' => $this->buildProtectionSummary($protection),
            'assessment_summary' => $this->buildAssessmentSummary($assessment),
            'structure_summary' => $this->compactJoin([
                $this->buildProfileSummary($profile),
                $this->buildSpanSummary($spans),
                $this->buildSubstructureSummary($substructures),
                $this->buildProtectionSummary($protection),
            ]),
        ];

        if (! $withRelations) {
            return $decorated;
        }

        return [
            ...$decorated,
            'profile' => $profile ?? $this->firstByBridge(self::PROFILE_TABLE, $bridge['uniqid']),
            'spans' => $spans !== [] ? $spans : $this->manyByBridge(self::SPAN_TABLE, $bridge['uniqid']),
            'substructures' => $substructures !== [] ? $substructures : $this->manyByBridge(self::SUBSTRUCTURE_TABLE, $bridge['uniqid']),
            'protection' => $protection ?? $this->firstByBridge(self::PROTECTION_TABLE, $bridge['uniqid']),
            'assessment' => $assessment ?? $this->firstByBridge(self::ASSESSMENT_TOTAL_TABLE, $bridge['uniqid']),
            'relations' => $this->relationMap(),
        ];
    }

    /**
     * @param  array<int, string>  $uniqids
     * @return array<string, array<string, mixed>>
     */
    private function loadRelationSnapshots(array $uniqids): array
    {
        $uniqids = array_values(array_unique(array_filter(array_map(
            fn (mixed $uniqid): ?string => $this->normalizeString($uniqid),
            $uniqids,
        ))));

        if ($uniqids === []) {
            return [];
        }

        $snapshots = [];

        foreach ($uniqids as $uniqid) {
            $snapshots[$uniqid] = [
                'profile' => null,
                'spans' => [],
                'substructures' => [],
                'protection' => null,
                'assessment' => null,
            ];
        }

        $profileRows = DB::connection($this->connectionName())
            ->table(self::PROFILE_TABLE)
            ->whereIn('id_jembatan', $uniqids)
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        foreach ($profileRows as $row) {
            $bridgeId = (string) ($row['id_jembatan'] ?? '');

            if ($bridgeId !== '' && isset($snapshots[$bridgeId]) && $snapshots[$bridgeId]['profile'] === null) {
                $snapshots[$bridgeId]['profile'] = $row;
            }
        }

        $spanRows = DB::connection($this->connectionName())
            ->table(self::SPAN_TABLE)
            ->whereIn('id_jembatan', $uniqids)
            ->orderBy('id_jembatan')
            ->orderBy('urut')
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        foreach ($spanRows as $row) {
            $bridgeId = (string) ($row['id_jembatan'] ?? '');

            if ($bridgeId !== '' && isset($snapshots[$bridgeId])) {
                $snapshots[$bridgeId]['spans'][] = $row;
            }
        }

        $substructureRows = DB::connection($this->connectionName())
            ->table(self::SUBSTRUCTURE_TABLE)
            ->whereIn('id_jembatan', $uniqids)
            ->orderBy('id_jembatan')
            ->orderBy('urut')
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        foreach ($substructureRows as $row) {
            $bridgeId = (string) ($row['id_jembatan'] ?? '');

            if ($bridgeId !== '' && isset($snapshots[$bridgeId])) {
                $snapshots[$bridgeId]['substructures'][] = $row;
            }
        }

        $protectionRows = DB::connection($this->connectionName())
            ->table(self::PROTECTION_TABLE)
            ->whereIn('id_jembatan', $uniqids)
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        foreach ($protectionRows as $row) {
            $bridgeId = (string) ($row['id_jembatan'] ?? '');

            if ($bridgeId !== '' && isset($snapshots[$bridgeId]) && $snapshots[$bridgeId]['protection'] === null) {
                $snapshots[$bridgeId]['protection'] = $row;
            }
        }

        $assessmentRows = DB::connection($this->connectionName())
            ->table(self::ASSESSMENT_TOTAL_TABLE)
            ->whereIn('id_jembatan', $uniqids)
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        foreach ($assessmentRows as $row) {
            $bridgeId = (string) ($row['id_jembatan'] ?? '');

            if ($bridgeId !== '' && isset($snapshots[$bridgeId]) && $snapshots[$bridgeId]['assessment'] === null) {
                $snapshots[$bridgeId]['assessment'] = $row;
            }
        }

        return $snapshots;
    }

    private function findOrFail(string $uniqid): array
    {
        $record = $this->find($uniqid);

        if ($record !== null) {
            return $record;
        }

        throw ValidationException::withMessages([
            'uniqid' => ['Data jembatan source tidak ditemukan.'],
        ]);
    }

    private function ensureBridgeDoesNotExist(string $uniqid): void
    {
        $exists = DB::connection($this->connectionName())
            ->table(self::MAIN_TABLE)
            ->where('uniqid', $uniqid)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'uniqid' => ['Uniqid jembatan source sudah digunakan.'],
            ]);
        }
    }

    private function baseQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::connection($this->connectionName())
            ->table(self::MAIN_TABLE)
            ->whereNull('deleted_at');
    }

    private function applySearch(\Illuminate\Database\Query\Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $stationMatches = $this->matchingReferenceKeys('m_stasiun', $search);
        $wilkerMatches = $this->matchingReferenceKeys('m_wilayah_kerja', $search);
        $lintasMatches = $this->matchingReferenceKeys('m_lintas', $search);

        $query->where(function (\Illuminate\Database\Query\Builder $builder) use ($search, $stationMatches, $wilkerMatches, $lintasMatches): void {
            $builder
                ->where('uniqid', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                ->orWhere('no_bh', 'like', "%{$search}%")
                ->orWhere('km_hm', 'like', "%{$search}%")
                ->orWhere('arah_bh', 'like', "%{$search}%")
                ->orWhere('jenis', 'like', "%{$search}%");

            if ($stationMatches !== []) {
                $builder->orWhereIn('stasiun1', $stationMatches)
                    ->orWhereIn('stasiun2', $stationMatches);
            }

            if ($wilkerMatches !== []) {
                $builder->orWhereIn('wil_ker', $wilkerMatches);
            }

            if ($lintasMatches !== []) {
                $builder->orWhereIn('lintas', $lintasMatches);
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function matchingReferenceKeys(string $table, string $search): array
    {
        $rows = DB::connection($this->connectionName())
            ->table($table)
            ->where(function (\Illuminate\Database\Query\Builder $query) use ($search, $table): void {
                $query
                    ->where('uniqid', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");

                if ($this->tableHasColumn($table, 'nama')) {
                    $query->orWhere('nama', 'like', "%{$search}%");
                }
            })
            ->limit(100)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        $keys = [];

        foreach ($rows as $row) {
            foreach (['uniqid', 'kode', 'id', 'nama'] as $column) {
                $value = $row[$column] ?? null;

                if ($value !== null && $value !== '') {
                    $keys[] = (string) $value;
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function referenceLookups(): array
    {
        return [
            'm_provinsi' => $this->buildLookup('m_provinsi', ['id', 'kode', 'uniqid']),
            'm_kabkot' => $this->buildLookup('m_kabkot', ['id', 'kode', 'uniqid']),
            'm_lintas' => $this->buildLookup('m_lintas', ['id', 'kode', 'uniqid', 'nama']),
            'm_stasiun' => $this->buildLookup('m_stasiun', ['id', 'kode', 'uniqid', 'nama']),
            'm_wilayah_kerja' => $this->buildLookup('m_wilayah_kerja', ['id', 'kode', 'uniqid', 'nama']),
            'm_wilayah_operasi' => $this->buildLookup('m_wilayah_operasi', ['id', 'kode', 'uniqid', 'nama']),
        ];
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, array<string, mixed>>
     */
    private function buildLookup(string $table, array $keys): array
    {
        $rows = DB::connection($this->connectionName())
            ->table($table)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        $lookup = [];

        foreach ($rows as $row) {
            foreach ($keys as $key) {
                $value = $row[$key] ?? null;
                $normalized = $this->normalizeLookupKey($value);

                if ($normalized !== null) {
                    $lookup[$normalized] = $row;
                }
            }
        }

        return $lookup;
    }

    /**
     * @param  array<string, array<string, mixed>>  $lookup
     */
    private function resolveReferenceLabel(array $lookup, mixed $value): ?string
    {
        $key = $this->normalizeLookupKey($value);

        if ($key === null || ! isset($lookup[$key])) {
            return null;
        }

        return $this->normalizeString($lookup[$key]['nama'] ?? null)
            ?? $this->normalizeString($lookup[$key]['kode'] ?? null)
            ?? $this->normalizeString($lookup[$key]['uniqid'] ?? null);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function firstByBridge(string $table, string $uniqid): ?array
    {
        $row = DB::connection($this->connectionName())
            ->table($table)
            ->where('id_jembatan', $uniqid)
            ->orderBy('id')
            ->first();

        return $row instanceof \stdClass ? (array) $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function manyByBridge(string $table, string $uniqid): array
    {
        return DB::connection($this->connectionName())
            ->table($table)
            ->where('id_jembatan', $uniqid)
            ->orderBy('urut')
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();
    }

    /**
     * @param  array<int, string|null>  $parts
     */
    private function compactJoin(array $parts, string $separator = ' | '): ?string
    {
        $items = array_values(array_filter(array_map(
            fn (mixed $value): ?string => $this->normalizeString($value),
            $parts,
        )));

        return $items === [] ? null : implode($separator, $items);
    }

    /**
     * @param  array<string, mixed>|null  $profile
     */
    private function buildProfileSummary(?array $profile): ?string
    {
        if ($profile === null) {
            return null;
        }

        return $this->compactJoin([
            $this->normalizeString($profile['perpotongan'] ?? null),
            isset($profile['jml_bentang']) && $profile['jml_bentang'] !== null
                ? (string) $profile['jml_bentang'].' bentang'
                : null,
            $this->normalizeString($profile['pjg_total'] ?? null) !== null
                ? 'total '.$this->normalizeString($profile['pjg_total'] ?? null).' m'
                : null,
            $this->normalizeString($profile['thn_selesai'] ?? null) !== null
                ? 'selesai '.$this->normalizeString($profile['thn_selesai'] ?? null)
                : null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $spans
     */
    private function buildSpanSummary(array $spans): ?string
    {
        if ($spans === []) {
            return null;
        }

        $lengths = array_values(array_filter(array_map(
            fn (array $span): ?string => $this->normalizeString($span['pjg_bentang'] ?? null),
            $spans,
        )));

        return $this->compactJoin([
            count($spans).' data bentang',
            $lengths !== [] ? implode(' + ', array_slice($lengths, 0, 3)).' m' : null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $substructures
     */
    private function buildSubstructureSummary(array $substructures): ?string
    {
        if ($substructures === []) {
            return null;
        }

        return $this->compactJoin([
            count($substructures).' struktur bawah',
            $this->compactJoin(array_values(array_unique(array_filter(array_map(
                fn (array $substructure): ?string => $this->normalizeString($substructure['material'] ?? null),
                $substructures,
            )))), ', '),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $protection
     */
    private function buildProtectionSummary(?array $protection): ?string
    {
        if ($protection === null) {
            return null;
        }

        return $this->compactJoin([
            $this->normalizeString($protection['pelindung_arus_material'] ?? null),
            $this->normalizeString($protection['pengarah_arus_material'] ?? null),
            $this->normalizeString($protection['pelindung_longsoran_material'] ?? null),
        ], ', ');
    }

    /**
     * @param  array<string, mixed>|null  $assessment
     */
    private function buildAssessmentSummary(?array $assessment): ?string
    {
        if ($assessment === null) {
            return null;
        }

        return $this->compactJoin([
            isset($assessment['total']) && $assessment['total'] !== null
                ? 'nilai '.rtrim(rtrim(number_format((float) $assessment['total'], 2, '.', ''), '0'), '.')
                : null,
            isset($assessment['kesimpulan']) && $assessment['kesimpulan'] !== null
                ? 'kesimpulan '.$assessment['kesimpulan']
                : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function mainTablePayload(
        array $payload,
        string $uniqid,
        string $actorName,
        Carbon $timestamp,
        bool $creating,
    ): array {
        $record = [
            'uniqid' => $uniqid,
            'tanggal' => $payload['tanggal'] ?? null,
            'wil_ker' => $this->nullableString($payload['wil_ker'] ?? null),
            'id_prov' => $this->nullableString($payload['id_prov'] ?? null),
            'id_kabkot' => $this->nullableString($payload['id_kabkot'] ?? null),
            'wil_op' => $this->nullableString($payload['wil_op'] ?? null),
            'lat' => (string) ($payload['lat'] ?? ''),
            'lon' => (string) ($payload['lon'] ?? ''),
            'nama' => (string) ($payload['nama'] ?? ''),
            'lintas' => $this->nullableString($payload['lintas'] ?? null),
            'stasiun1' => $this->nullableString($payload['stasiun1'] ?? null),
            'stasiun2' => $this->nullableString($payload['stasiun2'] ?? null),
            'no_bh' => $this->nullableString($payload['no_bh'] ?? null),
            'arah_bh' => $this->nullableString($payload['arah_bh'] ?? null),
            'jenis' => $this->nullableString($payload['jenis'] ?? null),
            'km_hm' => $this->nullableString($payload['km_hm'] ?? null),
            'foto1' => $this->nullableString($payload['foto1'] ?? null),
            'foto2' => $this->nullableString($payload['foto2'] ?? null),
            'foto3' => $this->nullableString($payload['foto3'] ?? null),
            'foto4' => $this->nullableString($payload['foto4'] ?? null),
            'caption1' => $this->nullableString($payload['caption1'] ?? null),
            'caption2' => $this->nullableString($payload['caption2'] ?? null),
            'caption3' => $this->nullableString($payload['caption3'] ?? null),
            'caption4' => $this->nullableString($payload['caption4'] ?? null),
            'dokumen' => $this->nullableString($payload['dokumen'] ?? null),
            'video' => $this->nullableString($payload['video'] ?? null),
            'catatan' => $this->nullableString($payload['catatan'] ?? null),
            'active' => (int) ($payload['active'] ?? 1),
            'status' => (int) ($payload['status'] ?? 1),
            'statusdata' => (int) ($payload['statusdata'] ?? 0),
            'updated_by' => $actorName,
            'updated_at' => $timestamp,
            'deleted_at' => null,
        ];

        if ($creating) {
            $record['created_by'] = $actorName;
            $record['created_at'] = $timestamp;
        }

        return $record;
    }

    /**
     * @param  array<string, mixed>|null  $profile
     */
    private function syncProfile(
        \Illuminate\Database\Connection $connection,
        string $uniqid,
        ?array $profile,
        string $actorName,
        Carbon $timestamp,
        bool $creating,
    ): void {
        if ($profile === null || $profile === [] || ! $this->hasMeaningfulValues($profile, ['uniqid', 'active'])) {
            $connection->table(self::PROFILE_TABLE)->where('id_jembatan', $uniqid)->delete();

            return;
        }

        $payload = [
            'uniqid' => $this->normalizeString($profile['uniqid'] ?? null) ?? uniqid(),
            'id_jembatan' => $uniqid,
            'perpotongan' => $this->nullableString($profile['perpotongan'] ?? null),
            'jml_lintasan' => $profile['jml_lintasan'] ?? null,
            'jml_bentang' => $profile['jml_bentang'] ?? null,
            'pjg_bentang1' => $this->nullableString($profile['pjg_bentang1'] ?? null),
            'pjg_bentang2' => $this->nullableString($profile['pjg_bentang2'] ?? null),
            'pjg_bentang3' => $this->nullableString($profile['pjg_bentang3'] ?? null),
            'pjg_total' => $this->nullableString($profile['pjg_total'] ?? null),
            'thn_selesai' => $this->nullableString($profile['thn_selesai'] ?? null),
            'rm_bgn_atas' => $this->nullableString($profile['rm_bgn_atas'] ?? null),
            'rm_bgn_bawah' => $this->nullableString($profile['rm_bgn_bawah'] ?? null),
            'active' => (int) ($profile['active'] ?? 1),
            'updated_by' => $actorName,
            'updated_at' => $timestamp,
        ];

        $existingId = $connection->table(self::PROFILE_TABLE)
            ->where('id_jembatan', $uniqid)
            ->value('id');

        if ($existingId === null) {
            $payload['created_by'] = $actorName;
            $payload['created_at'] = $timestamp;
            $connection->table(self::PROFILE_TABLE)->insert($payload);

            return;
        }

        if ($creating) {
            $payload['created_by'] = $actorName;
            $payload['created_at'] = $timestamp;
        }

        $connection->table(self::PROFILE_TABLE)
            ->where('id', $existingId)
            ->update($payload);
    }

    /**
     * @param  array<string, mixed>|null  $protection
     */
    private function syncProtection(
        \Illuminate\Database\Connection $connection,
        string $uniqid,
        ?array $protection,
        string $actorName,
        Carbon $timestamp,
        bool $creating,
    ): void {
        if ($protection === null || $protection === [] || ! $this->hasMeaningfulValues($protection, ['uniqid'])) {
            $connection->table(self::PROTECTION_TABLE)->where('id_jembatan', $uniqid)->delete();

            return;
        }

        $payload = [
            'uniqid' => $this->normalizeString($protection['uniqid'] ?? null) ?? uniqid(),
            'id_jembatan' => $uniqid,
            'pelindung_arus_material' => $this->nullableString($protection['pelindung_arus_material'] ?? null),
            'pelindung_arus_tipe' => $this->nullableString($protection['pelindung_arus_tipe'] ?? null),
            'pengarah_arus_material' => $this->nullableString($protection['pengarah_arus_material'] ?? null),
            'pengarah_arus_tipe' => $this->nullableString($protection['pengarah_arus_tipe'] ?? null),
            'pelindung_longsoran_material' => $this->nullableString($protection['pelindung_longsoran_material'] ?? null),
            'pelindung_longsoran_tipe' => $this->nullableString($protection['pelindung_longsoran_tipe'] ?? null),
            'updated_by' => $actorName,
            'updated_at' => $timestamp,
        ];

        $existingId = $connection->table(self::PROTECTION_TABLE)
            ->where('id_jembatan', $uniqid)
            ->value('id');

        if ($existingId === null) {
            $payload['created_by'] = $actorName;
            $payload['created_at'] = $timestamp;
            $connection->table(self::PROTECTION_TABLE)->insert($payload);

            return;
        }

        if ($creating) {
            $payload['created_by'] = $actorName;
            $payload['created_at'] = $timestamp;
        }

        $connection->table(self::PROTECTION_TABLE)
            ->where('id', $existingId)
            ->update($payload);
    }

    /**
     * @param  array<string, mixed>|null  $assessment
     */
    private function syncAssessmentTotal(
        \Illuminate\Database\Connection $connection,
        string $uniqid,
        ?array $assessment,
        string $actorName,
        Carbon $timestamp,
        bool $creating,
    ): void {
        if ($assessment === null || $assessment === [] || ! $this->hasMeaningfulValues($assessment, ['uniqid'])) {
            $connection->table(self::ASSESSMENT_TOTAL_TABLE)->where('id_jembatan', $uniqid)->delete();

            return;
        }

        $payload = [
            'uniqid' => $this->normalizeString($assessment['uniqid'] ?? null) ?? uniqid(),
            'id_jembatan' => $uniqid,
            'total' => $assessment['total'] ?? null,
            'kesimpulan' => $assessment['kesimpulan'] ?? null,
            'updated_by' => $actorName,
            'updated_at' => $timestamp,
        ];

        $existingId = $connection->table(self::ASSESSMENT_TOTAL_TABLE)
            ->where('id_jembatan', $uniqid)
            ->value('id');

        if ($existingId === null) {
            $payload['created_by'] = $actorName;
            $payload['created_at'] = $timestamp;
            $connection->table(self::ASSESSMENT_TOTAL_TABLE)->insert($payload);

            return;
        }

        if ($creating) {
            $payload['created_by'] = $actorName;
            $payload['created_at'] = $timestamp;
        }

        $connection->table(self::ASSESSMENT_TOTAL_TABLE)
            ->where('id', $existingId)
            ->update($payload);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function replaceRows(
        \Illuminate\Database\Connection $connection,
        string $table,
        string $uniqid,
        array $rows,
        string $actorName,
        Carbon $timestamp,
        string $type,
    ): void {
        $connection->table($table)->where('id_jembatan', $uniqid)->delete();

        if ($rows === []) {
            return;
        }

        $normalized = [];

        foreach (array_values($rows) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            if (! $this->hasMeaningfulValues($row, ['uniqid', 'active', 'urut'])) {
                continue;
            }

            if ($type === 'span') {
                $normalized[] = [
                    'uniqid' => $this->normalizeString($row['uniqid'] ?? null) ?? uniqid(),
                    'id_jembatan' => $uniqid,
                    'pjg_bentang' => $this->nullableString($row['pjg_bentang'] ?? null),
                    'urut' => (int) ($row['urut'] ?? ($index + 1)),
                    'active' => (int) ($row['active'] ?? 1),
                    'created_by' => $actorName,
                    'created_at' => $timestamp,
                    'updated_by' => $actorName,
                    'updated_at' => $timestamp,
                ];

                continue;
            }

            $normalized[] = [
                'uniqid' => $this->normalizeString($row['uniqid'] ?? null) ?? uniqid(),
                'id_jembatan' => $uniqid,
                'nomor' => $this->nullableString($row['nomor'] ?? null),
                'material' => $this->nullableString($row['material'] ?? null),
                'tipe' => $this->nullableString($row['tipe'] ?? null),
                'manteling' => $this->nullableString($row['manteling'] ?? null),
                'jenis' => $this->nullableString($row['jenis'] ?? null),
                'urut' => (int) ($row['urut'] ?? ($index + 1)),
                'created_by' => $actorName,
                'created_at' => $timestamp,
                'updated_by' => $actorName,
                'updated_at' => $timestamp,
            ];
        }

        if ($normalized !== []) {
            $connection->table($table)->insert($normalized);
        }
    }

    private function connectionName(): string
    {
        return $this->bridgeSourceSql->connectionName();
    }

    private function actorName(mixed $actor): string
    {
        if ($actor instanceof User) {
            return Str::limit(trim($actor->name !== '' ? $actor->name : $actor->email), 64, '');
        }

        if ($actor instanceof ApiClient) {
            return Str::limit(trim($actor->name !== '' ? $actor->name : $actor->code), 64, '');
        }

        return 'api';
    }

    private function normalizeLookupKey(mixed $value): ?string
    {
        $value = $this->normalizeString($value);

        return $value === null ? null : mb_strtoupper($value);
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function nullableString(mixed $value): ?string
    {
        return $this->normalizeString($value);
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        static $cache = [];

        $key = $this->connectionName().':'.$table.':'.$column;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $columns = DB::connection($this->connectionName())
            ->getSchemaBuilder()
            ->getColumnListing($table);

        return $cache[$key] = in_array($column, $columns, true);
    }

    /**
     * @return array<int, string>
     */
    private function allowedTables(): array
    {
        $tables = config('master-data.bridge_source.tables', []);

        return array_values(array_filter(is_array($tables) ? $tables : [], fn (mixed $table): bool => is_string($table) && $table !== ''));
    }

    private function assertAllowedTable(string $table): void
    {
        if (! in_array($table, $this->allowedTables(), true) || ! $this->tableExists($table)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel source jembatan tidak ditemukan atau tidak diizinkan.'],
            ]);
        }
    }

    private function tableCount(string $table): int
    {
        try {
            return DB::connection($this->connectionName())->table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::connection($this->connectionName())
                ->getSchemaBuilder()
                ->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveRawTableRowKey(string $table, array $row, int $index): string
    {
        foreach (['row_key', 'uniqid', 'uuid', 'kode', 'code', 'id'] as $candidate) {
            $value = $row[$candidate] ?? null;

            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return sprintf('%s-%d', $table, $index + 1);
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $ignoredKeys
     */
    private function hasMeaningfulValues(array $values, array $ignoredKeys = []): bool
    {
        foreach ($values as $key => $value) {
            if (in_array((string) $key, $ignoredKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                if ($this->hasMeaningfulValues($value)) {
                    return true;
                }

                continue;
            }

            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }
}
