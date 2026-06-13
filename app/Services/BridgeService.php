<?php

namespace App\Services;

use App\Support\BridgeSource\BridgeSourceSql;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BridgeService
{
    private const MAIN_TABLE = 'm_jembatan';

    public function __construct(
        private readonly BridgeSourceSql $bridgeSourceSql,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $limit = $this->limit($filters['limit'] ?? $filters['per_page'] ?? null, 100, 500);
        $page = max((int) ($filters['page'] ?? 1), 1);
        $query = $this->filteredBridgeQuery($filters);
        $total = (clone $query)->count();
        $rows = $query
            ->orderBy('id')
            ->forPage($page, $limit)
            ->get()
            ->map(fn (object $row): array => $this->bridgeListItem((array) $row))
            ->all();

        return new Paginator($rows, $total, $limit, $page, [
            'path' => request()->url(),
            'pageName' => 'page',
        ]);
    }

    public function findDetail(string $kodeJembatan): ?array
    {
        $bridge = $this->findBridge($kodeJembatan);

        if ($bridge === null) {
            return null;
        }

        $kode = (string) $bridge['uniqid'];

        return [
            ...$this->bridgeListItem($bridge),
            'raw' => $bridge,
            'profil' => $this->firstByBridge('m_jembatan_profil', $kode),
            'nilai_kondisi_terakhir' => $this->latestByBridge('m_jembatan_nilai_total', 'id_jembatan', $kode),
            'perawatan_terakhir' => $this->latestByBridge('m_jembatan_perawatan', 'kode_jembatan', $kode),
            'survey_terakhir' => $this->latestByBridge('m_jembatan_survey', 'kode_jembatan', $kode),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(array $filters): array
    {
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        $limit = $this->limit($filters['limit'] ?? null, 20, 50);

        return $this->filteredBridgeQuery(['keyword' => $keyword])
            ->orderBy('nama')
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => [
                'kode_jembatan' => (string) $row->uniqid,
                'nama' => $row->nama,
                'no_bh' => $row->no_bh,
                'km_hm' => $row->km_hm,
                'lintas' => $this->referenceLabel('m_lintas', $row->lintas),
            ])
            ->all();
    }

    public function byBridgeNumber(string $bridgeNumber): array
    {
        return $this->baseBridgeQuery()
            ->where('no_bh', $bridgeNumber)
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => $this->bridgeListItem((array) $row))
            ->all();
    }

    protected function filteredBridgeQuery(array $filters): Builder
    {
        $query = $this->baseBridgeQuery();
        $keyword = trim((string) ($filters['keyword'] ?? $filters['search'] ?? ''));

        foreach ([
            'wil_op' => 'wil_op',
            'wil_ker' => 'wil_ker',
            'lintas' => 'lintas',
            'id_prov' => 'id_prov',
            'id_kabkot' => 'id_kabkot',
            'jenis' => 'jenis',
            'active' => 'active',
            'statusdata' => 'statusdata',
        ] as $filter => $column) {
            if (array_key_exists($filter, $filters) && $filters[$filter] !== null && $filters[$filter] !== '') {
                $query->where($column, $filters[$filter]);
            }
        }

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('uniqid', 'like', "%{$keyword}%")
                    ->orWhere('nama', 'like', "%{$keyword}%")
                    ->orWhere('no_bh', 'like', "%{$keyword}%")
                    ->orWhere('km_hm', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    protected function baseBridgeQuery(): Builder
    {
        return DB::connection($this->connectionName())
            ->table(self::MAIN_TABLE)
            ->whereNull('deleted_at');
    }

    protected function findBridge(string $kodeJembatan): ?array
    {
        $row = $this->baseBridgeQuery()
            ->where('uniqid', $kodeJembatan)
            ->first();

        return $row instanceof \stdClass ? (array) $row : null;
    }

    protected function bridgeListItem(array $bridge): array
    {
        return [
            'kode_jembatan' => $bridge['uniqid'] ?? null,
            'nama' => $bridge['nama'] ?? null,
            'no_bh' => $bridge['no_bh'] ?? null,
            'jenis' => $bridge['jenis'] ?? null,
            'km_hm' => $bridge['km_hm'] ?? null,
            'lintas' => $this->referenceLabel('m_lintas', $bridge['lintas'] ?? null),
            'stasiun1' => $this->referenceLabel('m_stasiun', $bridge['stasiun1'] ?? null),
            'stasiun2' => $this->referenceLabel('m_stasiun', $bridge['stasiun2'] ?? null),
            'wilayah_operasi' => $this->referenceLabel('m_wilayah_operasi', $bridge['wil_op'] ?? null),
            'wilayah_kerja' => $this->referenceLabel('m_wilayah_kerja', $bridge['wil_ker'] ?? null),
            'id_prov' => $bridge['id_prov'] ?? null,
            'id_kabkot' => $bridge['id_kabkot'] ?? null,
            'lat' => $this->coordinate($bridge['lat'] ?? null),
            'lon' => $this->coordinate($bridge['lon'] ?? null),
            'active' => $this->nullableInt($bridge['active'] ?? null),
            'status' => $this->nullableInt($bridge['status'] ?? null),
            'statusdata' => $this->nullableInt($bridge['statusdata'] ?? null),
            'created_at' => $bridge['created_at'] ?? null,
            'updated_at' => $bridge['updated_at'] ?? null,
        ];
    }

    protected function referenceLabel(string $table, mixed $value): ?string
    {
        $key = $this->normalizeKey($value);

        if ($key === null) {
            return null;
        }

        static $cache = [];
        $connection = $this->connectionName();
        $cacheKey = $connection.'.'.$table;

        if (! isset($cache[$cacheKey])) {
            $cache[$cacheKey] = [];
            $rows = DB::connection($connection)->table($table)->get();

            foreach ($rows as $row) {
                $item = (array) $row;

                foreach (['id', 'kode', 'uniqid', 'nama'] as $column) {
                    $candidate = $this->normalizeKey($item[$column] ?? null);

                    if ($candidate !== null) {
                        $cache[$cacheKey][$candidate] = $item;
                    }
                }
            }
        }

        $row = $cache[$cacheKey][$key] ?? null;

        return $row === null
            ? (string) $value
            : ($row['nama'] ?? $row['kode'] ?? $row['uniqid'] ?? (string) $value);
    }

    protected function firstByBridge(string $table, string $kodeJembatan): ?array
    {
        $row = DB::connection($this->connectionName())
            ->table($table)
            ->where('id_jembatan', $kodeJembatan)
            ->orderBy('id')
            ->first();

        return $row instanceof \stdClass ? (array) $row : null;
    }

    protected function latestByBridge(string $table, string $foreignKey, string $kodeJembatan): ?array
    {
        $query = DB::connection($this->connectionName())
            ->table($table)
            ->where($foreignKey, $kodeJembatan);

        foreach (['tanggal', 'updated_at', 'id'] as $column) {
            if ($this->tableHasColumn($table, $column)) {
                $query->orderByDesc($column);
            }
        }

        $row = $query->first();

        return $row instanceof \stdClass ? (array) $row : null;
    }

    public function rowsByBridge(string $table, string $kodeJembatan, string $foreignKey = 'id_jembatan'): array
    {
        $query = DB::connection($this->connectionName())
            ->table($table)
            ->where($foreignKey, $kodeJembatan);

        foreach (['urut', 'tanggal', 'id'] as $column) {
            if ($this->tableHasColumn($table, $column)) {
                $query->orderBy($column);
            }
        }

        return $query
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        static $columns = [];

        $key = $this->connectionName().'.'.$table;

        if (! isset($columns[$key])) {
            $columns[$key] = DB::connection($this->connectionName())
                ->getSchemaBuilder()
                ->getColumnListing($table);
        }

        return in_array($column, $columns[$key], true);
    }

    protected function connectionName(): string
    {
        return $this->bridgeSourceSql->connectionName();
    }

    protected function limit(mixed $value, int $default, int $max): int
    {
        return min(max((int) ($value ?? $default), 1), $max);
    }

    protected function coordinate(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    protected function validLatitude(mixed $value): bool
    {
        $latitude = $this->coordinate($value);

        return $latitude !== null && $latitude >= -90 && $latitude <= 90;
    }

    protected function validLongitude(mixed $value): bool
    {
        $longitude = $this->coordinate($value);

        return $longitude !== null && $longitude >= -180 && $longitude <= 180;
    }

    protected function normalizeKey(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : Str::lower($normalized);
    }

    protected function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
