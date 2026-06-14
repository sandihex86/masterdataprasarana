<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WarehouseService
{
    /**
     * @var array<int, string>
     */
    private const SEARCH_COLUMNS = [
        'id_gudang',
        'kode_gudang',
        'nama_gudang',
        'tipe_gudang',
        'id_wilker',
        'id_prov',
        'id_kabkot',
    ];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? config('master-data.pagination.default_per_page')), 1), 100);
        $page = max((int) ($filters['page'] ?? 1), 1);
        $query = $this->baseQuery();
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                foreach (self::SEARCH_COLUMNS as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        foreach (['tipe_gudang', 'id_wilker', 'id_prov', 'id_kabkot'] as $column) {
            if (array_key_exists($column, $filters) && $filters[$column] !== null && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        if (array_key_exists('active', $filters) && $filters['active'] !== null && $filters['active'] !== '') {
            $query->where('active', $this->booleanFilter($filters['active']));
        }

        $total = (clone $query)->count();
        $sortBy = (string) (($filters['sort_by'] ?? null) ?: 'updated_at');
        $sortDir = (string) (($filters['sort_dir'] ?? null) ?: 'desc');

        $rows = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', 'desc')
            ->forPage($page, $perPage)
            ->get();

        return new Paginator($rows, $total, $perPage, $page, [
            'path' => request()->url(),
            'pageName' => 'page',
        ]);
    }

    public function find(string $idGudang): object
    {
        return $this->findRecord($idGudang);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: Collection<int, object>, meta: array<string, mixed>}
     */
    public function batch(array $filters = []): array
    {
        $query = $this->baseQuery();

        if (array_key_exists('active', $filters) && $filters['active'] !== null && $filters['active'] !== '') {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOL));
        }

        if (! empty($filters['updated_since'])) {
            $query->where('updated_at', '>=', $filters['updated_since']);
        }

        $records = $query
            ->orderBy('id')
            ->get();

        return [
            'data' => $records,
            'meta' => [
                'total' => $records->count(),
                'filters' => [
                    'active' => array_key_exists('active', $filters) && $filters['active'] !== null && $filters['active'] !== ''
                        ? $this->booleanFilter($filters['active'])
                        : null,
                    'updated_since' => $filters['updated_since'] ?? null,
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): object
    {
        $identity = (string) Str::ulid();
        $now = now();
        $data = [
            ...$payload,
            'id_gudang' => $identity,
            'kode_gudang' => $identity,
            'active' => array_key_exists('active', $payload) ? filter_var($payload['active'], FILTER_VALIDATE_BOOL) : true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::connection($this->connectionName())->table('m_gudang')->insert($data);

        return $this->findRecord($identity);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(string $idGudang, array $payload): object
    {
        $current = $this->findRecord($idGudang);
        $data = [
            ...$payload,
            'updated_at' => now(),
        ];

        if (array_key_exists('active', $data)) {
            $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOL);
        }

        DB::connection($this->connectionName())
            ->table('m_gudang')
            ->where('id', $current->id)
            ->update($data);

        return $this->findRecord($current->id_gudang);
    }

    public function delete(string $idGudang): void
    {
        $current = $this->findRecord($idGudang);

        DB::connection($this->connectionName())
            ->table('m_gudang')
            ->where('id', $current->id)
            ->update([
                'active' => false,
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function findRecord(string $idGudang): object
    {
        $record = $this->baseQuery()
            ->where(function (Builder $query) use ($idGudang): void {
                $query
                    ->where('id_gudang', $idGudang)
                    ->orWhere('kode_gudang', $idGudang);
            })
            ->first();

        if ($record === null) {
            throw new NotFoundHttpException('Data gudang tidak ditemukan.');
        }

        return $record;
    }

    private function baseQuery(): Builder
    {
        return DB::connection($this->connectionName())
            ->table('m_gudang')
            ->whereNull('deleted_at');
    }

    private function connectionName(): string
    {
        return 'warehouse';
    }

    private function booleanFilter(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
