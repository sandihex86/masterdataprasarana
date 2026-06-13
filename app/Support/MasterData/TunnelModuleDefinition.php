<?php

namespace App\Support\MasterData;

final class TunnelModuleDefinition
{
    public static function typeAttributes(): array
    {
        return [
            'description' => 'Master data terowongan DJKA yang disiapkan sebagai bounded context terpisah dari jembatan.',
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
                'data.tunnel_number' => ['nullable', 'string', 'max:32'],
                'data.tunnel_kind' => ['nullable', 'string', 'max:255'],
                'data.km_hm' => ['nullable', 'string', 'max:16'],
                'data.length_m' => ['nullable', 'numeric'],
                'data.track_count' => ['nullable', 'integer'],
                'data.completed_year' => ['nullable', 'string', 'max:8'],
                'data.latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'data.longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'data.profile' => ['nullable', 'array'],
                'data.assessment' => ['nullable', 'array'],
                'data.photo_1' => ['nullable', 'string', 'max:255'],
                'data.photo_2' => ['nullable', 'string', 'max:255'],
                'data.document_path' => ['nullable', 'string', 'max:255'],
                'data.video_path' => ['nullable', 'string', 'max:255'],
            ],
            'searchable_fields' => ['code', 'name', 'data.tunnel_number', 'data.lintas_code', 'description'],
            'visible_fields' => ['code', 'name', 'status', 'data.tunnel_number', 'data.lintas_code', 'data.km_hm'],
            'mapping_configuration' => [
                'source_system' => 'legacy_terowongan',
                'source_table' => 'm_terowongan',
                'connection' => 'tunnel',
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
                    'profile' => 'm_terowongan_profil',
                    'scores_total' => 'm_terowongan_nilai_total',
                    'inspection_detail' => 'm_terowongan_detail',
                    'maintenance' => 'm_terowongan_perawatan',
                    'survey' => 'm_terowongan_survey',
                ],
            ],
        ];
    }
}
