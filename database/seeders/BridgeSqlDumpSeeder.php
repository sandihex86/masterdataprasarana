<?php

namespace Database\Seeders;

use App\Enums\MasterDataStatus;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Models\User;
use App\Services\Import\SqlDumpTableReader;
use App\Services\MasterData\MasterDataChecksumService;
use App\Support\MasterData\BridgeModuleDefinition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BridgeSqlDumpSeeder extends Seeder
{
    use WithoutModelEvents;

    public string $dumpPath = '';

    public function run(SqlDumpTableReader $reader, MasterDataChecksumService $checksumService): void
    {
        $actorId = $this->resolveActorId();
        $this->ensureBridgeType($actorId);

        $this->dumpPath = $this->dumpPath !== ''
            ? $this->dumpPath
            : (string) config('master-data.bridge_source.dump_path', 'database/data/data_jembatan.sql');

        $path = base_path($this->dumpPath);

        $bridges = $reader->readTable($path, 'm_jembatan');
        $profilesByBridge = $this->keyByField($reader->readTable($path, 'm_jembatan_profil'), 'id_jembatan');
        $spansByBridge = $this->groupByField($reader->readTable($path, 'm_jembatan_bentang'), 'id_jembatan');
        $substructuresByBridge = $this->groupByField($reader->readTable($path, 'm_jembatan_bawah'), 'id_jembatan');
        $protectionsByBridge = $this->keyByField($reader->readTable($path, 'm_jembatan_detil_3'), 'id_jembatan');
        $assessmentsByBridge = $this->keyByField($reader->readTable($path, 'm_jembatan_nilai_total'), 'id_jembatan');

        $provinces = $this->createReferenceLookup($reader->readTable($path, 'm_provinsi'), ['id', 'kode', 'uniqid']);
        $cities = $this->createReferenceLookup($reader->readTable($path, 'm_kabkot'), ['id', 'kode', 'uniqid']);
        $lintas = $this->createReferenceLookup($reader->readTable($path, 'm_lintas'), ['id', 'kode', 'uniqid', 'nama']);
        $stations = $this->createReferenceLookup($reader->readTable($path, 'm_stasiun'), ['id', 'kode', 'uniqid', 'nama']);
        $wilayahKerja = $this->createReferenceLookup($reader->readTable($path, 'm_wilayah_kerja'), ['id', 'kode', 'uniqid', 'nama']);
        $wilayahOperasi = $this->createReferenceLookup($reader->readTable($path, 'm_wilayah_operasi'), ['id', 'kode', 'uniqid', 'nama']);

        foreach ($bridges as $bridge) {
            $province = $this->lookupReference($provinces, $bridge['id_prov'] ?? null);
            $city = $this->lookupReference($cities, $bridge['id_kabkot'] ?? null);
            $lintasRow = $this->lookupReference($lintas, $bridge['lintas'] ?? null);
            $stationStart = $this->lookupReference($stations, $bridge['stasiun1'] ?? null);
            $stationEnd = $this->lookupReference($stations, $bridge['stasiun2'] ?? null);
            $wilkerRow = $this->lookupReference($wilayahKerja, $bridge['wil_ker'] ?? null);
            $wilopRow = $this->lookupReference($wilayahOperasi, $bridge['wil_op'] ?? null);

            $profile = $profilesByBridge[$bridge['uniqid']] ?? null;
            $protection = $protectionsByBridge[$bridge['uniqid']] ?? null;
            $assessment = $assessmentsByBridge[$bridge['uniqid']] ?? null;

            $data = [
                'inspection_date' => $this->normalizeString($bridge['tanggal'] ?? null),
                'wil_ker' => $this->normalizeString($bridge['wil_ker'] ?? null),
                'wil_ker_name' => $this->normalizeString($wilkerRow['nama'] ?? null),
                'province_code' => $this->normalizeString($province['kode'] ?? $bridge['id_prov'] ?? null),
                'province_name' => $this->normalizeString($province['nama'] ?? null),
                'city_code' => $this->normalizeString($city['kode'] ?? $bridge['id_kabkot'] ?? null),
                'city_name' => $this->normalizeString($city['nama'] ?? null),
                'operational_area_code' => $this->normalizeString($wilopRow['kode'] ?? $bridge['wil_op'] ?? null),
                'operational_area_name' => $this->normalizeString($wilopRow['nama'] ?? null),
                'latitude' => $this->normalizeCoordinate($bridge['lat'] ?? null, true),
                'longitude' => $this->normalizeCoordinate($bridge['lon'] ?? null, false),
                'legacy_latitude_raw' => $this->normalizeString($bridge['lat'] ?? null),
                'legacy_longitude_raw' => $this->normalizeString($bridge['lon'] ?? null),
                'lintas_code' => $this->normalizeString($lintasRow['kode'] ?? $bridge['lintas'] ?? null),
                'lintas_name' => $this->normalizeString($lintasRow['nama'] ?? null),
                'station_start_code' => $this->normalizeString($stationStart['kode'] ?? $bridge['stasiun1'] ?? null),
                'station_start_name' => $this->normalizeString($stationStart['nama'] ?? null),
                'station_end_code' => $this->normalizeString($stationEnd['kode'] ?? $bridge['stasiun2'] ?? null),
                'station_end_name' => $this->normalizeString($stationEnd['nama'] ?? null),
                'bridge_number' => $this->normalizeString($bridge['no_bh'] ?? null),
                'direction' => $this->normalizeString($bridge['arah_bh'] ?? null),
                'bridge_kind' => $this->normalizeString($bridge['jenis'] ?? null),
                'km_hm' => $this->normalizeString($bridge['km_hm'] ?? null),
                'photo_1' => $this->normalizeString($bridge['foto1'] ?? null),
                'photo_2' => $this->normalizeString($bridge['foto2'] ?? null),
                'photo_3' => $this->normalizeString($bridge['foto3'] ?? null),
                'photo_4' => $this->normalizeString($bridge['foto4'] ?? null),
                'caption_1' => $this->normalizeString($bridge['caption1'] ?? null),
                'caption_2' => $this->normalizeString($bridge['caption2'] ?? null),
                'caption_3' => $this->normalizeString($bridge['caption3'] ?? null),
                'caption_4' => $this->normalizeString($bridge['caption4'] ?? null),
                'document_path' => $this->normalizeString($bridge['dokumen'] ?? null),
                'video_path' => $this->normalizeString($bridge['video'] ?? null),
                'legacy_active' => $bridge['active'] ?? null,
                'legacy_status' => $bridge['status'] ?? null,
                'legacy_status_data' => $bridge['statusdata'] ?? null,
                'profile' => $this->buildProfilePayload($profile),
                'spans' => $this->buildSpanPayload($spansByBridge[$bridge['uniqid']] ?? []),
                'substructures' => $this->buildSubstructurePayload($substructuresByBridge[$bridge['uniqid']] ?? []),
                'assessment' => $this->buildAssessmentPayload($assessment, $protection),
            ];

            $status = (int) ($bridge['active'] ?? 0) === 1
                ? MasterDataStatus::Active
                : MasterDataStatus::Inactive;

            $payload = [
                'source_system' => 'legacy_jembatan',
                'source_table' => 'm_jembatan',
                'source_id' => (string) $bridge['uniqid'],
                'entity_type' => 'bridge',
                'code' => (string) $bridge['uniqid'],
                'name' => $this->resolveBridgeName($bridge, $stationStart, $stationEnd),
                'parent_code' => $this->normalizeString($province['kode'] ?? $bridge['id_prov'] ?? null),
                'description' => $this->resolveBridgeDescription($bridge, $lintasRow, $stationStart, $stationEnd),
                'data' => $data,
                'metadata' => [
                    'raw_source' => $this->dumpPath,
                    'legacy_database' => 'jembatan',
                    'mapping_version' => 1,
                    'legacy_tables' => [
                        'm_jembatan',
                        'm_jembatan_profil',
                        'm_jembatan_bentang',
                        'm_jembatan_bawah',
                        'm_jembatan_detil_3',
                        'm_jembatan_nilai_total',
                    ],
                ],
                'status' => $status->value,
            ];

            $checksum = $checksumService->generate($payload);

            $record = MasterData::query()->firstOrNew([
                'source_system' => 'legacy_jembatan',
                'source_table' => 'm_jembatan',
                'source_id' => (string) $bridge['uniqid'],
            ]);

            if (! $record->exists) {
                $record->uuid = (string) Str::uuid();
                $record->created_by = $actorId;
                $record->version = 1;
            } elseif ($record->checksum !== $checksum) {
                $record->version = $record->version + 1;
            }

            $record->fill([
                ...$payload,
                'checksum' => $checksum,
                'status' => $status,
                'synced_at' => now(),
                'updated_by' => $actorId,
            ]);

            $record->save();
        }
    }

    private function resolveActorId(): ?int
    {
        return User::query()
            ->where('is_admin', true)
            ->orderBy('id')
            ->value('id');
    }

    private function ensureBridgeType(?int $actorId): void
    {
        $type = MasterDataType::query()->firstOrNew(['code' => 'bridge']);

        if (! $type->exists) {
            $type->uuid = (string) Str::uuid();
            $type->created_by = $actorId;
        }

        $type->fill([
            'name' => 'Jembatan',
            'is_active' => true,
            'updated_by' => $actorId,
            ...BridgeModuleDefinition::typeAttributes(),
        ]);

        $type->save();
    }

    private function buildProfilePayload(?array $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'intersection' => $this->normalizeString($profile['perpotongan'] ?? null),
            'track_count' => $profile['jml_lintasan'] ?? null,
            'span_count' => $profile['jml_bentang'] ?? null,
            'span_length_1_m' => $this->normalizeString($profile['pjg_bentang1'] ?? null),
            'span_length_2_m' => $this->normalizeString($profile['pjg_bentang2'] ?? null),
            'span_length_3_m' => $this->normalizeString($profile['pjg_bentang3'] ?? null),
            'total_length_m' => $this->normalizeString($profile['pjg_total'] ?? null),
            'completed_year' => $this->normalizeString($profile['thn_selesai'] ?? null),
            'top_structure_height_m' => $this->normalizeString($profile['rm_bgn_atas'] ?? null),
            'bottom_structure_height_m' => $this->normalizeString($profile['rm_bgn_bawah'] ?? null),
        ];
    }

    private function buildSpanPayload(array $spans): array
    {
        usort($spans, fn (array $left, array $right) => (int) ($left['urut'] ?? 0) <=> (int) ($right['urut'] ?? 0));

        return array_values(array_map(fn (array $span) => [
            'order' => $span['urut'] ?? null,
            'length_m' => $this->normalizeString($span['pjg_bentang'] ?? null),
        ], $spans));
    }

    private function buildSubstructurePayload(array $substructures): array
    {
        usort($substructures, fn (array $left, array $right) => (int) ($left['urut'] ?? 0) <=> (int) ($right['urut'] ?? 0));

        return array_values(array_map(fn (array $substructure) => [
            'order' => $substructure['urut'] ?? null,
            'number' => $this->normalizeString($substructure['nomor'] ?? null),
            'material' => $this->normalizeString($substructure['material'] ?? null),
            'type' => $this->normalizeString($substructure['tipe'] ?? null),
            'manteling' => $this->normalizeString($substructure['manteling'] ?? null),
            'kind' => $this->normalizeString($substructure['jenis'] ?? null),
        ], $substructures));
    }

    private function buildAssessmentPayload(?array $assessment, ?array $protection): ?array
    {
        if ($assessment === null && $protection === null) {
            return null;
        }

        return [
            'protection' => $protection === null ? null : [
                'flow_guard_material' => $this->normalizeString($protection['pelindung_arus_material'] ?? null),
                'flow_guard_type' => $this->normalizeString($protection['pelindung_arus_tipe'] ?? null),
                'stream_guide_material' => $this->normalizeString($protection['pengarah_arus_material'] ?? null),
                'stream_guide_type' => $this->normalizeString($protection['pengarah_arus_tipe'] ?? null),
                'landslide_guard_material' => $this->normalizeString($protection['pelindung_longsoran_material'] ?? null),
                'landslide_guard_type' => $this->normalizeString($protection['pelindung_longsoran_tipe'] ?? null),
            ],
            'total_score' => $assessment['total'] ?? null,
            'conclusion' => $assessment['kesimpulan'] ?? null,
        ];
    }

    private function resolveBridgeName(array $bridge, ?array $stationStart, ?array $stationEnd): string
    {
        $explicitName = $this->normalizeString($bridge['nama'] ?? null);

        if ($explicitName !== null) {
            return $explicitName;
        }

        $bridgeNumber = $this->normalizeString($bridge['no_bh'] ?? null);
        $direction = $this->normalizeString($bridge['arah_bh'] ?? null);
        $stationPath = implode(' - ', array_filter([
            $this->normalizeString($stationStart['nama'] ?? null),
            $this->normalizeString($stationEnd['nama'] ?? null),
        ]));

        $name = implode(' ', array_filter([
            'Jembatan',
            $bridgeNumber !== null ? "BH {$bridgeNumber}" : null,
            $direction,
        ]));

        if ($stationPath !== '') {
            return trim("{$name} {$stationPath}");
        }

        return $name !== '' ? $name : 'Jembatan Legacy';
    }

    private function resolveBridgeDescription(array $bridge, ?array $lintasRow, ?array $stationStart, ?array $stationEnd): string
    {
        $note = $this->normalizeString(strip_tags((string) ($bridge['catatan'] ?? '')));

        if ($note !== null) {
            return $note;
        }

        $segments = array_filter([
            $this->normalizeString($lintasRow['nama'] ?? null),
            implode(' - ', array_filter([
                $this->normalizeString($stationStart['nama'] ?? null),
                $this->normalizeString($stationEnd['nama'] ?? null),
            ])),
            $this->normalizeString($bridge['km_hm'] ?? null),
        ]);

        if ($segments === []) {
            return 'Data jembatan hasil impor dari dump legacy DJKA.';
        }

        return 'Data jembatan hasil impor legacy: '.implode(' | ', $segments);
    }

    private function keyByField(array $rows, string $field): array
    {
        $keyed = [];

        foreach ($rows as $row) {
            $value = $row[$field] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $keyed[(string) $value] = $row;
        }

        return $keyed;
    }

    private function groupByField(array $rows, string $field): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $value = $row[$field] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $grouped[(string) $value][] = $row;
        }

        return $grouped;
    }

    private function createReferenceLookup(array $rows, array $keys): array
    {
        $lookup = [];

        foreach ($rows as $row) {
            foreach ($keys as $key) {
                $value = $row[$key] ?? null;
                $normalizedKey = $this->normalizeLookupKey($value);

                if ($normalizedKey === null) {
                    continue;
                }

                $lookup[$normalizedKey] = $row;
            }
        }

        return $lookup;
    }

    private function lookupReference(array $lookup, mixed $value): ?array
    {
        $key = $this->normalizeLookupKey($value);

        if ($key === null) {
            return null;
        }

        return $lookup[$key] ?? null;
    }

    private function normalizeLookupKey(mixed $value): ?string
    {
        $value = $this->normalizeString($value);

        return $value === null ? null : mb_strtoupper($value);
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeCoordinate(mixed $value, bool $isLatitude): ?float
    {
        $normalized = $this->normalizeString($value);

        if ($normalized === null) {
            return null;
        }

        $normalized = str_replace(',', '.', $normalized);

        if (! is_numeric($normalized)) {
            return null;
        }

        $coordinate = (float) $normalized;

        if ($isLatitude && abs($coordinate) > 90) {
            return null;
        }

        if (! $isLatitude && abs($coordinate) > 180) {
            return null;
        }

        return $coordinate;
    }
}
