<?php

use App\Support\Infrastructure\InfrastructureBootstrapper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        app(InfrastructureBootstrapper::class)->bootstrapReferenceConnection('reference');
    }

    public function down(): void
    {
        if (! app(InfrastructureBootstrapper::class)->connectionConfigured('reference')) {
            return;
        }

        $schema = Schema::connection('reference');

        foreach ([
            'reference_sync_logs',
            'ref_stations',
            'ref_rail_lines',
            'ref_operational_areas',
            'ref_work_areas',
            'ref_cities',
            'ref_provinces',
        ] as $table) {
            if ($schema->hasTable($table)) {
                $schema->drop($table);
            }
        }
    }
};
