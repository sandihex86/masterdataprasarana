<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ReferenceSourceSeeder extends Seeder
{
    private string $connectionName = 'reference';

    /**
     * @var array<string, array{file: string, unique_by: array<int, string>, numeric?: array<int, string>, boolean?: array<int, string>, ulid_id?: bool}>
     */
    private array $tables = [
        'm_prasarana' => [
            'file' => 'm_prasarana.csv',
            'unique_by' => ['kode_prasarana'],
            'boolean' => ['active'],
            'ulid_id' => true,
        ],
        'm_lintas' => [
            'file' => 'm_lintas.csv',
            'unique_by' => ['kode_lintas'],
            'numeric' => ['panjang_km'],
            'boolean' => ['active'],
            'ulid_id' => true,
        ],
        'm_stasiun' => [
            'file' => 'm_stasiun.csv',
            'unique_by' => ['id'],
            'numeric' => ['lat', 'long'],
            'ulid_id' => true,
        ],
        'm_wilker' => [
            'file' => 'm_wilker.csv',
            'unique_by' => ['kode_prasarana'],
            'boolean' => ['active'],
            'ulid_id' => true,
        ],
        'kabupaten_kota' => [
            'file' => 'kabupaten_kota.csv',
            'unique_by' => ['id'],
        ],
        'kelurahan' => [
            'file' => 'kelurahan.csv',
            'unique_by' => ['id'],
        ],
        'kecamatan' => [
            'file' => 'kecamatan.csv',
            'unique_by' => ['id'],
        ],
        'provinsi' => [
            'file' => 'provinsi.csv',
            'unique_by' => ['id'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->tables as $table => $config) {
            if (! Schema::connection($this->connectionName)->hasTable($table)) {
                continue;
            }

            $this->seedTable($table, $config);
        }
    }

    /**
     * @param  array{file: string, unique_by: array<int, string>, numeric?: array<int, string>, boolean?: array<int, string>, ulid_id?: bool}  $config
     */
    private function seedTable(string $table, array $config): void
    {
        $path = database_path('data/'.$config['file']);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => ['File database/data/'.$config['file'].' tidak dapat dibaca.'],
            ]);
        }

        try {
            $headers = fgetcsv($handle);

            if (! is_array($headers) || $headers === []) {
                throw ValidationException::withMessages([
                    'file' => ['Header CSV '.$config['file'].' tidak ditemukan.'],
                ]);
            }

            $headers = array_map(
                fn (string $header): string => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header),
                $headers,
            );

            $batch = [];
            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($this->csvRowIsEmpty($row)) {
                    continue;
                }

                $payload = $this->payloadFromRow($headers, $row, $config);
                $this->fillUlidId($table, $payload, $config, $rowNumber);

                if (! $this->hasRequiredSeedIdentity($payload, $config['unique_by'])) {
                    continue;
                }

                $batch[] = [
                    ...$payload,
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= 1000) {
                    $this->flush($table, $batch, $config['unique_by']);
                    $batch = [];
                }
            }

            $this->flush($table, $batch, $config['unique_by']);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string|null>  $row
     * @param  array{file: string, unique_by: array<int, string>, numeric?: array<int, string>, boolean?: array<int, string>, ulid_id?: bool}  $config
     * @return array<string, mixed>
     */
    private function payloadFromRow(array $headers, array $row, array $config): array
    {
        $payload = [];
        $numericColumns = array_fill_keys($config['numeric'] ?? [], true);
        $booleanColumns = array_fill_keys($config['boolean'] ?? [], true);

        foreach ($headers as $index => $header) {
            $value = trim((string) ($row[$index] ?? ''));

            if ($value === '') {
                $payload[$header] = null;
                continue;
            }

            if (isset($numericColumns[$header])) {
                $payload[$header] = $this->normalizeDecimal($value);
                continue;
            }

            if (isset($booleanColumns[$header])) {
                $payload[$header] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (int) $value;
                continue;
            }

            $payload[$header] = $value;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array{file: string, unique_by: array<int, string>, numeric?: array<int, string>, boolean?: array<int, string>, ulid_id?: bool}  $config
     */
    private function fillUlidId(string $table, array &$payload, array $config, int $rowNumber): void
    {
        if (($config['ulid_id'] ?? false) !== true || filled($payload['id'] ?? null)) {
            return;
        }

        $payload['id'] = $this->deterministicUlid($table, $rowNumber);
    }

    private function deterministicUlid(string $table, int $rowNumber): string
    {
        $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $hash = hash('sha256', $table.'|'.$rowNumber, true);
        $suffix = '';

        for ($index = 0; $index < 16; $index++) {
            $suffix .= $alphabet[ord($hash[$index]) % 32];
        }

        return '01JREFEREN'.$suffix;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $uniqueBy
     */
    private function hasRequiredSeedIdentity(array $payload, array $uniqueBy): bool
    {
        foreach ($uniqueBy as $column) {
            if (($payload[$column] ?? null) === null || $payload[$column] === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $batch
     * @param  array<int, string>  $uniqueBy
     */
    private function flush(string $table, array $batch, array $uniqueBy): void
    {
        if ($batch === []) {
            return;
        }

        $connection = DB::connection($this->connectionName);

        if ($connection->getDriverName() === 'sqlite') {
            $connection->table($table)->insert($batch);

            return;
        }

        foreach ($batch as $payload) {
            $identity = [];

            foreach ($uniqueBy as $column) {
                $identity[$column] = $payload[$column] ?? null;
            }

            $connection->table($table)->updateOrInsert($identity, $payload);
        }
    }

    private function normalizeDecimal(string $value): ?string
    {
        $normalized = str_replace(',', '.', $value);

        return is_numeric($normalized) ? $normalized : null;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function csvRowIsEmpty(array $row): bool
    {
        return collect($row)->every(fn (mixed $value): bool => trim((string) $value) === '');
    }
}
