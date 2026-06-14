<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private string $connectionName = 'reference';

    /**
     * @var array<int, string>
     */
    private array $tables = [
        'm_prasarana',
        'm_lintas',
        'm_stasiun',
        'm_wilker',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::connection($this->connectionName)->hasTable($table)) {
                continue;
            }

            if (! Schema::connection($this->connectionName)->hasColumn($table, 'id')) {
                Schema::connection($this->connectionName)->table($table, function (Blueprint $blueprint): void {
                    $blueprint->char('id', 26)->nullable()->after('internal_id');
                });
            }

            DB::connection($this->connectionName)
                ->table($table)
                ->orderBy('internal_id')
                ->chunkById(500, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        $id = trim((string) ($row->id ?? ''));

                        if ($this->isUlid($id)) {
                            continue;
                        }

                        DB::connection($this->connectionName)
                            ->table($table)
                            ->where('internal_id', $row->internal_id)
                            ->update([
                                'id' => (string) Str::ulid(),
                                'updated_at' => now(),
                            ]);
                    }
                }, 'internal_id', 'internal_id');
        }
    }

    public function down(): void
    {
        // Reference ULIDs are part of the current schema, so rollback keeps them intact.
    }

    private function isUlid(string $value): bool
    {
        return preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $value) === 1;
    }
};
