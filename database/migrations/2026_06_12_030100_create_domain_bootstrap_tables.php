<?php

use App\Support\Infrastructure\InfrastructureBootstrapper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $domainConnections = [
        'track',
        'operational_facility',
        'certificate',
        'warehouse',
    ];

    public function up(): void
    {
        foreach ($this->domainConnections as $connection) {
            app(InfrastructureBootstrapper::class)->bootstrapOperationalDomainConnection($connection);
        }
    }

    public function down(): void
    {
        foreach ($this->domainConnections as $connection) {
            if (! app(InfrastructureBootstrapper::class)->connectionConfigured($connection)) {
                continue;
            }

            $schema = Schema::connection($connection);

            foreach (['projection_checkpoints', 'sync_runs', 'asset_sources'] as $table) {
                if ($schema->hasTable($table)) {
                    $schema->drop($table);
                }
            }
        }
    }
};
