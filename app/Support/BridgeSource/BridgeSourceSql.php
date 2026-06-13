<?php

namespace App\Support\BridgeSource;

use Illuminate\Support\Str;
use RuntimeException;

class BridgeSourceSql
{
    /**
     * @var array<int, string>
     */
    public const SOURCE_TABLES = [
        'm_jembatan',
        'm_jembatan_baja',
        'm_jembatan_bawah',
        'm_jembatan_bentang',
        'm_jembatan_beton',
        'm_jembatan_bu300922',
        'm_jembatan_bu_250922',
        'm_jembatan_detail',
        'm_jembatan_detil_3',
        'm_jembatan_nilai_atas',
        'm_jembatan_nilai_bawah',
        'm_jembatan_nilai_pelindung',
        'm_jembatan_nilai_review',
        'm_jembatan_nilai_total',
        'm_jembatan_perawatan',
        'm_jembatan_profil',
        'm_jembatan_survey',
        'm_jembatan_survey_baja',
        'm_jembatan_survey_baja1',
        'm_jembatan_survey_bawah',
        'm_jembatan_survey_bentang',
        'm_jembatan_survey_beton',
        'm_jembatan_survey_beton1',
        'm_jembatan_survey_detil_2',
        'm_jembatan_survey_detil_3',
        'm_jembatan_survey_nilai',
        'm_jembatan_survey_nilai_atas',
        'm_jembatan_survey_nilai_bawah',
        'm_jembatan_survey_nilai_pelindung',
        'm_jembatan_survey_nilai_review',
        'm_jembatan_survey_nilai_total',
        'm_jembatan_survey_profil',
        'm_jembatan_survey_review',
        'm_jembatan_survey_review_dokumen',
        'm_jembatan_survey_review_nilai_atas',
        'm_jembatan_survey_review_nilai_bawah',
        'm_jembatan_survey_review_nilai_pelindung',
        'm_jembatan_survey_review_nilai_total',
        'm_jembatan_survey_status',
        'm_kabkot',
        'm_lintas',
        'm_provinsi',
        'm_stasiun',
        'm_stasiun_280922_ori',
        'm_stasiun_bu300922',
        'm_stasiun_old',
        'm_stasiun_old2',
        'm_wilayah_kerja',
        'm_wilayah_operasi',
    ];

    /**
     * @var array<string, array{label: string, description: string}>
     */
    private const TABLE_METADATA = [
        'm_jembatan' => [
            'label' => 'Tabel Induk Jembatan',
            'description' => 'Data utama jembatan hasil survey source.',
        ],
        'm_jembatan_profil' => [
            'label' => 'Profil Jembatan',
            'description' => 'Profil struktur dan dimensi utama jembatan.',
        ],
        'm_jembatan_bentang' => [
            'label' => 'Bentang Jembatan',
            'description' => 'Daftar bentang per jembatan.',
        ],
        'm_jembatan_bawah' => [
            'label' => 'Struktur Bawah',
            'description' => 'Komponen struktur bawah per jembatan.',
        ],
        'm_jembatan_detil_3' => [
            'label' => 'Detail Pelindung',
            'description' => 'Informasi pelindung arus, pengarah arus, dan longsoran.',
        ],
        'm_jembatan_nilai_total' => [
            'label' => 'Nilai Total',
            'description' => 'Ringkasan nilai total asesmen jembatan.',
        ],
        'm_provinsi' => [
            'label' => 'Lookup Provinsi',
            'description' => 'Referensi provinsi pada source jembatan.',
        ],
        'm_kabkot' => [
            'label' => 'Lookup Kab/Kota',
            'description' => 'Referensi kabupaten/kota pada source jembatan.',
        ],
        'm_lintas' => [
            'label' => 'Lookup Lintas',
            'description' => 'Referensi lintas pada source jembatan.',
        ],
        'm_stasiun' => [
            'label' => 'Lookup Stasiun',
            'description' => 'Referensi stasiun awal dan akhir.',
        ],
        'm_wilayah_kerja' => [
            'label' => 'Lookup Wilayah Kerja',
            'description' => 'Referensi wilayah kerja pada source jembatan.',
        ],
        'm_wilayah_operasi' => [
            'label' => 'Lookup Wilayah Operasi',
            'description' => 'Referensi wilayah operasi pada source jembatan.',
        ],
    ];

    public function connectionName(): string
    {
        if (app()->runningUnitTests()) {
            return (string) config('database.default');
        }

        $configured = (string) config('master-data.bridge_source.connection', 'bridge');

        foreach ([$configured, 'bridge'] as $connection) {
            $database = config('database.connections.'.$connection.'.database');

            if (is_string($database) && $database !== '') {
                return $connection;
            }
        }

        return (string) config('database.default');
    }

    /**
     * @return array<int, string>
     */
    public function sourceTables(): array
    {
        return self::SOURCE_TABLES;
    }

    /**
     * @return array{label: string, description: string}
     */
    public function tableMeta(string $table): array
    {
        if (isset(self::TABLE_METADATA[$table])) {
            return self::TABLE_METADATA[$table];
        }

        $label = (string) Str::of($table)
            ->replaceFirst('m_', '')
            ->replace('_', ' ')
            ->title();

        $description = str_starts_with($table, 'm_jembatan')
            ? 'Tabel source modul jembatan pada database domain bridge.'
            : 'Tabel referensi source modul jembatan pada database domain bridge.';

        return [
            'label' => $label,
            'description' => $description,
        ];
    }

    /**
     * @param  array<int, string>|null  $tables
     * @return array<string, string>
     */
    public function createStatements(?array $tables = null): array
    {
        $path = $this->schemaPath();

        if (! is_file($path)) {
            throw new RuntimeException("Bridge source schema SQL not found at [{$path}].");
        }

        $contents = file_get_contents($path);

        if (! is_string($contents) || $contents === '') {
            throw new RuntimeException("Unable to read bridge source schema SQL at [{$path}].");
        }

        $allowed = array_fill_keys($tables ?? $this->sourceTables(), true);
        $statements = [];

        preg_match_all('/CREATE TABLE `([^`]+)` \((?:.|\R)*?\) ENGINE=[^;]+;/i', $contents, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $table = $match[1] ?? null;

            if (! is_string($table) || ! isset($allowed[$table])) {
                continue;
            }

            $statement = $match[0] ?? null;

            if (! is_string($statement)) {
                continue;
            }

            $statements[$table] = $this->sanitizeCreateStatement($statement);
        }

        return $statements;
    }

    /**
     * @param  callable(string, string): void  $consumer
     * @param  array<int, string>|null  $tables
     */
    public function streamInsertStatements(callable $consumer, ?array $tables = null): void
    {
        $path = $this->dataPath();

        if (! is_file($path)) {
            throw new RuntimeException("Bridge source data SQL not found at [{$path}].");
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open bridge source data SQL at [{$path}].");
        }

        $allowed = array_fill_keys($tables ?? $this->sourceTables(), true);
        $table = null;
        $statement = '';
        $collecting = false;

        try {
            while (($line = fgets($handle)) !== false) {
                if (! $collecting) {
                    if (! preg_match('/^INSERT INTO `([^`]+)`/i', $line, $matches)) {
                        continue;
                    }

                    $candidate = $matches[1] ?? null;

                    if (! is_string($candidate) || ! isset($allowed[$candidate])) {
                        continue;
                    }

                    $table = $candidate;
                    $collecting = true;
                    $statement = '';
                }

                $statement .= $line;

                if (! str_contains($line, ';')) {
                    continue;
                }

                if ($table !== null) {
                    $consumer($table, $this->sanitizeInsertStatement($statement));
                }

                $table = null;
                $statement = '';
                $collecting = false;
            }
        } finally {
            fclose($handle);
        }
    }

    public function schemaPath(): string
    {
        return base_path('database/struktur/struktur_jembatan.sql');
    }

    public function dataPath(): string
    {
        return base_path((string) config('master-data.bridge_source.dump_path', 'database/struktur/data_jembatan.sql'));
    }

    private function sanitizeCreateStatement(string $statement): string
    {
        $statement = preg_replace('/AUTO_INCREMENT=\d+\s*/i', '', $statement) ?? $statement;
        $statement = preg_replace("/\\b(timestamp|datetime)\\s+NOT NULL DEFAULT '0000-00-00 00:00:00'/i", '$1 NULL DEFAULT NULL', $statement) ?? $statement;
        $statement = preg_replace("/\\b(timestamp|datetime)\\s+NULL DEFAULT '0000-00-00 00:00:00'/i", '$1 NULL DEFAULT NULL', $statement) ?? $statement;
        $statement = preg_replace("/\\bdate\\s+NOT NULL DEFAULT '0000-00-00'/i", 'date NULL DEFAULT NULL', $statement) ?? $statement;
        $statement = preg_replace("/\\bdate\\s+DEFAULT '0000-00-00'/i", 'date DEFAULT NULL', $statement) ?? $statement;
        $statement = preg_replace('/DEFAULT CHARSET=(latin1|utf8)\b/i', 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', $statement) ?? $statement;

        return trim($statement);
    }

    private function sanitizeInsertStatement(string $statement): string
    {
        $statement = preg_replace("/'0000-00-00 00:00:00'/", 'NULL', $statement) ?? $statement;
        $statement = preg_replace("/'0000-00-00'/", 'NULL', $statement) ?? $statement;

        return trim($statement);
    }
}
