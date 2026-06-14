<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $connectionName = 'reference';

    public function up(): void
    {
        $schema = Schema::connection($this->connectionName);

        if (! $schema->hasTable('m_prasarana')) {
            $schema->create('m_prasarana', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 64)->nullable();
                $table->string('kode_prasarana', 64)->nullable();
                $table->string('nama_prasarana', 191);
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('m_lintas')) {
            $schema->create('m_lintas', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 64)->nullable();
                $table->string('kode_lintas', 64)->nullable()->nullable();
                $table->string('nama_lintas', 191)->nullable();
                $table->string('stasiun_awal_id', 64)->nullable();
                $table->string('stasiun_akhir_id', 64)->nullable();
                $table->decimal('panjang_km', 12, 3)->nullable();
                $table->string('id_wilayah_kerja', 64)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('m_stasiun')) {
            $schema->create('m_stasiun', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 64)->nullable();
                $table->string('nama_stasiun', 191);
                $table->string('wilayah_operasi', 191)->nullable();
                $table->string('kecamatan', 191)->nullable();
                $table->string('zona', 64)->nullable();
                $table->string('x', 64)->nullable();
                $table->string('y', 64)->nullable();
                $table->string('z', 64)->nullable();
                $table->decimal('lat', 12, 8)->nullable();
                $table->decimal('long', 12, 8)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('m_wilker')) {
            $schema->create('m_wilker', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 64)->nullable();
                $table->string('kode_prasarana', 64)->nullable();
                $table->string('nama_prasarana', 191);
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('kabupaten_kota')) {
            $schema->create('kabupaten_kota', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 32)->nullable();
                $table->string('name', 191);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('kelurahan')) {
            $schema->create('kelurahan', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 32)->nullable();
                $table->string('name', 191);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('kecamatan')) {
            $schema->create('kecamatan', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 32)->nullable();
                $table->string('name', 191);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('provinsi')) {
            $schema->create('provinsi', function (Blueprint $table): void {
                $table->bigIncrements('internal_id');
                $table->string('id', 32)->nullable();
                $table->string('name', 191);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connectionName);

        foreach ([
            'kelurahan',
            'kecamatan',
            'kabupaten_kota',
            'provinsi',
            'm_wilker',
            'm_stasiun',
            'm_lintas',
            'm_prasarana',
        ] as $table) {
            $schema->dropIfExists($table);
        }
    }
};
