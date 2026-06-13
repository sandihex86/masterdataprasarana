<?php

namespace App\Services;

use App\Models\Tunnel;
use App\Models\TunnelDoc;
use App\Models\TunnelSpec;
use App\Models\TunnelStructure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TunnelService
{
    /**
     * @var array<int, string>
     */
    private const TUNNEL_FIELDS = [
        'kode_aset',
        'nomor_bh',
        'nama_terowongan',
        'id_wilayah_kerja',
        'id_lintas',
        'km_hm',
        'panjang_m',
        'tahun_bangunan',
        'tahun_operasi',
        'umur_tahun',
        'lat',
        'long',
        'status_operasi',
        'status_aset',
        'kondisi_terakhir',
        'tgl_inspeksi_terakhir',
    ];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1), 100);
        $sortBy = (string) ($filters['sort_by'] ?? 'updated_at');
        $sortDir = (string) ($filters['sort_dir'] ?? 'desc');

        return $this->filteredQuery($filters)
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    public function find(string $tunnelId): Tunnel
    {
        return Tunnel::query()
            ->with(['structure', 'specs', 'docs'])
            ->where('tunnel_id', $tunnelId)
            ->firstOrFail();
    }

    public function create(array $payload): Tunnel
    {
        return DB::connection('tunnel')->transaction(function () use ($payload): Tunnel {
            $tunnel = Tunnel::query()->create($this->onlyTunnelFields($payload));
            $this->syncNestedDetails($tunnel, $payload);

            return $this->find($tunnel->tunnel_id);
        });
    }

    public function update(string $tunnelId, array $payload): Tunnel
    {
        return DB::connection('tunnel')->transaction(function () use ($tunnelId, $payload): Tunnel {
            $tunnel = $this->find($tunnelId);
            $fields = $this->onlyTunnelFields($payload);

            if ($fields !== []) {
                $tunnel->fill($fields)->save();
            }

            $this->syncNestedDetails($tunnel, $payload);

            return $this->find($tunnelId);
        });
    }

    public function delete(string $tunnelId): void
    {
        $this->find($tunnelId)->delete();
    }

    public function structure(string $tunnelId): ?TunnelStructure
    {
        return $this->find($tunnelId)->structure;
    }

    public function upsertStructure(string $tunnelId, array $payload): TunnelStructure
    {
        $tunnel = $this->find($tunnelId);

        return $this->upsertRelation($tunnel, TunnelStructure::class, $payload);
    }

    public function specs(string $tunnelId): ?TunnelSpec
    {
        return $this->find($tunnelId)->specs;
    }

    public function upsertSpecs(string $tunnelId, array $payload): TunnelSpec
    {
        $tunnel = $this->find($tunnelId);

        return $this->upsertRelation($tunnel, TunnelSpec::class, $payload);
    }

    public function docs(string $tunnelId): ?TunnelDoc
    {
        return $this->find($tunnelId)->docs;
    }

    public function upsertDocs(string $tunnelId, array $payload): TunnelDoc
    {
        $tunnel = $this->find($tunnelId);

        return $this->upsertRelation($tunnel, TunnelDoc::class, $payload);
    }

    private function filteredQuery(array $filters): Builder
    {
        $query = Tunnel::query();
        $search = trim((string) ($filters['search'] ?? ''));

        foreach ([
            'id_wilayah_kerja',
            'id_lintas',
            'status_operasi',
            'status_aset',
            'kondisi_terakhir',
        ] as $filter) {
            if (array_key_exists($filter, $filters) && $filters[$filter] !== null && $filters[$filter] !== '') {
                $query->where($filter, $filters[$filter]);
            }
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('nama_terowongan', 'like', '%'.$search.'%')
                    ->orWhere('kode_aset', 'like', '%'.$search.'%')
                    ->orWhere('nomor_bh', 'like', '%'.$search.'%')
                    ->orWhere('km_hm', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function onlyTunnelFields(array $payload): array
    {
        return collect($payload)
            ->only(self::TUNNEL_FIELDS)
            ->all();
    }

    private function syncNestedDetails(Tunnel $tunnel, array $payload): void
    {
        if (array_key_exists('structure', $payload) && is_array($payload['structure'])) {
            $this->upsertRelation($tunnel, TunnelStructure::class, $payload['structure']);
        }

        if (array_key_exists('specs', $payload) && is_array($payload['specs'])) {
            $this->upsertRelation($tunnel, TunnelSpec::class, $payload['specs']);
        }

        if (array_key_exists('docs', $payload) && is_array($payload['docs'])) {
            $this->upsertRelation($tunnel, TunnelDoc::class, $payload['docs']);
        }
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TModel>  $modelClass
     * @return TModel
     */
    private function upsertRelation(Tunnel $tunnel, string $modelClass, array $payload)
    {
        unset($payload['tunnel_id'], $payload['id']);

        if ($payload === []) {
            throw ValidationException::withMessages([
                'payload' => ['Payload detail tunnel tidak boleh kosong.'],
            ]);
        }

        /** @var TModel $model */
        $model = $modelClass::query()->updateOrCreate(
            ['tunnel_id' => $tunnel->tunnel_id],
            $payload,
        );

        return $model;
    }
}
