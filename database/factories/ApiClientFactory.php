<?php

namespace Database\Factories;

use App\Models\ApiClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiClient>
 */
class ApiClientFactory extends Factory
{
    protected $model = ApiClient::class;

    public function definition(): array
    {
        $code = Str::slug(fake()->unique()->company(), '_');

        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->company(),
            'code' => substr($code, 0, 100),
            'description' => fake()->sentence(),
            'owner_name' => fake()->name(),
            'owner_email' => fake()->safeEmail(),
            'allowed_ips' => ['127.0.0.1'],
            'allowed_origins' => ['http://localhost'],
            'rate_limit_per_minute' => 60,
            'rate_limit_per_day' => 10000,
            'expires_at' => now()->addMonth(),
            'last_used_at' => null,
            'is_active' => true,
        ];
    }
}
