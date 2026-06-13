<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $connectionName = 'tunnel';

    public function up(): void
    {
        Schema::connection($this->connectionName)->create('m_tunnels', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('tunnel_id', 26)->unique();
            $table->string('kode_aset', 50)->nullable()->unique();
            $table->string('nomor_bh', 50)->nullable();
            $table->string('nama_terowongan', 150);
            $table->string('id_wilayah_kerja', 50)->nullable()->index();
            $table->string('id_lintas', 50)->nullable()->index();
            $table->string('km_hm', 30)->nullable();
            $table->decimal('panjang_m', 10, 2)->nullable();
            $table->unsignedSmallInteger('tahun_bangunan')->nullable();
            $table->unsignedSmallInteger('tahun_operasi')->nullable();
            $table->unsignedSmallInteger('umur_tahun')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('long', 10, 7)->nullable();
            $table->string('status_operasi', 30)->nullable()->index();
            $table->string('status_aset', 30)->nullable()->index();
            $table->string('kondisi_terakhir', 50)->nullable();
            $table->date('tgl_inspeksi_terakhir')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_wilayah_kerja', 'id_lintas', 'status_operasi', 'status_aset'], 'm_tunnels_common_filter_index');
        });

        Schema::connection($this->connectionName)->create('m_tunnel_structures', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('tunnel_id', 26)->unique();
            $table->string('jenis_struktur', 100)->nullable();
            $table->string('material_struktur', 100)->nullable();
            $table->string('material_lining', 100)->nullable();
            $table->string('material_portal', 100)->nullable();
            $table->string('material_invert', 100)->nullable();
            $table->string('metode_konstruksi', 100)->nullable();
            $table->string('waterproofing', 100)->nullable();
            $table->unsignedSmallInteger('tahun_rehabilitasi_terakhir')->nullable();
            $table->timestamps();
            $table->foreign('tunnel_id')
                ->references('tunnel_id')
                ->on('m_tunnels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::connection($this->connectionName)->create('m_tunnel_specs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('tunnel_id', 26)->unique();
            $table->unsignedTinyInteger('jumlah_jalur')->nullable();
            $table->string('jenis_jalur', 50)->nullable();
            $table->decimal('gauge_m', 5, 3)->nullable();
            $table->decimal('lebar_bersih_m', 8, 2)->nullable();
            $table->decimal('tinggi_bersih_m', 8, 2)->nullable();
            $table->unsignedInteger('clearance_horizontal_mm')->nullable();
            $table->unsignedInteger('clearance_vertikal_mm')->nullable();
            $table->string('bentuk_penampang', 100)->nullable();
            $table->decimal('gradien_persen', 5, 2)->nullable();
            $table->decimal('radius_lengkung_m', 10, 2)->nullable();
            $table->text('catatan_teknis')->nullable();
            $table->timestamps();
            $table->foreign('tunnel_id')
                ->references('tunnel_id')
                ->on('m_tunnels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::connection($this->connectionName)->create('m_tunnel_docs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('tunnel_id', 26)->unique();
            $table->string('no_ded_bed_kajian_teknis', 100)->nullable();
            $table->json('ded_bed_kajian_teknis')->nullable();
            $table->string('no_spesifikasi_teknis', 100)->nullable();
            $table->json('spesifikasi_teknis')->nullable();
            $table->string('no_shop_drawing', 100)->nullable();
            $table->json('shop_drawing')->nullable();
            $table->string('no_as_built_drawing', 100)->nullable();
            $table->json('as_built_drawing')->nullable();
            $table->string('no_dok_hasil_uji', 100)->nullable();
            $table->json('dok_hasil_uji')->nullable();
            $table->timestamps();
            $table->foreign('tunnel_id')
                ->references('tunnel_id')
                ->on('m_tunnels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connectionName)->dropIfExists('m_tunnel_docs');
        Schema::connection($this->connectionName)->dropIfExists('m_tunnel_specs');
        Schema::connection($this->connectionName)->dropIfExists('m_tunnel_structures');
        Schema::connection($this->connectionName)->dropIfExists('m_tunnels');
    }
};
