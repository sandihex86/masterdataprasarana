<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LegacyDatabaseService
{
    public function testConnection(?string $connection = null): bool
    {
        $connection ??= $this->defaultConnection();
        DB::connection($connection)->getPdo();

        return true;
    }

    public function listTables(?string $connection = null): array
    {
        $connection ??= $this->defaultConnection();

        return array_map(
            fn (object $table) => array_values((array) $table)[0],
            DB::connection($connection)->select('SHOW TABLES'),
        );
    }

    public function listColumns(string $table, ?string $connection = null): array
    {
        $connection ??= $this->defaultConnection();
        $this->assertAllowedTable($table, $connection);

        return Schema::connection($connection)->getColumnListing($table);
    }

    public function columnMetadata(string $table, ?string $connection = null): array
    {
        $connection ??= $this->defaultConnection();
        $this->assertAllowedTable($table, $connection);

        return Schema::connection($connection)->getColumns($table);
    }

    public function rowCount(string $table, ?string $connection = null): int
    {
        $connection ??= $this->defaultConnection();
        $this->assertAllowedTable($table, $connection);

        return DB::connection($connection)->table($table)->count();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sampleRows(string $table, int $limit = 10, ?string $connection = null): array
    {
        $connection ??= $this->defaultConnection();
        $this->assertAllowedTable($table, $connection);

        return DB::connection($connection)
            ->table($table)
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();
    }

    public function queryTable(string $table, ?string $connection = null): Builder
    {
        $connection ??= $this->defaultConnection();
        $this->assertAllowedTable($table, $connection);

        return DB::connection($connection)->table($table);
    }

    public function hasTable(string $table, ?string $connection = null): bool
    {
        $connection ??= $this->defaultConnection();

        return in_array($table, $this->listTables($connection), true);
    }

    private function assertAllowedTable(string $table, string $connection): void
    {
        if (! in_array($table, $this->listTables($connection), true)) {
            throw ValidationException::withMessages([
                'source_table' => ['Tabel source tidak ditemukan atau tidak diizinkan.'],
            ]);
        }
    }

    private function defaultConnection(): string
    {
        return (string) config('master-data.bridge_source.connection', 'bridge');
    }
}
