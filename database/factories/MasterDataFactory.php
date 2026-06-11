<?php

namespace Database\Factories;

use App\Enums\MasterDataStatus;
use App\Models\MasterData;
use App\Models\MasterDataType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MasterData>
 */
class MasterDataFactory extends Factory
{
    protected $model = MasterData::class;

    public function definition(): array
    {
        $type = MasterDataType::query()->firstOrCreate([
            'code' => 'station',
        ], [
            'uuid' => (string) Str::uuid(),
            'name' => 'Station',
            'is_active' => true,
        ]);
        $code = strtoupper(fake()->unique()->lexify('STA???'));

        return [
            'uuid' => (string) Str::uuid(),
            'source_system' => 'legacy_test',
            'source_table' => 'mst_station',
            'source_id' => (string) fake()->unique()->numberBetween(1, 999999),
            'entity_type' => $type->code,
            'code' => $code,
            'name' => fake()->city(),
            'parent_code' => '31',
            'description' => fake()->sentence(),
            'data' => [
                'province_code' => '31',
                'latitude' => -6.2,
                'longitude' => 106.8,
            ],
            'metadata' => [
                'import_batch_uuid' => (string) Str::uuid(),
            ],
            'checksum' => hash('sha256', $code),
            'version' => 1,
            'status' => MasterDataStatus::Active,
        ];
    }
}
