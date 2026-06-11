<?php

namespace App\Support\MasterData;

final class BridgeModuleDefinition
{
    public static function typeAttributes(): array
    {
        return [
            'description' => 'Master data jembatan DJKA yang dinormalisasi dari basis data legacy jembatan.',
            'validation_rules' => [
                'code' => ['required', 'string', 'max:191'],
                'name' => ['required', 'string', 'max:191'],
                'data.inspection_date' => ['nullable', 'date'],
                'data.wil_ker' => ['nullable', 'string', 'max:255'],
                'data.province_code' => ['nullable', 'string', 'max:32'],
                'data.city_code' => ['nullable', 'string', 'max:32'],
                'data.operational_area_code' => ['nullable', 'string', 'max:32'],
                'data.lintas_code' => ['nullable', 'string', 'max:16'],
                'data.station_start_code' => ['nullable', 'string', 'max:32'],
                'data.station_end_code' => ['nullable', 'string', 'max:32'],
                'data.bridge_number' => ['nullable', 'string', 'max:32'],
                'data.direction' => ['nullable', 'string', 'max:255'],
                'data.bridge_kind' => ['nullable', 'string', 'max:255'],
                'data.km_hm' => ['nullable', 'string', 'max:16'],
                'data.photo_1' => ['nullable', 'string', 'max:255'],
                'data.photo_2' => ['nullable', 'string', 'max:255'],
                'data.photo_3' => ['nullable', 'string', 'max:255'],
                'data.photo_4' => ['nullable', 'string', 'max:255'],
                'data.caption_1' => ['nullable', 'string', 'max:255'],
                'data.caption_2' => ['nullable', 'string', 'max:255'],
                'data.caption_3' => ['nullable', 'string', 'max:255'],
                'data.caption_4' => ['nullable', 'string', 'max:255'],
                'data.document_path' => ['nullable', 'string', 'max:255'],
                'data.video_path' => ['nullable', 'string', 'max:255'],
                'data.legacy_active' => ['nullable', 'integer'],
                'data.legacy_status' => ['nullable', 'integer'],
                'data.legacy_status_data' => ['nullable', 'integer'],
                'data.profile' => ['nullable', 'array'],
                'data.spans' => ['nullable', 'array'],
                'data.substructures' => ['nullable', 'array'],
                'data.assessment' => ['nullable', 'array'],
                'data.latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'data.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ],
            'searchable_fields' => ['code', 'name', 'data.bridge_number', 'data.lintas_code', 'description'],
            'visible_fields' => ['code', 'name', 'status', 'data.bridge_number', 'data.lintas_code', 'data.km_hm'],
            'mapping_configuration' => [
                'source_system' => 'legacy_jembatan',
                'source_table' => 'm_jembatan',
                'identity' => [
                    'source_id' => 'uniqid',
                    'code' => 'uniqid',
                ],
                'reference_tables' => [
                    'province' => 'm_provinsi',
                    'city' => 'm_kabkot',
                    'lintas' => 'm_lintas',
                    'station' => 'm_stasiun',
                ],
                'detail_tables' => [
                    'profile' => 'm_jembatan_profil',
                    'spans' => 'm_jembatan_bentang',
                    'steel_components' => 'm_jembatan_baja',
                    'concrete_components' => 'm_jembatan_beton',
                    'substructures' => 'm_jembatan_bawah',
                    'protection' => 'm_jembatan_detil_3',
                    'scores_top' => 'm_jembatan_nilai_atas',
                    'scores_bottom' => 'm_jembatan_nilai_bawah',
                    'scores_protection' => 'm_jembatan_nilai_pelindung',
                    'scores_total' => 'm_jembatan_nilai_total',
                    'inspection_detail' => 'm_jembatan_detail',
                    'maintenance' => 'm_jembatan_perawatan',
                ],
            ],
        ];
    }
}
