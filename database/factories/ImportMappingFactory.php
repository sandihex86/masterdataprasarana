<?php

namespace Database\Factories;

use App\Models\ImportMapping;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ImportMapping>
 */
class ImportMappingFactory extends Factory
{
    protected $model = ImportMapping::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => 'Mapping Stasiun Legacy',
            'source_system' => 'legacy_djka',
            'source_table' => 'mst_stasiun',
            'entity_type' => 'station',
            'version' => 1,
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
        ];
    }
}
