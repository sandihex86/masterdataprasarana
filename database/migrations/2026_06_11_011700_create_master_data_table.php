<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_data', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('source_system', 100)->nullable()->index();
            $table->string('source_table', 100)->nullable()->index();
            $table->string('source_id', 191)->nullable()->index();
            $table->string('entity_type', 100)->index();
            $table->string('code', 191)->index();
            $table->string('name', 191)->nullable()->index();
            $table->string('parent_code', 191)->nullable()->index();
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->json('metadata')->nullable();
            $table->char('checksum', 64)->nullable()->index();
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 50)->default('active')->index();
            $table->timestamp('synced_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['entity_type', 'status']);
            $table->index(['entity_type', 'name']);
            $table->index(['source_system', 'source_table', 'source_id'], 'master_data_source_lookup_index');
            $table->index(['entity_type', 'code'], 'master_data_entity_code_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data');
    }
};
