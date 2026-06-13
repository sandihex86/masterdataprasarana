<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TunnelApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::connection('tunnel')->hasTable('m_tunnels')) {
            (require base_path('database/migrations/2026_06_13_080000_create_tunnel_source_tables.php'))->up();
        }
    }

    public function test_tunnel_index_requires_authentication(): void
    {
        $this->getJson('/api/tunnels')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'AUTHENTICATION_REQUIRED');
    }

    public function test_api_client_can_crud_tunnel_with_nested_details(): void
    {
        [, $token] = $this->issueClientToken([
            'master-data:read',
            'master-data:write',
            'master-data:delete',
        ]);

        $createResponse = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/tunnels', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.kode_aset', 'PRAS-04-01')
            ->assertJsonPath('data.nama_terowongan', 'Sasaksaat')
            ->assertJsonPath('data.structure.material_lining', 'Beton')
            ->assertJsonPath('data.specs.jumlah_jalur', 1)
            ->assertJsonPath('data.docs.no_ded_bed_kajian_teknis', 'DED/TUNNEL/2024/001');

        $tunnelId = $createResponse->json('data.tunnel_id');

        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $tunnelId);
        $this->assertDatabaseHas('m_tunnels', [
            'tunnel_id' => $tunnelId,
            'kode_aset' => 'PRAS-04-01',
            'nama_terowongan' => 'Sasaksaat',
        ], 'tunnel');
        $this->assertDatabaseHas('m_tunnel_structures', [
            'tunnel_id' => $tunnelId,
            'jenis_struktur' => 'Terowongan batu',
        ], 'tunnel');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/tunnels?search=Sasaksaat&id_wilayah_kerja=FK&sort_by=nama_terowongan&sort_dir=asc')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tunnel_id', $tunnelId)
            ->assertJsonPath('meta.pagination.total', 1);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson("/api/tunnels/{$tunnelId}")
            ->assertOk()
            ->assertJsonPath('data.tunnel_id', $tunnelId)
            ->assertJsonPath('data.specs.bentuk_penampang', 'Tapal kuda');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->patchJson("/api/tunnels/{$tunnelId}", [
                'status_aset' => 'Nonaktif',
                'kondisi_terakhir' => 'Sedang',
                'specs' => [
                    'jumlah_jalur' => 2,
                    'jenis_jalur' => 'Ganda',
                    'gauge_m' => 1.067,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.status_aset', 'Nonaktif')
            ->assertJsonPath('data.specs.jumlah_jalur', 2);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->patchJson("/api/tunnels/{$tunnelId}/structure", [
                'material_lining' => 'Shotcrete',
                'waterproofing' => 'Membran',
            ])
            ->assertOk()
            ->assertJsonPath('data.material_lining', 'Shotcrete')
            ->assertJsonPath('data.waterproofing', 'Membran');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->patchJson("/api/tunnels/{$tunnelId}/docs", [
                'no_dok_hasil_uji' => 'UJI/TUNNEL/2026/009',
                'dok_hasil_uji' => [
                    'file_name' => 'uji_sasaksaat.pdf',
                    'path' => 'tunnels/docs/uji_sasaksaat.pdf',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.no_dok_hasil_uji', 'UJI/TUNNEL/2026/009')
            ->assertJsonPath('data.dok_hasil_uji.file_name', 'uji_sasaksaat.pdf');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson("/api/tunnels/{$tunnelId}/docs")
            ->assertOk()
            ->assertJsonPath('data.no_dok_hasil_uji', 'UJI/TUNNEL/2026/009');

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->deleteJson("/api/tunnels/{$tunnelId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('m_tunnels', [
            'tunnel_id' => $tunnelId,
        ], 'tunnel');
    }

    public function test_tunnel_validation_rejects_manual_tunnel_id_and_invalid_numbers(): void
    {
        [, $token] = $this->issueClientToken(['master-data:write']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->postJson('/api/tunnels', [
                'tunnel_id' => '01JY0000000000000000000000',
                'nama_terowongan' => '',
                'tahun_bangunan' => 1700,
                'lat' => -91,
                'long' => 181,
                'panjang_m' => -1,
                'structure' => [
                    'tahun_rehabilitasi_terakhir' => 1700,
                ],
                'specs' => [
                    'jumlah_jalur' => 0,
                    'clearance_horizontal_mm' => 0,
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'details' => [
                        'tunnel_id',
                        'nama_terowongan',
                        'tahun_bangunan',
                        'lat',
                        'long',
                        'panjang_m',
                        'structure.tahun_rehabilitasi_terakhir',
                        'specs.jumlah_jalur',
                        'specs.clearance_horizontal_mm',
                    ],
                ],
            ]);
    }

    public function test_v1_tunnel_alias_is_available(): void
    {
        [, $token] = $this->issueClientToken(['master-data:read']);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders($this->apiHeaders($token))
            ->getJson('/api/v1/tunnels')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'kode_aset' => 'PRAS-04-01',
            'nomor_bh' => '503',
            'nama_terowongan' => 'Sasaksaat',
            'id_wilayah_kerja' => 'FK',
            'id_lintas' => 'FK',
            'km_hm' => '143+144',
            'panjang_m' => 950.00,
            'tahun_bangunan' => 1904,
            'tahun_operasi' => 1906,
            'umur_tahun' => 120,
            'lat' => -6.8321000,
            'long' => 107.4521000,
            'status_operasi' => 'Operasi',
            'status_aset' => 'Aktif',
            'kondisi_terakhir' => 'Baik',
            'tgl_inspeksi_terakhir' => '2026-06-13',
            'structure' => [
                'jenis_struktur' => 'Terowongan batu',
                'material_struktur' => 'Beton',
                'material_lining' => 'Beton',
                'material_portal' => 'Pasangan batu',
                'material_invert' => 'Beton',
                'metode_konstruksi' => 'Konvensional',
                'waterproofing' => 'Ada',
                'tahun_rehabilitasi_terakhir' => 2020,
            ],
            'specs' => [
                'jumlah_jalur' => 1,
                'jenis_jalur' => 'Tunggal',
                'gauge_m' => 1.067,
                'lebar_bersih_m' => 4.50,
                'tinggi_bersih_m' => 5.20,
                'clearance_horizontal_mm' => 4500,
                'clearance_vertikal_mm' => 5200,
                'bentuk_penampang' => 'Tapal kuda',
                'gradien_persen' => 1.25,
                'radius_lengkung_m' => 300.00,
                'catatan_teknis' => 'Data teknis awal dari master tunnel.',
            ],
            'docs' => [
                'no_ded_bed_kajian_teknis' => 'DED/TUNNEL/2024/001',
                'ded_bed_kajian_teknis' => [
                    'file_name' => 'ded_sasaksaat.pdf',
                    'path' => 'tunnels/docs/ded_sasaksaat.pdf',
                ],
                'no_spesifikasi_teknis' => 'SPES/TUNNEL/2024/001',
                'spesifikasi_teknis' => null,
                'no_shop_drawing' => 'SHOP/TUNNEL/2024/001',
                'shop_drawing' => null,
                'no_as_built_drawing' => 'ABD/TUNNEL/2024/001',
                'as_built_drawing' => null,
                'no_dok_hasil_uji' => 'UJI/TUNNEL/2024/001',
                'dok_hasil_uji' => null,
            ],
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

        $token = $client->createToken('tunnel-api-test', $abilities, now()->addDay());
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
