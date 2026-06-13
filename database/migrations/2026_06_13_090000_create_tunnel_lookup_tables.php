<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $connectionName = 'tunnel';

    public function up(): void
    {
        $this->createLookupTable('m_tunnel_lookup_lintas');
        $this->createLookupTable('m_tunnel_lookup_wilayah_kerja');
        $this->createLookupTable('m_tunnel_lookup_wilayah_operasi');
    }

    public function down(): void
    {
        Schema::connection($this->connectionName)->dropIfExists('m_tunnel_lookup_wilayah_operasi');
        Schema::connection($this->connectionName)->dropIfExists('m_tunnel_lookup_wilayah_kerja');
        Schema::connection($this->connectionName)->dropIfExists('m_tunnel_lookup_lintas');
    }

    private function createLookupTable(string $tableName): void
    {
        Schema::connection($this->connectionName)->create($tableName, function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->string('kode', 50)->nullable()->index();
            $table->string('nama', 150)->index();
            $table->unsignedTinyInteger('active')->default(1)->index();
            $table->unsignedSmallInteger('sort_order')->nullable()->index();
            $table->string('source_system', 50)->default('seed');
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
