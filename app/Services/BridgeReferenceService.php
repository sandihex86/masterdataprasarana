<?php

namespace App\Services;

use App\Support\BridgeSource\BridgeSourceSql;
use Illuminate\Support\Facades\DB;

class BridgeReferenceService
{
    private const TABLES = [
        'provinces' => 'm_provinsi',
        'cities' => 'm_kabkot',
        'operation-areas' => 'm_wilayah_operasi',
        'work-areas' => 'm_wilayah_kerja',
        'routes' => 'm_lintas',
        'stations' => 'm_stasiun',
        'segments' => 'm_petak',
    ];

    public function __construct(
        private readonly BridgeSourceSql $bridgeSourceSql,
    ) {}

    public function list(string $type, array $filters): ?array
    {
        $table = self::TABLES[$type] ?? null;

        if ($table === null) {
            return null;
        }

        $limit = min(max((int) ($filters['limit'] ?? 1000), 1), 2000);
        $keyword = trim((string) ($filters['keyword'] ?? ''));

        $connection = DB::connection($this->bridgeSourceSql->connectionName());
        $columns = $connection->getSchemaBuilder()->getColumnListing($table);
        $query = $connection
            ->table($table)
            ->when($keyword !== '', function ($builder) use ($keyword, $columns): void {
                $builder->where(function ($nested) use ($keyword, $columns): void {
                    foreach (['uniqid', 'nama', 'kode'] as $column) {
                        if (in_array($column, $columns, true)) {
                            $nested->orWhere($column, 'like', "%{$keyword}%");
                        }
                    }
                });
            })
            ->limit($limit);

        if (in_array('nama', $columns, true)) {
            $query->orderBy('nama');
        } elseif (in_array('id', $columns, true)) {
            $query->orderBy('id');
        }

        return $query
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();
    }
}
