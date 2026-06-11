<?php

namespace Tests\Feature\Api;

use App\Enums\MasterDataStatus;
use App\Models\ApiClient;
use App\Models\MasterData;
use App\Models\MasterDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterDataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/master-data')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'AUTHENTICATION_REQUIRED');
    }

    public function test_api_client_token_can_list_records_and_returns_request_id(): void
    {
        $type = $this->createStationType();
        $this->createRecord($type, [
            'code' => 'GMR',
            'name' => 'Gambir',
        ]);

        [, $token] = $this->issueClientToken(['master-data:read']);
        $requestId = (string) Str::uuid();

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token, ['X-Request-ID' => $requestId]))
            ->getJson('/api/v1/master-data?type=station');

        $response
            ->assertOk()
            ->assertHeader('X-Request-ID', $requestId)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.request_id', $requestId)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'GMR');
    }

    public function test_inactive_api_client_is_rejected(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read'], ['is_active' => false]);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/master-data')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'ACCESS_DENIED');
    }

    public function test_expired_token_is_rejected(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read'], tokenExpiresAt: now()->subMinute());

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/master-data')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'TOKEN_EXPIRED');
    }

    public function test_master_data_crud_restore_flow_works(): void
    {
        $this->createStationType();
        [, $token] = $this->issueClientToken([
            'master-data:read',
            'master-data:write',
            'master-data:delete',
        ]);

        $storeResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/master-data', [
                'source_system' => 'legacy_test',
                'source_table' => 'mst_stasiun',
                'source_id' => '123',
                'entity_type' => 'station',
                'code' => 'JNG',
                'name' => 'Jatinegara',
                'parent_code' => '31',
                'description' => 'Data uji CRUD',
                'status' => 'active',
                'data' => [
                    'latitude' => -6.2146,
                    'longitude' => 106.8704,
                ],
                'metadata' => [
                    'raw_source' => 'feature-test',
                ],
            ]);

        $uuid = $storeResponse->json('data.uuid');

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.code', 'JNG');

        $this->assertDatabaseHas('master_data', [
            'uuid' => $uuid,
            'code' => 'JNG',
            'version' => 1,
        ]);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->patchJson("/api/v1/master-data/{$uuid}", [
                'name' => 'Jatinegara Baru',
                'status' => MasterDataStatus::Inactive->value,
            ])
            ->assertOk()
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.name', 'Jatinegara Baru');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->deleteJson("/api/v1/master-data/{$uuid}")
            ->assertOk();

        $this->assertSoftDeleted('master_data', [
            'uuid' => $uuid,
        ]);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson("/api/v1/master-data/{$uuid}/restore")
            ->assertOk()
            ->assertJsonPath('data.uuid', $uuid)
            ->assertJsonPath('data.deleted_at', null);
    }

    public function test_filters_pagination_and_type_record_routes_work(): void
    {
        $type = $this->createStationType();
        $this->createRecord($type, ['code' => 'GMR', 'name' => 'Gambir']);
        $this->createRecord($type, ['code' => 'JNG', 'name' => 'Jatinegara']);
        $this->createRecord($type, ['code' => 'BD', 'name' => 'Bandung']);

        [, $token] = $this->issueClientToken(['master-data:read']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/master-data?type=station&search=ga&sort=name&per_page=2')
            ->assertOk()
            ->assertJsonPath('meta.pagination.per_page', 2)
            ->assertJsonCount(2, 'data');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/master-data-types/station/records/JNG')
            ->assertOk()
            ->assertJsonPath('data.code', 'JNG')
            ->assertJsonPath('data.entity_type', 'station');
    }

    public function test_invalid_sort_is_rejected(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/master-data?sort=drop_table')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    private function createStationType(): MasterDataType
    {
        return MasterDataType::factory()->create([
            'code' => 'station',
            'name' => 'Stasiun',
            'validation_rules' => [
                'code' => ['required', 'string', 'max:191'],
                'name' => ['required', 'string', 'max:191'],
            ],
        ]);
    }

    /**
     * @param  array<int, string>  $abilities
     * @param  array<string, mixed>  $clientOverrides
     * @return array{0: ApiClient, 1: string}
     */
    private function issueClientToken(array $abilities, array $clientOverrides = [], ?\DateTimeInterface $tokenExpiresAt = null): array
    {
        $client = ApiClient::factory()->create(array_merge([
            'allowed_ips' => ['127.0.0.1'],
        ], $clientOverrides));

        $token = $client->createToken('feature-test', $abilities, $tokenExpiresAt ?? now()->addDay());
        $token->accessToken->forceFill([
            'api_client_id' => $client->id,
        ])->save();

        return [$client, $token->plainTextToken];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRecord(MasterDataType $type, array $attributes = []): MasterData
    {
        return MasterData::query()->create(array_merge([
            'uuid' => (string) Str::uuid(),
            'source_system' => 'legacy_test',
            'source_table' => 'mst_stasiun',
            'source_id' => (string) fake()->unique()->numberBetween(1, 999999),
            'entity_type' => $type->code,
            'code' => strtoupper(fake()->unique()->lexify('STA???')),
            'name' => fake()->city(),
            'parent_code' => '31',
            'description' => fake()->sentence(),
            'data' => [
                'province_code' => '31',
                'latitude' => -6.2,
                'longitude' => 106.8,
            ],
            'metadata' => [
                'raw_source' => 'feature-test',
            ],
            'checksum' => hash('sha256', Str::uuid()->toString()),
            'version' => 1,
            'status' => MasterDataStatus::Active,
        ], $attributes));
    }

    /**
     * @param  array<string, string>  $extra
     * @return array<string, string>
     */
    private function apiHeaders(string $token, array $extra = []): array
    {
        return array_merge([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ], $extra);
    }
}
