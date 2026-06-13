<?php

namespace Database\Seeders;

use App\Enums\MasterDataStatus;
use App\Models\ApiClient;
use App\Models\ImportMapping;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Support\MasterData\BridgeModuleDefinition;
use App\Support\MasterData\TunnelModuleDefinition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $actorId = null;

        $stationType = $this->seedMasterDataType(
            code: 'station',
            name: 'Stasiun',
            actorId: $actorId,
            attributes: [
                'validation_rules' => [
                    'code' => ['required', 'string', 'max:191'],
                    'name' => ['required', 'string', 'max:191'],
                    'data.latitude' => ['nullable', 'numeric', 'between:-90,90'],
                    'data.longitude' => ['nullable', 'numeric', 'between:-180,180'],
                ],
            ],
        );

        $bridgeType = $this->seedMasterDataType(
            code: 'bridge',
            name: 'Jembatan',
            actorId: $actorId,
            attributes: BridgeModuleDefinition::typeAttributes(),
        );
        $this->seedMasterDataType(
            code: 'tunnel',
            name: 'Terowongan',
            actorId: $actorId,
            attributes: TunnelModuleDefinition::typeAttributes(),
        );
        $this->seedMasterDataType(code: 'railway_track', name: 'Jalur Kereta', actorId: $actorId);
        $this->seedMasterDataType(code: 'province', name: 'Provinsi', actorId: $actorId);
        $this->seedMasterDataType(code: 'city', name: 'Kabupaten/Kota', actorId: $actorId);
        $this->seedMasterDataType(code: 'operator', name: 'Operator', actorId: $actorId);

        $this->seedMasterDataRecord($stationType, $actorId);
        $this->seedBridgeMasterDataRecord($bridgeType, $actorId);
        $this->seedApiClient($actorId);
        $this->seedImportMapping($actorId);
        $this->seedBridgeImportMapping($actorId);
    }

    private function seedMasterDataType(string $code, string $name, ?int $actorId, array $attributes = []): MasterDataType
    {
        $type = MasterDataType::query()->firstOrNew(['code' => $code]);

        if (! $type->exists) {
            $type->uuid = (string) Str::uuid();
            $type->created_by = $actorId;
        }

        $type->fill(array_merge([
            'name' => $name,
            'description' => $type->description ?? 'Data referensi untuk pengembangan lokal.',
            'validation_rules' => [
                'code' => ['required', 'string', 'max:191'],
                'name' => ['nullable', 'string', 'max:191'],
            ],
            'searchable_fields' => ['code', 'name'],
            'visible_fields' => ['code', 'name', 'status'],
            'mapping_configuration' => [
                'source_system' => 'legacy_example',
            ],
            'is_active' => true,
            'updated_by' => $actorId,
        ], $attributes));

        $type->save();

        return $type;
    }

    private function seedMasterDataRecord(MasterDataType $stationType, ?int $actorId): void
    {
        $record = MasterData::query()->firstOrNew([
            'source_system' => 'legacy_seed',
            'source_table' => 'mst_stasiun',
            'source_id' => '1',
        ]);

        if (! $record->exists) {
            $record->uuid = (string) Str::uuid();
            $record->created_by = $actorId;
        }

        $record->fill([
            'entity_type' => $stationType->code,
            'code' => 'GMR',
            'name' => 'Gambir',
            'parent_code' => '31',
            'description' => 'Contoh data stasiun untuk pengembangan lokal.',
            'data' => [
                'station_class' => 'Besar A',
                'province_code' => '31',
                'city_code' => '3171',
                'latitude' => -6.1767,
                'longitude' => 106.8305,
                'address' => 'Jakarta Pusat',
            ],
            'metadata' => [
                'raw_source' => 'seed',
                'mapping_version' => 1,
            ],
            'checksum' => hash('sha256', 'GMR'),
            'version' => 1,
            'status' => MasterDataStatus::Active,
            'updated_by' => $actorId,
        ]);

        $record->save();
    }

    private function seedBridgeMasterDataRecord(MasterDataType $bridgeType, ?int $actorId): void
    {
        $record = MasterData::query()->firstOrNew([
            'source_system' => 'legacy_jembatan',
            'source_table' => 'm_jembatan',
            'source_id' => 'bridge-seed-001',
        ]);

        if (! $record->exists) {
            $record->uuid = (string) Str::uuid();
            $record->created_by = $actorId;
        }

        $record->fill([
            'entity_type' => $bridgeType->code,
            'code' => 'BH-0001',
            'name' => 'Jembatan Cikubang',
            'parent_code' => '32',
            'description' => 'Contoh master data jembatan hasil normalisasi struktur legacy m_jembatan.',
            'data' => [
                'inspection_date' => '2024-01-15',
                'wil_ker' => 'DAOP 2 Bandung',
                'province_code' => '32',
                'city_code' => '3204',
                'operational_area_code' => '2',
                'latitude' => -6.8423,
                'longitude' => 107.1291,
                'lintas_code' => 'BDB',
                'station_start_code' => 'CKB',
                'station_end_code' => 'PDL',
                'bridge_number' => 'BH-0001',
                'direction' => 'Hilir ke arah Padalarang',
                'bridge_kind' => 'Rangka Baja',
                'km_hm' => '123+450',
                'photo_1' => 'bridge/bh-0001/foto-1.jpg',
                'photo_2' => 'bridge/bh-0001/foto-2.jpg',
                'caption_1' => 'Tampak depan jembatan',
                'caption_2' => 'Tampak samping jembatan',
                'document_path' => 'bridge/bh-0001/dokumen-inspeksi.pdf',
                'video_path' => 'bridge/bh-0001/video.mp4',
                'legacy_active' => 1,
                'legacy_status' => 1,
                'legacy_status_data' => 1,
                'profile' => [
                    'intersection' => 'Sungai',
                    'track_count' => 1,
                    'span_count' => 3,
                    'span_length_1_m' => '20',
                    'span_length_2_m' => '25',
                    'span_length_3_m' => '20',
                    'total_length_m' => '65',
                    'completed_year' => '1998',
                    'top_structure_height_m' => '8',
                    'bottom_structure_height_m' => '12',
                ],
                'spans' => [
                    ['order' => 1, 'length_m' => '20'],
                    ['order' => 2, 'length_m' => '25'],
                    ['order' => 3, 'length_m' => '20'],
                ],
                'substructures' => [
                    ['order' => 1, 'number' => 'P1', 'material' => 'Beton', 'type' => 'Pilar', 'manteling' => 'Baik', 'kind' => 'Pondasi dalam'],
                    ['order' => 2, 'number' => 'A1', 'material' => 'Beton', 'type' => 'Abutmen', 'manteling' => 'Baik', 'kind' => 'Pondasi langsung'],
                ],
                'assessment' => [
                    'top' => ['span' => 2, 'bearing' => 2],
                    'bottom' => ['kind' => 'Pilar', 'condition_1' => 1, 'condition_2' => 2],
                    'protection' => ['flow_guard' => 1, 'guide' => 1, 'landslide_guard' => 1],
                    'total_score' => 92.5,
                    'conclusion' => 1,
                ],
            ],
            'metadata' => [
                'raw_source' => 'seed',
                'mapping_version' => 1,
                'legacy_database' => 'jembatan',
                'legacy_tables' => ['m_jembatan', 'm_jembatan_profil', 'm_jembatan_bentang'],
            ],
            'checksum' => hash('sha256', 'BH-0001'),
            'version' => 1,
            'status' => MasterDataStatus::Active,
            'updated_by' => $actorId,
        ]);

        $record->save();
    }

    private function seedApiClient(?int $actorId): void
    {
        $client = ApiClient::query()->firstOrNew(['code' => 'local_api_client']);

        if (! $client->exists) {
            $client->uuid = (string) Str::uuid();
            $client->created_by = $actorId;
        }

        $client->forceFill([
            'name' => 'Client API',
            'description' => $client->description,
            'owner_name' => null,
            'owner_email' => null,
            'allowed_ips' => ['127.0.0.1'],
            'allowed_origins' => array_values(array_filter([(string) config('app.url')])),
            'rate_limit_per_minute' => 60,
            'rate_limit_per_day' => 10000,
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'updated_by' => $actorId,
        ])->save();
    }

    private function seedImportMapping(?int $actorId): void
    {
        $mapping = ImportMapping::query()->firstOrNew([
            'source_system' => 'legacy_djka',
            'source_table' => 'mst_stasiun',
            'entity_type' => 'station',
            'version' => 1,
        ]);

        if (! $mapping->exists) {
            $mapping->uuid = (string) Str::uuid();
            $mapping->created_by = $actorId;
        }

        $mapping->fill([
            'name' => 'Mapping Stasiun Legacy',
            'mapping' => [
                'source_system' => 'legacy_djka',
                'source_table' => 'mst_stasiun',
                'entity_type' => 'station',
                'identity' => [
                    'source_id' => 'id_stasiun',
                    'code' => 'kode_stasiun',
                ],
                'columns' => [
                    'name' => 'nama_stasiun',
                    'parent_code' => 'kode_provinsi',
                    'description' => 'keterangan',
                ],
                'data' => [
                    'station_class' => 'kelas_stasiun',
                    'province_code' => 'kode_provinsi',
                    'city_code' => 'kode_kabupaten',
                    'latitude' => 'latitude',
                    'longitude' => 'longitude',
                    'address' => 'alamat',
                ],
                'transformations' => [
                    'code' => ['trim', 'uppercase'],
                    'name' => ['trim', 'normalize_whitespace'],
                    'latitude' => ['nullable_float'],
                    'longitude' => ['nullable_float'],
                ],
            ],
            'transformations' => [
                'code' => ['trim', 'uppercase'],
                'name' => ['trim', 'normalize_whitespace'],
                'latitude' => ['nullable_float'],
                'longitude' => ['nullable_float'],
            ],
            'validation_rules' => [],
            'is_active' => true,
            'updated_by' => $actorId,
        ]);

        $mapping->save();
    }

    private function seedBridgeImportMapping(?int $actorId): void
    {
        $mapping = ImportMapping::query()->firstOrNew([
            'source_system' => 'legacy_jembatan',
            'source_table' => 'm_jembatan',
            'entity_type' => 'bridge',
            'version' => 1,
        ]);

        if (! $mapping->exists) {
            $mapping->uuid = (string) Str::uuid();
            $mapping->created_by = $actorId;
        }

        $mapping->fill([
            'name' => 'Mapping Jembatan Legacy',
            'mapping' => [
                'source_system' => 'legacy_jembatan',
                'source_table' => 'm_jembatan',
                'entity_type' => 'bridge',
                'identity' => [
                    'source_id' => 'uniqid',
                    'code' => 'uniqid',
                ],
                'columns' => [
                    'name' => 'nama',
                    'parent_code' => 'id_prov',
                    'description' => 'catatan',
                ],
                'data' => [
                    'inspection_date' => 'tanggal',
                    'wil_ker' => 'wil_ker',
                    'province_code' => 'id_prov',
                    'city_code' => 'id_kabkot',
                    'operational_area_code' => 'wil_op',
                    'latitude' => [
                        'source' => 'lat',
                        'transformations' => ['decimal_comma_to_dot', 'nullable_float'],
                    ],
                    'longitude' => [
                        'source' => 'lon',
                        'transformations' => ['decimal_comma_to_dot', 'nullable_float'],
                    ],
                    'lintas_code' => 'lintas',
                    'station_start_code' => 'stasiun1',
                    'station_end_code' => 'stasiun2',
                    'bridge_number' => 'no_bh',
                    'direction' => 'arah_bh',
                    'bridge_kind' => 'jenis',
                    'km_hm' => 'km_hm',
                    'photo_1' => 'foto1',
                    'photo_2' => 'foto2',
                    'photo_3' => 'foto3',
                    'photo_4' => 'foto4',
                    'caption_1' => 'caption1',
                    'caption_2' => 'caption2',
                    'caption_3' => 'caption3',
                    'caption_4' => 'caption4',
                    'document_path' => 'dokumen',
                    'video_path' => 'video',
                    'legacy_active' => [
                        'source' => 'active',
                        'transformations' => ['nullable_integer'],
                    ],
                    'legacy_status' => [
                        'source' => 'status',
                        'transformations' => ['nullable_integer'],
                    ],
                    'legacy_status_data' => [
                        'source' => 'statusdata',
                        'transformations' => ['nullable_integer'],
                    ],
                ],
                'transformations' => [
                    'source_id' => ['trim'],
                    'code' => ['trim'],
                    'name' => ['trim', 'normalize_whitespace'],
                    'parent_code' => ['trim'],
                    'description' => ['trim', 'normalize_whitespace'],
                ],
                'status' => 'active',
            ],
            'transformations' => [
                'source_id' => ['trim'],
                'code' => ['trim'],
                'name' => ['trim', 'normalize_whitespace'],
                'parent_code' => ['trim'],
                'description' => ['trim', 'normalize_whitespace'],
            ],
            'validation_rules' => [],
            'is_active' => true,
            'updated_by' => $actorId,
        ]);

        $mapping->save();
    }
}
