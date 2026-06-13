<?php

namespace App\Services\MasterData;

use App\Models\MasterData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BridgeQueryService
{
    /**
     * @var array<string, string>
     */
    private array $sortMap = [
        'code' => 'code',
        'name' => 'name',
        'status' => 'status',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];

    public function paginate(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('per_page', config('master-data.pagination.default_per_page')), 1),
            config('master-data.pagination.max_per_page'),
        );

        $query = MasterData::query()->where('entity_type', 'bridge');
        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request->string('sort')->toString());

        return $query->paginate($perPage)->withQueryString();
    }

    public function applyFilters(Builder $query, Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));

        return $query
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')->toString()))
            ->when($request->filled('code'), fn (Builder $builder) => $builder->where('code', $request->string('code')->toString()))
            ->when($request->filled('bridge_number'), fn (Builder $builder) => $builder->where('data->bridge_number', $request->string('bridge_number')->toString()))
            ->when($request->filled('bridge_kind'), fn (Builder $builder) => $builder->where('data->bridge_kind', $request->string('bridge_kind')->toString()))
            ->when($request->filled('province_code'), fn (Builder $builder) => $builder->where('data->province_code', $request->string('province_code')->toString()))
            ->when($request->filled('city_code'), fn (Builder $builder) => $builder->where('data->city_code', $request->string('city_code')->toString()))
            ->when($request->filled('operational_area_code'), fn (Builder $builder) => $builder->where('data->operational_area_code', $request->string('operational_area_code')->toString()))
            ->when($request->filled('lintas_code'), fn (Builder $builder) => $builder->where('data->lintas_code', $request->string('lintas_code')->toString()))
            ->when($request->filled('station_start_code'), fn (Builder $builder) => $builder->where('data->station_start_code', $request->string('station_start_code')->toString()))
            ->when($request->filled('station_end_code'), fn (Builder $builder) => $builder->where('data->station_end_code', $request->string('station_end_code')->toString()))
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('data->bridge_number', 'like', "%{$search}%")
                        ->orWhere('data->km_hm', 'like', "%{$search}%")
                        ->orWhere('data->station_start_name', 'like', "%{$search}%")
                        ->orWhere('data->station_end_name', 'like', "%{$search}%");
                });
            });
    }

    public function applySorting(Builder $query, string $sort): Builder
    {
        $sort = $sort !== '' ? $sort : '-updated_at';
        $descending = str_starts_with($sort, '-');
        $requestedColumn = ltrim($sort, '-');

        if (! isset($this->sortMap[$requestedColumn])) {
            throw ValidationException::withMessages([
                'sort' => ['Kolom sorting tidak diizinkan untuk modul bridge.'],
            ]);
        }

        return $query->orderBy($this->sortMap[$requestedColumn], $descending ? 'desc' : 'asc');
    }
}
