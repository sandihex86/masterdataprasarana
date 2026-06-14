<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private string $connectionName = 'warehouse';

    public function up(): void
    {
        if (! Schema::connection($this->connectionName)->hasTable('m_gudang')) {
            return;
        }

        if (! Schema::connection($this->connectionName)->hasColumn('m_gudang', 'id_gudang')) {
            Schema::connection($this->connectionName)->table('m_gudang', function (Blueprint $table): void {
                $table->char('id_gudang', 26)->nullable()->after('id');
            });
        }

        DB::connection($this->connectionName)
            ->table('m_gudang')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $kodeGudang = trim((string) ($row->kode_gudang ?? ''));

                    if ($kodeGudang === '') {
                        $kodeGudang = (string) Str::ulid();
                    }

                    DB::connection($this->connectionName)
                        ->table('m_gudang')
                        ->where('id', $row->id)
                        ->update([
                            'id_gudang' => $kodeGudang,
                            'kode_gudang' => $kodeGudang,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::connection($this->connectionName)->hasTable('m_gudang')) {
            return;
        }

        if (Schema::connection($this->connectionName)->hasColumn('m_gudang', 'id_gudang')) {
            Schema::connection($this->connectionName)->table('m_gudang', function (Blueprint $table): void {
                $table->dropColumn('id_gudang');
            });
        }
    }
};
