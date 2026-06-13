<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use App\Models\ImportMapping;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportMappingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::connection('bridge')->hasTable('legacy_stations')) {
            Schema::connection('bridge')->create('legacy_stations', function (Blueprint $table): void {
                $table->unsignedBigInteger('id_stasiun')->primary();
                $table->string('kode_stasiun', 20);
                $table->string('nama_stasiun', 191);
                $table->string('kode_provinsi', 20)->nullable();
                $table->string('kode_kabupaten', 20)->nullable();
                $table->string('kelas_stasiun', 100)->nullable();
                $table->string('latitude', 50)->nullable();
                $table->string('longitude', 50)->nullable();
                $table->string('alamat', 191)->nullable();
                $table->text('keterangan')->nullable();
            });
        }

        DB::connection('bridge')->table('legacy_stations')->truncate();
        DB::connection('bridge')->table('legacy_stations')->insert([
            [
                'id_stasiun' => 1,
                'kode_stasiun' => ' gmr ',
                'nama_stasiun' => '  Gambir  ',
                'kode_provinsi' => '31',
                'kode_kabupaten' => '3171',
                'kelas_stasiun' => 'Besar A',
                'latitude' => '-6.1767',
                'longitude' => '106.8305',
                'alamat' => 'Jakarta Pusat',
                'keterangan' => 'Stasiun utama Jakarta',
            ],
            [
                'id_stasiun' => 2,
                'kode_stasiun' => 'jng',
                'nama_stasiun' => 'Jatinegara',
                'kode_provinsi' => '31',
                'kode_kabupaten' => '3175',
                'kelas_stasiun' => 'Besar B',
                'latitude' => '-6,2146',
                'longitude' => '106,8704',
                'alamat' => 'Jakarta Timur',
                'keterangan' => 'Perlu normalisasi decimal comma',
            ],
            [
                'id_stasiun' => 3,
                'kode_stasiun' => 'bd',
                'nama_stasiun' => 'Bandung',
                'kode_provinsi' => '32',
                'kode_kabupaten' => '3273',
                'kelas_stasiun' => 'Besar C',
                'latitude' => '999',
                'longitude' => '107.6191',
                'alamat' => 'Bandung',
                'keterangan' => 'Baris invalid latitude',
            ],
        ]);
    }

    public function test_can_store_import_mapping_configuration(): void
    {
        [, $token] = $this->issueClientToken(['imports:create', 'imports:read']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/import-mappings', [
                'name' => 'Mapping Legacy Stasiun',
                'mapping' => $this->mappingConfiguration(),
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.source_table', 'legacy_stations')
            ->assertJsonPath('data.mapping.entity_type', 'station');

        $this->assertDatabaseHas('import_mappings', [
            'name' => 'Mapping Legacy Stasiun',
            'source_table' => 'legacy_stations',
            'entity_type' => 'station',
        ]);
    }

    public function test_preview_mapping_transforms_rows_and_returns_validation_errors(): void
    {
        [, $token] = $this->issueClientToken(['imports:create', 'imports:read']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/import-mappings/preview', [
                'mapping' => $this->mappingConfiguration(),
                'limit' => 3,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.count', 3)
            ->assertJsonPath('data.items.0.normalized.code', 'GMR')
            ->assertJsonPath('data.items.0.normalized.name', 'Gambir')
            ->assertJsonPath('data.items.1.normalized.data.latitude', -6.2146)
            ->assertJsonPath('data.items.1.normalized.data.longitude', 106.8704)
            ->assertJsonPath('data.items.2.normalized', null);

        $errors = $response->json('data.items.2.errors');

        $this->assertArrayHasKey('data.latitude', $errors);
    }

    public function test_preview_can_use_saved_mapping_uuid(): void
    {
        $mapping = ImportMapping::factory()->create([
            'mapping' => $this->mappingConfiguration(),
            'source_table' => 'legacy_stations',
        ]);

        [, $token] = $this->issueClientToken(['imports:create', 'imports:read']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/import-mappings/preview', [
                'mapping_uuid' => $mapping->uuid,
                'limit' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.items.0.normalized.code', 'GMR');
    }

    /**
     * @return array<string, mixed>
     */
    private function mappingConfiguration(): array
    {
        return [
            'source_system' => 'legacy_djka',
            'source_table' => 'legacy_stations',
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
                'latitude' => [
                    'source' => 'latitude',
                    'transformations' => ['decimal_comma_to_dot', 'nullable_float'],
                ],
                'longitude' => [
                    'source' => 'longitude',
                    'transformations' => ['decimal_comma_to_dot', 'nullable_float'],
                ],
                'address' => 'alamat',
            ],
            'transformations' => [
                'code' => ['trim', 'uppercase'],
                'name' => ['trim', 'normalize_whitespace'],
            ],
            'status' => 'active',
        ];
    }

    /**
     * @param  array<int, string>  $abilities
     * @param  array<string, mixed>  $clientOverrides
     * @return array{0: ApiClient, 1: string}
     */
    private function issueClientToken(array $abilities, array $clientOverrides = []): array
    {
        $client = ApiClient::factory()->create(array_merge([
            'allowed_ips' => ['127.0.0.1'],
        ], $clientOverrides));

        $token = $client->createToken('feature-test', $abilities, now()->addDay());
        $token->accessToken->forceFill([
            'api_client_id' => $client->id,
        ])->save();

        return [$client, $token->plainTextToken];
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
