<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TunnelLookupSeeder extends Seeder
{
    private string $connectionName = 'tunnel';

    public function run(): void
    {
        $this->seedLookup('m_tunnel_lookup_wilayah_kerja', [
            ['kode' => '-', 'nama' => 'BTP Kelas II Palembang'],
            ['kode' => '-', 'nama' => 'BTP Kelas II Padang'],
            ['kode' => '-', 'nama' => 'BTP Kelas I Medan'],
            ['kode' => '-', 'nama' => 'BTP Kelas I Surabaya'],
            ['kode' => '-', 'nama' => 'BTP Kelas I Semarang'],
            ['kode' => '-', 'nama' => 'BTP Kelas I Bandung'],
            ['kode' => '-', 'nama' => 'BTP Kelas I Jakarta'],
        ]);

        $this->seedLookup('m_tunnel_lookup_lintas', [
            ['kode' => 'Kmlp - Klg', 'nama' => 'Kamalpier - Kalianget'],
            ['kode' => 'Kmlp - Pm', 'nama' => 'Kamalpier - Pamekasan'],
            ['kode' => 'Mlj - Dpt', 'nama' => 'Malang Jagalan - Dampit'],
            ['kode' => 'Uj - Kap', 'nama' => 'Ujung - Karang Pilang'],
            ['kode' => 'Wrs - Knn', 'nama' => 'Wirosari - Kradenan'],
            ['kode' => 'Jm - Kd', 'nama' => 'Jombang - Kediri'],
            ['kode' => 'Kud-Bkn', 'nama' => 'KUDUS - BAKALAN'],
            ['kode' => 'My - Wlh', 'nama' => 'Mayong - Welahan'],
            ['kode' => 'Kln - Kbd', 'nama' => 'Kaliwungu - Kalibodri'],
            ['kode' => 'Tg - Ppk', 'nama' => 'Tegal - Prupuk'],
        ]);

        $this->seedLookup('m_tunnel_lookup_wilayah_operasi', [
            ['kode' => '1', 'nama' => 'DAOP I'],
            ['kode' => '2', 'nama' => 'DAOP II'],
            ['kode' => '3', 'nama' => 'DAOP III'],
            ['kode' => '4', 'nama' => 'DAOP IV'],
            ['kode' => '5', 'nama' => 'DAOP V'],
            ['kode' => '6', 'nama' => 'DAOP VI'],
            ['kode' => '7', 'nama' => 'DAOP VII'],
            ['kode' => '8', 'nama' => 'DAOP VIII'],
        ]);
    }

    /**
     * @param  array<int, array{kode: string, nama: string}>  $rows
     */
    private function seedLookup(string $table, array $rows): void
    {
        if (! Schema::connection($this->connectionName)->hasTable($table)) {
            return;
        }

        $timestamp = now();

        foreach ($rows as $index => $row) {
            $existingId = DB::connection($this->connectionName)
                ->table($table)
                ->where('kode', $row['kode'])
                ->where('nama', $row['nama'])
                ->value('id');

            DB::connection($this->connectionName)->table($table)->updateOrInsert(
                [
                    'kode' => $row['kode'],
                    'nama' => $row['nama'],
                ],
                [
                    'id' => $existingId ?: (string) Str::ulid(),
                    'active' => 1,
                    'sort_order' => $index + 1,
                    'source_system' => 'attachment_seed',
                    'updated_by' => '-',
                    'catatan' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            );
        }
    }
}
