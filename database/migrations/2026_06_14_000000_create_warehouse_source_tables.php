<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $connectionName = 'warehouse';

    public function up(): void
    {
        Schema::connection($this->connectionName)->create('m_gudang', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('id_gudang', 26)->nullable();
            $table->string('kode_gudang', 50)->nullable();
            $table->string('nama_gudang', 191);
            $table->string('tipe_gudang', 100)->nullable();
            $table->string('id_wilker', 50)->nullable();
            $table->string('id_prov', 50)->nullable();
            $table->string('id_kabkot', 50)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('long', 10, 7)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connectionName)->dropIfExists('m_gudang');
    }
};
