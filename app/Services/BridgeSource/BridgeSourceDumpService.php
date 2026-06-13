<?php

namespace App\Services\BridgeSource;

use App\Services\Import\SqlDumpTableReader;
use App\Support\BridgeSource\BridgeSourceSql;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class BridgeSourceDumpService
{
    public function __construct(
        private readonly SqlDumpTableReader $reader,
        private readonly BridgeSourceSql $bridgeSourceSql,
    ) {}

    public function countCombined(): int
    {
        return count($this->rows('m_jembatan'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tables(): array
    {
        return array_values(array_map(function (string $table): array {
            $meta = $this->bridgeSourceSql->tableMeta($table);

            return [
                'key' => $table,
                'table' => $table,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'kind' => $this->bridgeSourceSql->tableKind($table),
                'row_count' => count($this->rows($table)),
                'href' => route('dashboard.bridge-source.tables.show', ['table' => $table]),
            ];
        }, $this->allowedTables()));
    }

    /**
     * @return array<string, mixed>
     */
    public function tablePage(string $table): array
    {
        $this->assertAllowedTable($table);
        $rows = $this->rows($table);
        $columns = $rows === [] ? ['row_key'] : array_keys($rows[0]);
        $meta = $this->bridgeSourceSql->tableMeta($table);

        return [
            'table' => $table,
            'label' => $meta['label'],
            'description' => $meta['description'],
            'kind' => $this->bridgeSourceSql->tableKind($table),
            'row_count' => count($rows),
            'columns' => $columns,
            'list_endpoint' => route('dashboard.bridge-source.tables.rows', ['table' => $table]),
        ];
    }

    public function paginate(string $table, array $filters = []): LengthAwarePaginator
    {
        $this->assertAllowedTable($table);

        $perPage = min(
            max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1),
            config('master-data.pagination.max_per_page'),
        );
        $page = max((int) ($filters['page'] ?? 1), 1);
        $search = trim((string) ($filters['search'] ?? ''));

        $rows = $this->rows($table);

        if ($search !== '') {
            $rows = array_values(array_filter($rows, function (array $row) use ($search): bool {
                foreach ($row as $value) {
                    if (is_scalar($value) && str_contains(mb_strtolower((string) $value), mb_strtolower($search))) {
                        return true;
                    }
                }

                return false;
            }));
        }

        $total = count($rows);
        $items = array_slice($rows, ($page - 1) * $perPage, $perPage);

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

    public function paginateCombined(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(
            max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1),
            config('master-data.pagination.max_per_page'),
        );
        $page = max((int) ($filters['page'] ?? 1), 1);
        $search = trim((string) ($filters['search'] ?? ''));

        $rows = array_map(
            fn (array $row): array => $this->decorateBridge($row, false),
            $this->rows('m_jembatan'),
        );

        if ($search !== '') {
            $rows = array_values(array_filter($rows, function (array $row) use ($search): bool {
                foreach ($row as $value) {
                    if (is_scalar($value) && str_contains(mb_strtolower((string) $value), mb_strtolower($search))) {
                        return true;
                    }
                }

                return false;
            }));
        }

        usort($rows, function (array $left, array $right): int {
            return strcmp(
                (string) ($right['updated_at'] ?? $right['created_at'] ?? ''),
                (string) ($left['updated_at'] ?? $left['created_at'] ?? ''),
            );
        });

        $total = count($rows);
        $items = array_slice($rows, ($page - 1) * $perPage, $perPage);

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

    /**
     * @return array<string, mixed>|null
     */
    public function findCombined(string $uniqid): ?array
    {
        foreach ($this->rows('m_jembatan') as $row) {
            if ((string) ($row['uniqid'] ?? '') === $uniqid) {
                return $this->decorateBridge($row, true);
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(string $table): array
    {
        $this->assertAllowedTable($table);

        $cacheKey = sprintf('bridge-source-dump:%s:%s', md5($this->dumpPath()), $table);
        $ttl = max((int) config('master-data.cache.ttl', 300), 1);

        /** @var array<int, array<string, mixed>> $rows */
        $rows = Cache::remember($cacheKey, now()->addSeconds($ttl), function () use ($table): array {
            $rows = $this->reader->readTable($this->dumpPath(), $table);

            return array_values(array_map(
                fn (array $row, int $index): array => [
                    ...$row,
                    'row_key' => $this->resolveRowKey($row, $index),
                ],
                $rows,
                array_keys($rows),
            ));
        });

        return $rows;
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
        if (! in_array($table, $this->allowedTables(), true)) {
            throw ValidationException::withMessages([
                'table' => ['Tabel source jembatan tidak ditemukan atau tidak diizinkan.'],
            ]);
        }
    }

    private function dumpPath(): string
    {
        return base_path((string) config('master-data.bridge_source.dump_path'));
    }

    /**
     * @param  array<string, mixed>  $bridge
     * @return array<string, mixed>
     */
    private function decorateBridge(array $bridge, bool $withRelations): array
    {
        $lookups = $this->lookupMaps();
        $profile = $this->firstRelated('m_jembatan_profil', 'id_jembatan', (string) ($bridge['uniqid'] ?? ''));
        $spans = $this->manyRelated('m_jembatan_bentang', 'id_jembatan', (string) ($bridge['uniqid'] ?? ''), ['urut', 'id']);
        $substructures = $this->manyRelated('m_jembatan_bawah', 'id_jembatan', (string) ($bridge['uniqid'] ?? ''), ['urut', 'id']);
        $protection = $this->firstRelated('m_jembatan_detil_3', 'id_jembatan', (string) ($bridge['uniqid'] ?? ''));
        $assessment = $this->firstRelated('m_jembatan_nilai_total', 'id_jembatan', (string) ($bridge['uniqid'] ?? ''));

        $decorated = [
            ...$bridge,
            'wil_ker_name' => $this->lookupLabel($lookups['m_wilayah_kerja'], $bridge['wil_ker'] ?? null),
            'province_name' => $this->lookupLabel($lookups['m_provinsi'], $bridge['id_prov'] ?? null),
            'city_name' => $this->lookupLabel($lookups['m_kabkot'], $bridge['id_kabkot'] ?? null),
            'wil_op_name' => $this->lookupLabel($lookups['m_wilayah_operasi'], $bridge['wil_op'] ?? null),
            'lintas_name' => $this->lookupLabel($lookups['m_lintas'], $bridge['lintas'] ?? null),
            'stasiun1_name' => $this->lookupLabel($lookups['m_stasiun'], $bridge['stasiun1'] ?? null),
            'stasiun2_name' => $this->lookupLabel($lookups['m_stasiun'], $bridge['stasiun2'] ?? null),
            'bridge_identity' => $this->joinValues([
                $this->stringValue($bridge['no_bh'] ?? null),
                $this->stringValue($bridge['nama'] ?? null),
                $this->stringValue($bridge['jenis'] ?? null),
            ]),
            'location_summary' => $this->joinValues([
                $this->stringValue($bridge['km_hm'] ?? null) !== null ? 'KM/HM '.$this->stringValue($bridge['km_hm'] ?? null) : null,
                $this->joinValues([
                    $this->lookupLabel($lookups['m_provinsi'], $bridge['id_prov'] ?? null),
                    $this->lookupLabel($lookups['m_kabkot'], $bridge['id_kabkot'] ?? null),
                ], ', '),
            ]),
            'route_summary' => $this->joinValues([
                $this->joinValues([
                    $this->lookupLabel($lookups['m_stasiun'], $bridge['stasiun1'] ?? null),
                    $this->lookupLabel($lookups['m_stasiun'], $bridge['stasiun2'] ?? null),
                ], ' -> '),
                $this->lookupLabel($lookups['m_lintas'], $bridge['lintas'] ?? null),
            ]),
            'wilayah_summary' => $this->joinValues([
                $this->lookupLabel($lookups['m_wilayah_kerja'], $bridge['wil_ker'] ?? null),
                $this->lookupLabel($lookups['m_wilayah_operasi'], $bridge['wil_op'] ?? null),
            ]),
            'profile_summary' => $this->profileSummary($profile),
            'span_summary' => $this->spanSummary($spans),
            'substructure_summary' => $this->substructureSummary($substructures),
            'protection_summary' => $this->protectionSummary($protection),
            'assessment_summary' => $this->assessmentSummary($assessment),
            'structure_summary' => $this->joinValues([
                $this->profileSummary($profile),
                $this->spanSummary($spans),
                $this->substructureSummary($substructures),
                $this->protectionSummary($protection),
            ]),
        ];

        if (! $withRelations) {
            return $decorated;
        }

        return [
            ...$decorated,
            'profile' => $profile,
            'spans' => $spans,
            'substructures' => $substructures,
            'protection' => $protection,
            'assessment' => $assessment,
        ];
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function lookupMaps(): array
    {
        static $cache = null;

        if (is_array($cache)) {
            return $cache;
        }

        $cache = [
            'm_provinsi' => $this->buildLookup('m_provinsi', ['id', 'kode', 'uniqid']),
            'm_kabkot' => $this->buildLookup('m_kabkot', ['id', 'kode', 'uniqid']),
            'm_lintas' => $this->buildLookup('m_lintas', ['id', 'kode', 'uniqid', 'nama']),
            'm_stasiun' => $this->buildLookup('m_stasiun', ['id', 'kode', 'uniqid', 'nama']),
            'm_wilayah_kerja' => $this->buildLookup('m_wilayah_kerja', ['id', 'kode', 'uniqid', 'nama']),
            'm_wilayah_operasi' => $this->buildLookup('m_wilayah_operasi', ['id', 'kode', 'uniqid', 'nama']),
        ];

        return $cache;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, array<string, mixed>>
     */
    private function buildLookup(string $table, array $keys): array
    {
        $lookup = [];

        foreach ($this->rows($table) as $row) {
            foreach ($keys as $key) {
                $normalized = $this->normalizeLookupKey($row[$key] ?? null);

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
    private function lookupLabel(array $lookup, mixed $value): ?string
    {
        $normalized = $this->normalizeLookupKey($value);

        if ($normalized === null || ! isset($lookup[$normalized])) {
            return null;
        }

        return $this->stringValue($lookup[$normalized]['nama'] ?? null)
            ?? $this->stringValue($lookup[$normalized]['kode'] ?? null)
            ?? $this->stringValue($lookup[$normalized]['uniqid'] ?? null)
            ?? $this->stringValue($lookup[$normalized]['id'] ?? null);
    }

    /**
     * @param  array<string, mixed>|null  $profile
     */
    private function profileSummary(?array $profile): ?string
    {
        if ($profile === null) {
            return null;
        }

        return $this->joinValues([
            $this->stringValue($profile['perpotongan'] ?? null),
            isset($profile['jml_bentang']) && $profile['jml_bentang'] !== null ? (string) $profile['jml_bentang'].' bentang' : null,
            $this->stringValue($profile['pjg_total'] ?? null) !== null ? 'total '.$this->stringValue($profile['pjg_total'] ?? null).' m' : null,
            $this->stringValue($profile['thn_selesai'] ?? null) !== null ? 'selesai '.$this->stringValue($profile['thn_selesai'] ?? null) : null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $spans
     */
    private function spanSummary(array $spans): ?string
    {
        if ($spans === []) {
            return null;
        }

        $lengths = array_values(array_filter(array_map(
            fn (array $span): ?string => $this->stringValue($span['pjg_bentang'] ?? null),
            $spans,
        )));

        return $this->joinValues([
            count($spans).' data bentang',
            $lengths !== [] ? implode(' + ', array_slice($lengths, 0, 3)).' m' : null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $substructures
     */
    private function substructureSummary(array $substructures): ?string
    {
        if ($substructures === []) {
            return null;
        }

        $materials = array_values(array_unique(array_filter(array_map(
            fn (array $row): ?string => $this->stringValue($row['material'] ?? null),
            $substructures,
        ))));

        return $this->joinValues([
            count($substructures).' struktur bawah',
            $materials !== [] ? implode(', ', $materials) : null,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $protection
     */
    private function protectionSummary(?array $protection): ?string
    {
        if ($protection === null) {
            return null;
        }

        return $this->joinValues([
            $this->stringValue($protection['pelindung_arus_material'] ?? null),
            $this->stringValue($protection['pengarah_arus_material'] ?? null),
            $this->stringValue($protection['pelindung_longsoran_material'] ?? null),
        ], ', ');
    }

    /**
     * @param  array<string, mixed>|null  $assessment
     */
    private function assessmentSummary(?array $assessment): ?string
    {
        if ($assessment === null) {
            return null;
        }

        return $this->joinValues([
            isset($assessment['total']) && $assessment['total'] !== null
                ? 'nilai '.rtrim(rtrim(number_format((float) $assessment['total'], 2, '.', ''), '0'), '.')
                : null,
            isset($assessment['kesimpulan']) && $assessment['kesimpulan'] !== null
                ? 'kesimpulan '.$assessment['kesimpulan']
                : null,
        ]);
    }

    /**
     * @param  array<int, string|null>  $values
     */
    private function joinValues(array $values, string $separator = ' | '): ?string
    {
        $items = array_values(array_filter(array_map(
            fn (mixed $value): ?string => $this->stringValue($value),
            $values,
        )));

        return $items === [] ? null : implode($separator, $items);
    }

    /**
     * @param  array<int, string>  $sortKeys
     * @return array<int, array<string, mixed>>
     */
    private function manyRelated(string $table, string $foreignKey, string $value, array $sortKeys = []): array
    {
        $rows = array_values(array_filter($this->rows($table), fn (array $row): bool => (string) ($row[$foreignKey] ?? '') === $value));

        if ($sortKeys !== []) {
            usort($rows, function (array $left, array $right) use ($sortKeys): int {
                foreach ($sortKeys as $sortKey) {
                    $comparison = ($left[$sortKey] ?? null) <=> ($right[$sortKey] ?? null);

                    if ($comparison !== 0) {
                        return $comparison;
                    }
                }

                return 0;
            });
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function firstRelated(string $table, string $foreignKey, string $value): ?array
    {
        foreach ($this->rows($table) as $row) {
            if ((string) ($row[$foreignKey] ?? '') === $value) {
                return $row;
            }
        }

        return null;
    }

    private function normalizeLookupKey(mixed $value): ?string
    {
        $value = $this->stringValue($value);

        return $value === null ? null : mb_strtoupper($value);
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveRowKey(array $row, int $index): string
    {
        foreach (['id', 'uniqid', 'kode', 'id_jembatan', 'nama'] as $candidate) {
            $value = $row[$candidate] ?? null;

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return 'row-'.($index + 1);
    }
}
