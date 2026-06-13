<?php

namespace App\Support\Infrastructure;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InfrastructureBootstrapper
{
    public function connectionConfigured(string $connection): bool
    {
        $database = config('database.connections.'.$connection.'.database');

        return is_string($database) && $database !== '';
    }

    public function bootstrapReferenceConnection(string $connection = 'reference'): void
    {
        if (! $this->connectionConfigured($connection)) {
            return;
        }

        $schema = Schema::connection($connection);

        if (! $schema->hasTable('ref_provinces')) {
            $schema->create('ref_provinces', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('code', 32)->unique();
                $table->string('name', 191);
                $table->string('legacy_uniqid', 64)->nullable()->index();
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('source_id', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('ref_cities')) {
            $schema->create('ref_cities', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('province_code', 32)->index();
                $table->string('code', 32)->unique();
                $table->string('name', 191);
                $table->string('legacy_uniqid', 64)->nullable()->index();
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('source_id', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('ref_work_areas')) {
            $schema->create('ref_work_areas', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('code', 64)->nullable()->index();
                $table->string('name', 191);
                $table->string('legacy_uniqid', 64)->nullable()->index();
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('source_id', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('ref_operational_areas')) {
            $schema->create('ref_operational_areas', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('code', 64)->unique();
                $table->string('name', 191);
                $table->string('legacy_uniqid', 64)->nullable()->index();
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('source_id', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('ref_rail_lines')) {
            $schema->create('ref_rail_lines', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('code', 64)->unique();
                $table->string('name', 191);
                $table->string('legacy_uniqid', 64)->nullable()->index();
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('source_id', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('ref_stations')) {
            $schema->create('ref_stations', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('province_code', 32)->nullable()->index();
                $table->string('city_code', 32)->nullable()->index();
                $table->string('operational_area_code', 64)->nullable()->index();
                $table->string('code', 64)->index();
                $table->string('name', 191)->index();
                $table->string('alias', 191)->nullable();
                $table->decimal('latitude', 12, 8)->nullable();
                $table->decimal('longitude', 12, 8)->nullable();
                $table->string('legacy_uniqid', 64)->nullable()->index();
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('source_id', 128)->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['code', 'name'], 'ref_stations_code_name_unique');
            });
        }

        if (! $schema->hasTable('reference_sync_logs')) {
            $schema->create('reference_sync_logs', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('scope', 64);
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('status', 32)->default('pending')->index();
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('processed_rows')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function bootstrapOperationalDomainConnection(string $connection): void
    {
        if (! $this->connectionConfigured($connection)) {
            return;
        }

        $schema = Schema::connection($connection);

        if (! $schema->hasTable('asset_sources')) {
            $schema->create('asset_sources', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('asset_key', 64)->unique();
                $table->string('asset_name', 191);
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('source_identifier', 128)->nullable();
                $table->string('lifecycle_status', 32)->default('draft')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('sync_runs')) {
            $schema->create('sync_runs', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('scope', 64);
                $table->string('source_system', 64)->nullable();
                $table->string('source_table', 64)->nullable();
                $table->string('status', 32)->default('pending')->index();
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('processed_rows')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('projection_checkpoints')) {
            $schema->create('projection_checkpoints', function (Blueprint $table): void {
                $table->id();
                $table->string('projector', 128)->unique();
                $table->string('checkpoint_value', 191)->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function bootstrapReportingConnection(string $connection = 'reporting'): void
    {
        if (! $this->connectionConfigured($connection)) {
            return;
        }

        $schema = Schema::connection($connection);

        if (! $schema->hasTable('reporting_snapshots')) {
            $schema->create('reporting_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('snapshot_key', 128)->unique();
                $table->string('domain', 64)->index();
                $table->string('entity_type', 64)->nullable()->index();
                $table->string('status', 32)->default('draft')->index();
                $table->json('payload');
                $table->timestamp('captured_at')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('projection_checkpoints')) {
            $schema->create('projection_checkpoints', function (Blueprint $table): void {
                $table->id();
                $table->string('projector', 128)->unique();
                $table->string('checkpoint_value', 191)->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }
}
