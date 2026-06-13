<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BridgeMaintenanceService extends BridgeService
{
    public function paginateForBridge(string $kodeJembatan, array $filters): ?LengthAwarePaginator
    {
        if ($this->findDetail($kodeJembatan) === null) {
            return null;
        }

        $limit = $this->limit($filters['limit'] ?? null, 50, 200);
        $page = max((int) ($filters['page'] ?? 1), 1);
        $query = DB::connection($this->connectionName())
            ->table('m_jembatan_perawatan')
            ->where('kode_jembatan', $kodeJembatan)
            ->when(! empty($filters['tanggal_mulai']), fn ($builder) => $builder->whereDate('tanggal', '>=', $filters['tanggal_mulai']))
            ->when(! empty($filters['tanggal_selesai']), fn ($builder) => $builder->whereDate('tanggal', '<=', $filters['tanggal_selesai']))
            ->when(! empty($filters['pemeriksa']), fn ($builder) => $builder->where('pemeriksa', 'like', '%'.$filters['pemeriksa'].'%'));
        $total = (clone $query)->count();
        $rows = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->forPage($page, $limit)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        return new Paginator($rows, $total, $limit, $page, [
            'path' => request()->url(),
            'pageName' => 'page',
        ]);
    }

    public function create(string $kodeJembatan, array $payload, mixed $actor = null): ?array
    {
        $bridge = $this->findDetail($kodeJembatan);

        if ($bridge === null) {
            return null;
        }

        $raw = $bridge['raw'];
        $actorName = (string) ($actor?->name ?? $actor?->email ?? 'api');
        $now = now();
        $insert = [
            'uniqid' => (string) Str::uuid(),
            'kode_jembatan' => $kodeJembatan,
            'tanggal' => $payload['tanggal'],
            'pemeriksa' => $payload['pemeriksa'] ?? null,
            'lat' => (string) ($payload['lat'] ?? $raw['lat'] ?? ''),
            'lon' => (string) ($payload['lon'] ?? $raw['lon'] ?? ''),
            'nama' => (string) ($raw['nama'] ?? ''),
            'lintas' => $raw['lintas'] ?? null,
            'stasiun1' => $raw['stasiun1'] ?? null,
            'stasiun2' => $raw['stasiun2'] ?? null,
            'no_bh' => $raw['no_bh'] ?? null,
            'arah_bh' => $raw['arah_bh'] ?? null,
            'jenis' => $raw['jenis'] ?? null,
            'km_hm' => $raw['km_hm'] ?? null,
            'dokumen' => $payload['dokumen'] ?? null,
            'catatan' => $payload['catatan'] ?? null,
            'iduser' => $actor?->id ?? null,
            'active' => array_key_exists('active', $payload) ? (int) (bool) $payload['active'] : 1,
            'created_by' => $actorName,
            'created_at' => $now,
            'updated_by' => $actorName,
            'updated_at' => $now,
        ];

        $id = DB::connection($this->connectionName())
            ->table('m_jembatan_perawatan')
            ->insertGetId($insert);

        $row = DB::connection($this->connectionName())
            ->table('m_jembatan_perawatan')
            ->where('id', $id)
            ->first();

        return $row instanceof \stdClass ? (array) $row : null;
    }
}
