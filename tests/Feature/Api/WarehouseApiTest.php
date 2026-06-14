<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarehouseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::connection('warehouse')->hasTable('m_gudang')) {
            (require base_path('database/migrations/2026_06_14_000000_create_warehouse_source_tables.php'))->up();
        }
    }

    public function test_v1_warehouse_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/warehouses')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'AUTHENTICATION_REQUIRED');
    }

    public function test_api_client_can_crud_v1_warehouse_and_identity_is_generated(): void
    {
        [, $token] = $this->issueClientToken([
            'master-data:read',
            'master-data:write',
            'master-data:delete',
        ]);

        $createResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/warehouses', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.nama_gudang', 'Gudang Payakabung')
            ->assertJsonPath('data.tipe_gudang', 'Material')
            ->assertJsonPath('data.coordinates.lat', -3.2831)
            ->assertJsonPath('data.coordinates.long', 104.9132)
            ->assertJsonPath('data.active', true);

        $idGudang = $createResponse->json('data.id_gudang');
        $kodeGudang = $createResponse->json('data.kode_gudang');

        $this->assertSame($idGudang, $kodeGudang);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $idGudang);
        $this->assertDatabaseHas('m_gudang', [
            'id_gudang' => $idGudang,
            'kode_gudang' => $idGudang,
            'nama_gudang' => 'Gudang Payakabung',
        ], 'warehouse');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/warehouses?search=Payakabung&id_wilker=DIVRE3&sort_by=nama_gudang&sort_dir=asc')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_gudang', $idGudang)
            ->assertJsonPath('data.0.kode_gudang', $idGudang)
            ->assertJsonPath('meta.pagination.total', 1);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/warehouses/batch?active=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_gudang', $idGudang)
            ->assertJsonPath('data.0.kode_gudang', $idGudang)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.filters.active', true);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson("/api/v1/warehouses/{$idGudang}")
            ->assertOk()
            ->assertJsonPath('data.id_gudang', $idGudang)
            ->assertJsonPath('data.kode_gudang', $idGudang);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->patchJson("/api/v1/warehouses/{$idGudang}", [
                'nama_gudang' => 'Gudang Payakabung Utama',
                'tipe_gudang' => 'Suku Cadang',
                'lat' => -3.28,
                'long' => 104.91,
            ])
            ->assertOk()
            ->assertJsonPath('data.id_gudang', $idGudang)
            ->assertJsonPath('data.kode_gudang', $idGudang)
            ->assertJsonPath('data.nama_gudang', 'Gudang Payakabung Utama')
            ->assertJsonPath('data.tipe_gudang', 'Suku Cadang');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->deleteJson("/api/v1/warehouses/{$idGudang}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('m_gudang', [
            'id_gudang' => $idGudang,
        ], 'warehouse');
    }

    public function test_warehouse_validation_rejects_manual_identity_and_invalid_coordinates(): void
    {
        [, $token] = $this->issueClientToken(['master-data:write']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/warehouses', [
                'id_gudang' => '01JY0000000000000000000000',
                'kode_gudang' => '01JY0000000000000000000000',
                'nama_gudang' => '',
                'lat' => -91,
                'long' => 181,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'details' => [
                        'id_gudang',
                        'kode_gudang',
                        'nama_gudang',
                        'lat',
                        'long',
                    ],
                ],
            ]);
    }

    public function test_warehouse_legacy_alias_is_available(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/warehouses')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'nama_gudang' => 'Gudang Payakabung',
            'tipe_gudang' => 'Material',
            'id_wilker' => 'DIVRE3',
            'id_prov' => '16',
            'id_kabkot' => '1607',
            'lat' => -3.2831,
            'long' => 104.9132,
            'active' => true,
        ];
    }

    /**
     * @param  array<int, string>  $abilities
     * @return array{0: ApiClient, 1: string}
     */
    private function issueClientToken(array $abilities): array
    {
        $client = ApiClient::factory()->create([
            'allowed_ips' => ['127.0.0.1'],
        ]);

        $token = $client->createToken('warehouse-api-test', $abilities, now()->addDay());
        $token->accessToken->forceFill([
            'api_client_id' => $client->id,
        ])->save();

        return [$client, $token->plainTextToken];
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(string $token): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ];
    }
}
