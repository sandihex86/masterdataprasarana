<?php

namespace App\Services\MasterData;

use App\Models\MasterData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MasterDataQueryService
{
    /**
     * @var array<string, string>
     */
    private array $sortMap = [
        'code' => 'code',
        'name' => 'name',
        'status' => 'status',
        'entity_type' => 'entity_type',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];

    public function paginate(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('per_page', config('master-data.pagination.default_per_page')), 1),
            config('master-data.pagination.max_per_page'),
        );

        $query = MasterData::query()->with('type');
        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request->string('sort')->toString());

        return $query->paginate($perPage)->withQueryString();
    }

    public function applyFilters(Builder $query, Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));

        return $query
            ->when($request->filled('type'), fn (Builder $builder) => $builder->where('entity_type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')->toString()))
            ->when($request->filled('code'), fn (Builder $builder) => $builder->where('code', $request->string('code')->toString()))
            ->when($request->filled('parent_code'), fn (Builder $builder) => $builder->where('parent_code', $request->string('parent_code')->toString()))
            ->when($request->filled('source_system'), fn (Builder $builder) => $builder->where('source_system', $request->string('source_system')->toString()))
            ->when($request->filled('source_table'), fn (Builder $builder) => $builder->where('source_table', $request->string('source_table')->toString()))
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });
    }

    public function applySorting(Builder $query, string $sort): Builder
    {
        $sort = $sort !== '' ? $sort : 'name';
        $descending = str_starts_with($sort, '-');
        $requestedColumn = ltrim($sort, '-');

        if (! isset($this->sortMap[$requestedColumn])) {
            throw ValidationException::withMessages([
                'sort' => ['Kolom sorting tidak diizinkan.'],
            ]);
        }

        return $query->orderBy($this->sortMap[$requestedColumn], $descending ? 'desc' : 'asc');
    }
}
