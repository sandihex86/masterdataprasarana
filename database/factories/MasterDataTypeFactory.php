<?php

namespace Database\Factories;

use App\Models\MasterDataType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MasterDataType>
 */
class MasterDataTypeFactory extends Factory
{
    protected $model = MasterDataType::class;

    public function definition(): array
    {
        $code = Str::slug(fake()->unique()->words(2, true), '_');

        return [
            'uuid' => (string) Str::uuid(),
            'code' => $code,
            'name' => Str::headline(str_replace('_', ' ', $code)),
            'description' => fake()->sentence(),
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
        ];
    }
}
