<?php

use App\Support\Infrastructure\InfrastructureBootstrapper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        app(InfrastructureBootstrapper::class)->bootstrapReportingConnection('reporting');
    }

    public function down(): void
    {
        if (! app(InfrastructureBootstrapper::class)->connectionConfigured('reporting')) {
            return;
        }

        $schema = Schema::connection('reporting');

        foreach (['projection_checkpoints', 'reporting_snapshots'] as $table) {
            if ($schema->hasTable($table)) {
                $schema->drop($table);
            }
        }
    }
};
