<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BridgeSourceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'master-data.bridge_source.connection' => config('database.default'),
        ]);

        $this->seedBridgeSourceFixtures();
    }

    public function test_bridge_source_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/bridges')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'AUTHENTICATION_REQUIRED');
    }

    public function test_api_client_can_read_bridge_source_metadata_index_and_detail(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/bridges/metadata')
            ->assertOk()
            ->assertJsonPath('data.source_table', 'm_jembatan')
            ->assertJsonPath('data.record_count', 1)
            ->assertJsonPath('data.relation_map.0.table', 'm_jembatan');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/bridges?search=Citeras')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uniqid', '6498347da7db7')
            ->assertJsonPath('data.0.headline.bridge_number', '334')
            ->assertJsonPath('data.0.headline.route_summary', 'Citeras -> Rangkasbitung')
            ->assertJsonPath('data.0.headline.work_area', 'BTP Kelas I Jakarta');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/bridges/6498347da7db7')
            ->assertOk()
            ->assertJsonPath('data.headline.connected_tables_count', 6)
            ->assertJsonPath('data.headline.assessment_total', 4)
            ->assertJsonPath('data.identity_location.bridge_number', '334')
            ->assertJsonPath('data.identity_location.km_hm', '78+613')
            ->assertJsonPath('data.territory_route.wilayah_kerja.display', 'BTP Kelas I Jakarta (62e60276e589f)')
            ->assertJsonPath('data.territory_route.stasiun_awal.display', 'Citeras (62dd01682ed32)')
            ->assertJsonPath('data.territory_route.stasiun_akhir.display', 'Rangkasbitung (62dd0168399bb)')
            ->assertJsonPath('data.profile.perpotongan', 'Sungai')
            ->assertJsonPath('data.spans.0.pjg_bentang', '20')
            ->assertJsonPath('data.substructures.1.material', 'Beton')
            ->assertJsonPath('data.protection.pelindung_longsoran_tipe', 'Dinding Penahan Tanah')
            ->assertJsonPath('data.assessment.kesimpulan_label', 'Rusak Berat');
    }

    public function test_api_client_can_crud_bridge_source_record(): void
    {
        [, $token] = $this->issueClientToken([
            'master-data:read',
            'master-data:write',
            'master-data:delete',
        ]);

        $storePayload = [
            'uniqid' => 'bridge-api-9001',
            'tanggal' => '2026-06-12',
            'wil_ker' => '62e60276e589f',
            'id_prov' => '31',
            'id_kabkot' => '3171',
            'wil_op' => '1',
            'lat' => '-6.1001',
            'lon' => '106.8002',
            'nama' => 'Jembatan API Baru',
            'lintas' => 'LN-01',
            'stasiun1' => '62dd01682ed32',
            'stasiun2' => '62dd0168399bb',
            'no_bh' => '9001',
            'arah_bh' => 'Hulu',
            'jenis' => '2',
            'km_hm' => '10+200',
            'catatan' => 'Dibuat dari API test.',
            'active' => 1,
            'status' => 1,
            'statusdata' => 0,
            'profile' => [
                'perpotongan' => 'Jalan Raya',
                'jml_lintasan' => 2,
                'jml_bentang' => 2,
                'pjg_bentang1' => '15',
                'pjg_bentang2' => '18',
                'pjg_total' => '33',
                'thn_selesai' => '2025',
                'rm_bgn_atas' => '7',
                'rm_bgn_bawah' => '11',
                'active' => 1,
            ],
            'spans' => [
                ['pjg_bentang' => '15', 'urut' => 1, 'active' => 1],
                ['pjg_bentang' => '18', 'urut' => 2, 'active' => 1],
            ],
            'substructures' => [
                ['nomor' => '1', 'material' => 'Beton', 'tipe' => 'Dinding', 'manteling' => '-', 'jenis' => '-', 'urut' => 1],
            ],
            'protection' => [
                'pelindung_longsoran_material' => 'Batu Kali',
                'pelindung_longsoran_tipe' => 'Dinding Penahan Tanah',
            ],
            'assessment' => [
                'total' => 3.5,
                'kesimpulan' => 3,
            ],
        ];

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/bridges', $storePayload)
            ->assertCreated()
            ->assertJsonPath('data.uniqid', 'bridge-api-9001')
            ->assertJsonPath('data.profile.pjg_total', '33')
            ->assertJsonPath('data.assessment.kesimpulan_label', 'Rusak Ringan');

        $this->assertDatabaseHas('m_jembatan', [
            'uniqid' => 'bridge-api-9001',
            'no_bh' => '9001',
            'nama' => 'Jembatan API Baru',
        ]);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->patchJson('/api/v1/bridges/bridge-api-9001', [
                'no_bh' => '9001-REV',
                'statusdata' => 1,
                'assessment' => [
                    'total' => 2,
                    'kesimpulan' => 2,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.identity_location.bridge_number', '9001-REV')
            ->assertJsonPath('data.assessment.kesimpulan_label', 'Sedang');

        $this->assertDatabaseHas('m_jembatan_nilai_total', [
            'id_jembatan' => 'bridge-api-9001',
            'total' => 2,
            'kesimpulan' => 2,
        ]);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->deleteJson('/api/v1/bridges/bridge-api-9001')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('m_jembatan', [
            'uniqid' => 'bridge-api-9001',
            'active' => 0,
            'status' => 0,
            'statusdata' => 0,
        ]);
    }

    public function test_api_client_can_inspect_schema_and_crud_source_lookup_tables(): void
    {
        [, $token] = $this->issueClientToken([
            'master-data:read',
            'master-data:write',
            'master-data:delete',
        ]);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/bridge-source/tables')
            ->assertOk()
            ->assertJsonPath('data.0.table', 'm_kabkot');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/bridge-source/tables/m_kabkot/schema')
            ->assertOk()
            ->assertJsonPath('data.table', 'm_kabkot')
            ->assertJsonFragment(['name' => 'kode'])
            ->assertJsonFragment(['name' => 'nama']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/v1/bridge-source/tables/m_wilayah_kerja/records', [
                'data' => [
                    'uniqid' => 'wilker-api-001',
                    'kode' => 'WK-API',
                    'nama' => 'Wilayah Kerja API',
                    'active' => 1,
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.row_key', 'wilker-api-001')
            ->assertJsonPath('data.data.nama', 'Wilayah Kerja API');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/bridge-source/tables/m_wilayah_kerja/records?search=API')
            ->assertOk()
            ->assertJsonPath('data.0.data.kode', 'WK-API');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->patchJson('/api/v1/bridge-source/tables/m_wilayah_kerja/records/wilker-api-001', [
                'data' => [
                    'nama' => 'Wilayah Kerja API Revisi',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.data.nama', 'Wilayah Kerja API Revisi');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/bridge-source/tables/m_wilayah_kerja/records/wilker-api-001')
            ->assertOk()
            ->assertJsonPath('data.row_key', 'wilker-api-001')
            ->assertJsonPath('data.data.nama', 'Wilayah Kerja API Revisi');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->deleteJson('/api/v1/bridge-source/tables/m_wilayah_kerja/records/wilker-api-001')
            ->assertOk();

        $this->assertDatabaseMissing('m_wilayah_kerja', [
            'uniqid' => 'wilker-api-001',
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

        $token = $client->createToken('bridge-source-test', $abilities, $tokenExpiresAt ?? now()->addDay());
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

    private function seedBridgeSourceFixtures(): void
    {
        DB::table('m_wilayah_kerja')->insert([
            'uniqid' => '62e60276e589f',
            'kode' => 'WK-JKT',
            'nama' => 'BTP Kelas I Jakarta',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);

        DB::table('m_wilayah_operasi')->insert([
            'uniqid' => 'wilop-1',
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
            'uniqid' => 'kabkot-3171',
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
            'uniqid' => 'lintas-01',
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
                'uniqid' => '62dd01682ed32',
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
                'uniqid' => '62dd0168399bb',
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
            'id' => 2450,
            'uniqid' => '6498347da7db7',
            'tanggal' => '2023-06-25',
            'wil_ker' => '62e60276e589f',
            'id_prov' => '31',
            'id_kabkot' => '3171',
            'wil_op' => null,
            'lat' => '-6.352918',
            'lon' => '106.261155',
            'nama' => '',
            'lintas' => null,
            'stasiun1' => '62dd01682ed32',
            'stasiun2' => '62dd0168399bb',
            'no_bh' => '334',
            'arah_bh' => 'Hilir',
            'jenis' => '2',
            'km_hm' => '78+613',
            'foto1' => 'jembatan11687696509.jpg',
            'foto2' => 'jembatan21687696509.jpg',
            'foto3' => 'jembatan31687696509.jpg',
            'foto4' => 'jembatan41687696509.jpg',
            'caption1' => null,
            'caption2' => null,
            'caption3' => null,
            'caption4' => null,
            'dokumen' => null,
            'video' => null,
            'catatan' => null,
            'active' => 1,
            'status' => 1,
            'statusdata' => 0,
            'created_by' => 'aries w',
            'created_at' => '2023-05-10 08:04:05',
            'updated_by' => 'aries w',
            'updated_at' => '2023-06-25 12:35:09',
            'deleted_at' => null,
        ]);

        DB::table('m_jembatan_profil')->insert([
            'id' => 8763,
            'uniqid' => '6498347dada10',
            'id_jembatan' => '6498347da7db7',
            'perpotongan' => 'Sungai',
            'jml_lintasan' => 0,
            'jml_bentang' => 1,
            'pjg_bentang1' => null,
            'pjg_bentang2' => null,
            'pjg_bentang3' => null,
            'pjg_total' => '20',
            'thn_selesai' => '2008',
            'rm_bgn_atas' => '1921',
            'rm_bgn_bawah' => null,
            'active' => 1,
            'created_by' => 'aries w',
            'created_at' => '2023-06-25 12:35:09',
            'updated_by' => '',
            'updated_at' => '2023-06-25 12:35:09',
        ]);

        DB::table('m_jembatan_bentang')->insert([
            'id' => 14047,
            'uniqid' => '6498347db0e34',
            'id_jembatan' => '6498347da7db7',
            'pjg_bentang' => '20',
            'urut' => 1,
            'active' => 0,
            'created_by' => 'aries w',
            'created_at' => '2023-06-25 12:35:09',
            'updated_by' => '',
            'updated_at' => '2023-06-25 12:35:09',
        ]);

        DB::table('m_jembatan_bawah')->insert([
            [
                'id' => 14047,
                'uniqid' => '6498347dba4fc',
                'id_jembatan' => '6498347da7db7',
                'nomor' => '1',
                'material' => 'Beton',
                'tipe' => 'Dinding',
                'manteling' => '-',
                'jenis' => '-',
                'urut' => 1,
                'created_by' => 'aries w',
                'created_at' => '2023-06-25 12:35:09',
                'updated_by' => '',
                'updated_at' => '2023-06-25 12:35:09',
            ],
            [
                'id' => 14048,
                'uniqid' => '6498347dc014f',
                'id_jembatan' => '6498347da7db7',
                'nomor' => '2',
                'material' => 'Beton',
                'tipe' => 'Dinding',
                'manteling' => '-',
                'jenis' => '-',
                'urut' => 2,
                'created_by' => 'aries w',
                'created_at' => '2023-06-25 12:35:09',
                'updated_by' => '',
                'updated_at' => '2023-06-25 12:35:09',
            ],
        ]);

        DB::table('m_jembatan_detil_3')->insert([
            'id' => 2450,
            'uniqid' => '6498347dc53d4',
            'id_jembatan' => '6498347da7db7',
            'pelindung_arus_material' => null,
            'pelindung_arus_tipe' => null,
            'pengarah_arus_material' => null,
            'pengarah_arus_tipe' => null,
            'pelindung_longsoran_material' => 'Batu Kali',
            'pelindung_longsoran_tipe' => 'Dinding Penahan Tanah',
            'created_by' => 'aries w',
            'created_at' => '2023-06-25 12:35:09',
            'updated_by' => '',
            'updated_at' => '2023-06-25 12:35:09',
        ]);

        DB::table('m_jembatan_nilai_total')->insert([
            'id' => 2450,
            'uniqid' => '6498347dd57f4',
            'id_jembatan' => '6498347da7db7',
            'total' => 4,
            'kesimpulan' => 4,
            'created_by' => 'aries w',
            'created_at' => '2023-06-25 12:35:09',
            'updated_by' => '',
            'updated_at' => '2023-06-25 12:35:09',
        ]);
    }
}
