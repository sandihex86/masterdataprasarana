<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BridgeMasterApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'master-data.bridge_source.connection' => config('database.default'),
        ]);

        $this->seedBridgeFixtures();
    }

    public function test_bridge_integration_health_is_public(): void
    {
        $this->getJson('/api/v1/integration/health')
            ->assertOk()
            ->assertJsonPath('message', 'Bridge API is running')
            ->assertJsonPath('data.service', 'Master Data Jembatan API');
    }

    public function test_api_client_can_read_bridge_master_mvp_endpoints(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read']);

        $headers = $this->apiHeaders($token);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges?keyword=334&limit=10')
            ->assertOk()
            ->assertJsonPath('data.0.kode_jembatan', 'bridge-001')
            ->assertJsonPath('data.0.wilayah_kerja', 'BTP Kelas I Jakarta');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges/bridge-001')
            ->assertOk()
            ->assertJsonPath('data.kode_jembatan', 'bridge-001')
            ->assertJsonPath('data.profil.perpotongan', 'Sungai')
            ->assertJsonPath('data.nilai_kondisi_terakhir.total', 4);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges/batch?limit=1')
            ->assertOk()
            ->assertJsonPath('meta.limit', 1)
            ->assertJsonPath('meta.has_more', false);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges/full-batch?limit=1')
            ->assertOk()
            ->assertJsonPath('data.0.profil.perpotongan', 'Sungai')
            ->assertJsonPath('data.0.bentang.0.pjg_bentang', '20');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges/changed?since=2023-01-01 00:00:00&limit=10')
            ->assertOk()
            ->assertJsonPath('data.0.kode_jembatan', 'bridge-001');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges/bridge-001/profile')
            ->assertOk()
            ->assertJsonPath('data.perpotongan', 'Sungai');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges/bridge-001/spans')
            ->assertOk()
            ->assertJsonPath('data.0.pjg_bentang', '20');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/master/bridges/geojson')
            ->assertOk()
            ->assertJsonPath('data.type', 'FeatureCollection')
            ->assertJsonPath('data.features.0.properties.kode_jembatan', 'bridge-001');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/bridges/bridge-001/condition')
            ->assertOk()
            ->assertJsonPath('data.total.0.total', 4);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/bridges/bridge-001/maintenance')
            ->assertOk()
            ->assertJsonPath('data.0.pemeriksa', 'Petugas Lama');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($headers)
            ->getJson('/api/v1/references/provinces')
            ->assertOk()
            ->assertJsonPath('data.0.nama', 'DKI Jakarta');
    }

    public function test_api_client_can_create_bridge_maintenance(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read', 'master-data:write']);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/bridges/bridge-001/maintenance', [
                'tanggal' => '2026-06-13',
                'pemeriksa' => 'Petugas API',
                'catatan' => 'Perawatan dari API.',
                'active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.kode_jembatan', 'bridge-001')
            ->assertJsonPath('data.pemeriksa', 'Petugas API')
            ->assertJsonPath('data.no_bh', '334');

        $this->assertDatabaseHas('m_jembatan_perawatan', [
            'kode_jembatan' => 'bridge-001',
            'pemeriksa' => 'Petugas API',
            'no_bh' => '334',
        ]);
    }

    private function seedBridgeFixtures(): void
    {
        DB::table('m_wilayah_kerja')->insert([
            'uniqid' => 'wk-001',
            'kode' => 'WK-JKT',
            'nama' => 'BTP Kelas I Jakarta',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_wilayah_operasi')->insert([
            'uniqid' => 'wo-001',
            'kode' => '1',
            'nama' => 'Wilayah Operasi 1',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_provinsi')->insert([
            'uniqid' => 'prov-31',
            'kode' => '31',
            'nama' => 'DKI Jakarta',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_kabkot')->insert([
            'uniqid' => 'city-3171',
            'id_prov' => '31',
            'kode' => '3171',
            'nama' => 'Jakarta Pusat',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_lintas')->insert([
            'uniqid' => 'lintas-001',
            'kode' => 'LN-01',
            'nama' => 'Lintas Rangkasbitung',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_stasiun')->insert([
            [
                'uniqid' => 'sta-001',
                'id_prov' => '31',
                'id_kabkot' => '3171',
                'kode' => 'CTR',
                'nama' => 'Citeras',
                'wil_op' => '1',
                'active' => 1,
                'created_by' => 'Seeder',
                'created_at' => now(),
                'updated_by' => 'Seeder',
                'updated_at' => now(),
            ],
            [
                'uniqid' => 'sta-002',
                'id_prov' => '31',
                'id_kabkot' => '3171',
                'kode' => 'RK',
                'nama' => 'Rangkasbitung',
                'wil_op' => '1',
                'active' => 1,
                'created_by' => 'Seeder',
                'created_at' => now(),
                'updated_by' => 'Seeder',
                'updated_at' => now(),
            ],
        ]);
        DB::table('m_jembatan')->insert([
            'id' => 1,
            'uniqid' => 'bridge-001',
            'tanggal' => '2023-06-25',
            'wil_ker' => 'wk-001',
            'id_prov' => '31',
            'id_kabkot' => '3171',
            'wil_op' => '1',
            'lat' => '-6.352918',
            'lon' => '106.261155',
            'nama' => 'Jembatan Test',
            'lintas' => 'LN-01',
            'stasiun1' => 'sta-001',
            'stasiun2' => 'sta-002',
            'no_bh' => '334',
            'arah_bh' => 'Hilir',
            'jenis' => '2',
            'km_hm' => '78+613',
            'active' => 1,
            'status' => 1,
            'statusdata' => 0,
            'created_by' => 'Seeder',
            'created_at' => '2023-05-10 08:04:05',
            'updated_by' => 'Seeder',
            'updated_at' => '2023-06-25 12:35:09',
            'deleted_at' => null,
        ]);
        DB::table('m_jembatan_profil')->insert([
            'uniqid' => 'profile-001',
            'id_jembatan' => 'bridge-001',
            'perpotongan' => 'Sungai',
            'jml_lintasan' => 1,
            'jml_bentang' => 1,
            'pjg_total' => '20',
            'thn_selesai' => '2008',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_jembatan_bentang')->insert([
            'uniqid' => 'span-001',
            'id_jembatan' => 'bridge-001',
            'pjg_bentang' => '20',
            'urut' => 1,
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_jembatan_nilai_total')->insert([
            'uniqid' => 'condition-001',
            'id_jembatan' => 'bridge-001',
            'total' => 4,
            'kesimpulan' => 4,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_jembatan_perawatan')->insert([
            'uniqid' => 'maintenance-001',
            'kode_jembatan' => 'bridge-001',
            'tanggal' => '2024-01-15',
            'pemeriksa' => 'Petugas Lama',
            'lat' => '-6.352918',
            'lon' => '106.261155',
            'nama' => 'Jembatan Test',
            'lintas' => 'LN-01',
            'stasiun1' => 'sta-001',
            'stasiun2' => 'sta-002',
            'no_bh' => '334',
            'arah_bh' => 'Hilir',
            'jenis' => '2',
            'km_hm' => '78+613',
            'catatan' => 'Fixture',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
    }

    private function issueClientToken(array $abilities): array
    {
        $client = ApiClient::factory()->create([
            'allowed_ips' => ['127.0.0.1'],
        ]);

        $token = $client->createToken('bridge-master-test', $abilities, now()->addDay());
        $token->accessToken->forceFill([
            'api_client_id' => $client->id,
        ])->save();

        return [$client, $token->plainTextToken];
    }

    private function apiHeaders(string $token): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ];
    }
}
