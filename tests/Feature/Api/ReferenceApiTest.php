<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use Database\Seeders\ReferenceSourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReferenceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::connection('reference')->hasTable('provinsi')) {
            (require base_path('database/migrations/2026_06_14_020000_create_reference_source_tables.php'))->up();
        }

        $this->seed(ReferenceSourceSeeder::class);
    }

    public function test_reference_lookup_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/references/provinsi/metadata')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'AUTHENTICATION_REQUIRED');
    }

    public function test_api_client_can_read_reference_metadata_batch_and_code_lookup(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read']);

        $entities = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/entities')
            ->assertOk();

        $this->assertContains('wilops', collect($entities->json('data'))->pluck('entity')->all());

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/provinsi/metadata')
            ->assertOk()
            ->assertJsonPath('data.table', 'provinsi')
            ->assertJsonPath('data.alias', 'provinsi')
            ->assertJsonPath('data.code_column', 'id')
            ->assertJsonPath('data.id_strategy', 'source_code');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/prasarana/metadata')
            ->assertOk()
            ->assertJsonPath('data.table', 'm_prasarana')
            ->assertJsonPath('data.entity', 'prasarana')
            ->assertJsonPath('data.represented_table', 'm_prasarana')
            ->assertJsonPath('data.code_column', 'kode_prasarana')
            ->assertJsonPath('data.id_column', 'id')
            ->assertJsonPath('data.id_strategy', 'ulid')
            ->assertJsonPath('data.fields.1.name', 'id')
            ->assertJsonPath('data.fields.1.format', 'ulid');

        $batchResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/provinsi/batch')
            ->assertOk()
            ->assertJsonPath('meta.reference.alias', 'provinsi')
            ->assertJsonPath('meta.reference.code_column', 'id');

        $this->assertGreaterThan(0, $batchResponse->json('meta.total'));

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/provinsi/kode/11')
            ->assertOk()
            ->assertJsonPath('data.table', 'provinsi')
            ->assertJsonPath('data.data.id', '11')
            ->assertJsonPath('data.data.name', 'Aceh (NAD)');

        $prasaranaResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/prasarana/kode/PRAS-01')
            ->assertOk()
            ->assertJsonPath('data.data.kode_prasarana', 'PRAS-01');

        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $prasaranaResponse->json('data.data.id'));
    }

    public function test_cluster_reference_entities_support_metadata_batch_by_id_and_search(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read']);

        $prasaranaBatch = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/prasarana/batch')
            ->assertOk()
            ->assertJsonPath('meta.reference.entity', 'prasarana')
            ->assertJsonPath('meta.reference.represented_table', 'm_prasarana');

        $prasaranaId = $prasaranaBatch->json('data.0.row_key');
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $prasaranaId);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/prasarana/'.$prasaranaId)
            ->assertOk()
            ->assertJsonPath('data.row_key', $prasaranaId)
            ->assertJsonPath('data.data.kode_prasarana', 'PRAS-01');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/stasiun/search?q=Jakarta&per_page=5')
            ->assertOk()
            ->assertJsonPath('meta.pagination.current_page', 1)
            ->assertJsonPath('data.0.table', 'm_stasiun');

        $wilopsBatch = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/wilops/batch')
            ->assertOk()
            ->assertJsonPath('meta.reference.entity', 'wilops')
            ->assertJsonPath('meta.reference.represented_table', 'm_stasiun');

        $wilopsId = $wilopsBatch->json('data.0.row_key');
        $this->assertNotEmpty($wilopsId);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/wilops/'.rawurlencode($wilopsId))
            ->assertOk()
            ->assertJsonPath('data.entity', 'wilops')
            ->assertJsonPath('data.data.id', $wilopsId);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/references/wilops/metadata')
            ->assertOk()
            ->assertJsonPath('data.fields.0.name', 'id')
            ->assertJsonPath('data.fields.0.format', 'source_value')
            ->assertJsonPath('data.fields.1.name', 'nama_wilops');
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

        $token = $client->createToken('reference-api-test', $abilities, now()->addDay());
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
