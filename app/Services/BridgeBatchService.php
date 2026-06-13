<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BridgeBatchService extends BridgeService
{
    public function batch(array $filters): array
    {
        $limit = $this->limit($filters['limit'] ?? null, 500, 1000);
        $cursor = max((int) ($filters['cursor'] ?? $filters['last_id'] ?? 0), 0);
        $query = $this->baseBridgeQuery()
            ->when($cursor > 0, fn ($builder) => $builder->where('id', '>', $cursor))
            ->when(($filters['active'] ?? null) !== null && ($filters['active'] ?? '') !== '', fn ($builder) => $builder->where('active', $filters['active']))
            ->when(! empty($filters['updated_since']), fn ($builder) => $builder->where('updated_at', '>=', $filters['updated_since']));

        $total = (clone $query)->count();
        $rows = $query->orderBy('id')->limit($limit + 1)->get();
        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit)->values();
        $nextCursor = $hasMore ? (int) $items->last()->id : null;

        return [
            'data' => $items->map(fn (object $row): array => $this->bridgeListItem((array) $row))->all(),
            'meta' => [
                'limit' => $limit,
                'next_cursor' => $nextCursor,
                'has_more' => $hasMore,
                'total' => $total,
            ],
        ];
    }

    public function fullBatch(array $filters): array
    {
        $batch = $this->batch([
            ...$filters,
            'limit' => $this->limit($filters['limit'] ?? null, 100, 200),
        ]);
        $codes = array_values(array_filter(array_map(
            fn (array $row): ?string => $row['kode_jembatan'] ?? null,
            $batch['data'],
        )));
        $relations = $this->loadRelations($codes);

        $batch['data'] = array_map(function (array $bridge) use ($relations): array {
            $kode = (string) $bridge['kode_jembatan'];

            return [
                ...$bridge,
                'lokasi' => [
                    'lat' => $bridge['lat'],
                    'lon' => $bridge['lon'],
                    'wilayah_operasi' => $bridge['wilayah_operasi'],
                    'wilayah_kerja' => $bridge['wilayah_kerja'],
                ],
                'profil' => $relations['profil'][$kode][0] ?? null,
                'bentang' => $relations['bentang'][$kode] ?? [],
                'struktur_baja' => $relations['baja'][$kode] ?? [],
                'struktur_beton' => $relations['beton'][$kode] ?? [],
                'struktur_bawah' => $relations['bawah'][$kode] ?? [],
                'pelindung' => $relations['pelindung'][$kode] ?? [],
                'nilai_kondisi' => [
                    'atas' => $relations['nilai_atas'][$kode] ?? [],
                    'bawah' => $relations['nilai_bawah'][$kode] ?? [],
                    'pelindung' => $relations['nilai_pelindung'][$kode] ?? [],
                    'total' => $relations['nilai_total'][$kode][0] ?? null,
                ],
                'survey_terakhir' => $relations['survey'][$kode][0] ?? null,
                'perawatan_terakhir' => $relations['perawatan'][$kode][0] ?? null,
            ];
        }, $batch['data']);

        return $batch;
    }

    public function changed(array $filters): array
    {
        return $this->batch([
            ...$filters,
            'updated_since' => $filters['since'] ?? null,
            'limit' => $this->limit($filters['limit'] ?? null, 500, 1000),
        ]);
    }

    private function loadRelations(array $codes): array
    {
        $map = [
            'profil' => ['m_jembatan_profil', 'id_jembatan'],
            'bentang' => ['m_jembatan_bentang', 'id_jembatan'],
            'baja' => ['m_jembatan_baja', 'id_jembatan'],
            'beton' => ['m_jembatan_beton', 'id_jembatan'],
            'bawah' => ['m_jembatan_bawah', 'id_jembatan'],
            'pelindung' => ['m_jembatan_detil_3', 'id_jembatan'],
            'nilai_atas' => ['m_jembatan_nilai_atas', 'id_jembatan'],
            'nilai_bawah' => ['m_jembatan_nilai_bawah', 'id_jembatan'],
            'nilai_pelindung' => ['m_jembatan_nilai_pelindung', 'id_jembatan'],
            'nilai_total' => ['m_jembatan_nilai_total', 'id_jembatan'],
            'survey' => ['m_jembatan_survey', 'kode_jembatan'],
            'perawatan' => ['m_jembatan_perawatan', 'kode_jembatan'],
        ];
        $relations = [];

        foreach ($map as $key => [$table, $foreignKey]) {
            $query = DB::connection($this->connectionName())
                ->table($table)
                ->whereIn($foreignKey, $codes)
                ->orderBy($foreignKey);

            foreach (['tanggal', 'urut', 'id'] as $column) {
                if ($this->tableHasColumn($table, $column)) {
                    $query->orderBy($column, $column === 'tanggal' ? 'desc' : 'asc');
                }
            }

            $relations[$key] = $query
                ->get()
                ->map(fn (object $row): array => (array) $row)
                ->groupBy(fn (array $row): string => (string) $row[$foreignKey])
                ->map(fn ($rows): array => $rows->values()->all())
                ->all();
        }

        return $relations;
    }
}
