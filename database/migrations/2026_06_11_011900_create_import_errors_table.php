<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_errors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedBigInteger('row_number')->nullable();
            $table->string('source_id', 191)->nullable()->index();
            $table->string('error_code', 100)->index();
            $table->string('field', 191)->nullable();
            $table->text('error_message');
            $table->json('raw_data')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index('import_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_errors');
    }
};
