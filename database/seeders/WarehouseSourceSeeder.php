<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WarehouseSourceSeeder extends Seeder
{
    private string $connectionName = 'warehouse';

    private string $tableName = 'm_gudang';

    public function run(): void
    {
        if (! Schema::connection($this->connectionName)->hasTable($this->tableName)) {
            return;
        }

        $path = database_path('data/m_gudang.csv');
        $rows = $this->csvRows($path);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => ['File database/data/m_gudang.csv tidak dapat dibaca.'],
            ]);
        }

        $headers = array_shift($rows);

        if (! is_array($headers) || $headers === []) {
            throw ValidationException::withMessages([
                'file' => ['Header CSV gudang tidak ditemukan.'],
            ]);
        }

        $headers = array_map(
            fn (string $header): string => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header),
            $headers,
        );

        foreach ($rows as $row) {
            if ($this->csvRowIsEmpty($row)) {
                continue;
            }

            $payload = $this->payloadFromRow($headers, $row);

            if (($payload['nama_gudang'] ?? null) === null) {
                continue;
            }

            $existingKodeGudang = DB::connection($this->connectionName)
                ->table($this->tableName)
                ->where('nama_gudang', $payload['nama_gudang'])
                ->where('tipe_gudang', $payload['tipe_gudang'])
                ->value('kode_gudang');

            if (filled($existingKodeGudang)) {
                $payload['id_gudang'] = (string) $existingKodeGudang;
                $payload['kode_gudang'] = (string) $existingKodeGudang;
            }

            DB::connection($this->connectionName)
                ->table($this->tableName)
                ->updateOrInsert(
                    [
                        'nama_gudang' => $payload['nama_gudang'],
                        'tipe_gudang' => $payload['tipe_gudang'],
                    ],
                    [
                        ...$payload,
                        'updated_at' => now(),
                        'created_at' => now(),
                        'deleted_at' => null,
                    ],
                );
        }
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function csvRows(string $path): array
    {
        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\n|\r/', $contents) ?: [])
            ->filter(fn (string $line): bool => trim($line) !== '')
            ->map(fn (string $line): array => str_getcsv($line))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>
     */
    private function payloadFromRow(array $headers, array $row): array
    {
        $payload = [];

        foreach ($headers as $index => $header) {
            $value = trim((string) ($row[$index] ?? ''));
            $payload[$header] = $value === '' ? null : $value;
        }

        $kodeGudang = $payload['kode_gudang'] ?: $payload['id'] ?: (string) Str::ulid();

        return [
            'id_gudang' => $kodeGudang,
            'kode_gudang' => $kodeGudang,
            'nama_gudang' => $payload['nama_gudang'] ?? null,
            'tipe_gudang' => $payload['tipe_gudang'] ?? null,
            'id_wilker' => $payload['id_wilker'] ?? null,
            'id_prov' => $payload['id_prov'] ?? null,
            'id_kabkot' => $payload['id_kabkot'] ?? null,
            'lat' => $payload['lat'] ?? null,
            'long' => $payload['long'] ?? null,
            'active' => (int) ($payload['active'] ?? 1),
        ];
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function csvRowIsEmpty(array $row): bool
    {
        return collect($row)->every(fn (mixed $value): bool => trim((string) $value) === '');
    }
}
