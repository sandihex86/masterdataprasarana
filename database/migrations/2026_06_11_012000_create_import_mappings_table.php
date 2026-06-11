<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_mappings', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 191);
            $table->string('source_system', 100);
            $table->string('source_table', 100);
            $table->string('entity_type', 100);
            $table->unsignedInteger('version')->default(1);
            $table->json('mapping');
            $table->json('transformations')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source_system', 'source_table', 'entity_type'], 'import_mappings_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_mappings');
    }
};
