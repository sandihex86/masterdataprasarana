<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Database\Seeders\TunnelLookupSeeder;
use Database\Seeders\WarehouseSourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_root(): void
    {
        $this->get('/')
            ->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.type' => 'v3',
            'services.recaptcha.site_key' => 'test-site-key',
            'services.recaptcha.secret_key' => 'test-secret-key',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Master Data Prasarana')
            ->assertSee('Masuk')
            ->assertSee('action="/login"', false)
            ->assertDontSee('example.com')
            ->assertDontSee('https://prasarana.labdata.id')
            ->assertDontSee('Masuk ke sistem.');
    }

    public function test_user_can_login_and_access_dashboard(): void
    {
        $this->seed();
        $user = User::factory()->admin()->create();
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.type' => 'v3',
            'services.recaptcha.site_key' => 'test-site-key',
            'services.recaptcha.secret_key' => 'test-secret-key',
            'services.recaptcha.login_action' => 'login',
            'services.recaptcha.score_threshold' => 0.5,
            'services.recaptcha.verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'action' => 'login',
                'score' => 0.9,
                'challenge_ts' => now()->toIso8601String(),
                'hostname' => 'localhost',
            ]),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'test-recaptcha-token',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('Status Modul')
            ->assertSee('Menu Penting')
            ->assertSee('Dokumentasi API')
            ->assertSee('Admin')
            ->assertSee('Monitoring');
    }

    public function test_operator_can_access_master_data_menu_without_menu_utama_subtitle(): void
    {
        $this->seed();
        $user = User::factory()->operator()->create();

        $response = $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan')
            ->assertOk()
            ->assertSee('Master Data')
            ->assertSee('Jembatan')
            ->assertSee('Terowongan')
            ->assertSee('Gudang')
            ->assertDontSee('<strong>Jalur</strong>', false)
            ->assertDontSee('<strong>Fasilitas Operasional</strong>', false)
            ->assertDontSee('<strong>Sertifikat</strong>', false)
            ->assertDontSee('<p class="nav-title">Integrasi</p>', false)
            ->assertDontSee('Menu Utama');

        $content = $response->getContent();

        $this->assertLessThan(
            strpos($content, 'Terowongan'),
            strpos($content, 'Jembatan'),
        );
        $this->actingAs($user)
            ->get('/dashboard/monitoring')
            ->assertRedirect('/dashboard');
    }

    public function test_admin_can_view_tunnel_records_from_tunnel_database_on_dashboard(): void
    {
        $this->ensureTunnelSchema();
        $this->seed(TunnelLookupSeeder::class);

        $user = User::factory()->admin()->create();

        \App\Models\Tunnel::query()->create([
            'kode_aset' => 'TUN-001',
            'nomor_bh' => 'BH-T-01',
            'nama_terowongan' => 'Terowongan Sasaksaat',
            'id_wilayah_kerja' => 'WK-01',
            'id_lintas' => 'LNT-01',
            'km_hm' => 'KM 143+144',
            'panjang_m' => 949.5,
            'status_operasi' => 'Operasi',
            'status_aset' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan')
            ->assertOk()
            ->assertSee('Terowongan')
            ->assertSee('prasarana_tunnel')
            ->assertSee('<select id="tunnel-wilayah" name="id_wilayah_kerja">', false)
            ->assertSee('<select id="tunnel-lintas" name="id_lintas">', false)
            ->assertSee('BTP KELAS II PALEMBANG')
            ->assertSee('KAMALPIER - KALIANGET')
            ->assertSee('tunnel-row-actions', false)
            ->assertSee('data-tunnel-row-action="delete"', false)
            ->assertSee('data-document-preview-modal', false)
            ->assertSee('File DED/BED/Kajian Teknis')
            ->assertSee('accept="application/pdf,image/*"', false)
            ->assertSee('Edit')
            ->assertSee('Koordinat')
            ->assertDontSee('<span>Tambah</span>', false)
            ->assertDontSee('<span>Import CSV</span>', false)
            ->assertDontSee('<span>Export CSV</span>', false)
            ->assertDontSee('<span>Template CSV</span>', false)
            ->assertSee('data-tunnel-source-app', false);

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/terowongan/source-records')
            ->assertOk()
            ->assertJsonPath('data.0.nama_terowongan', 'Terowongan Sasaksaat')
            ->assertJsonPath('data.0.nomor_bh', 'BH-T-01')
            ->assertJsonPath('meta.tunnel_source.main_table', 'm_tunnels');
    }

    public function test_admin_can_update_tunnel_record_from_dashboard(): void
    {
        $this->ensureTunnelSchema();
        Storage::fake('public');
        $user = User::factory()->admin()->create();

        $tunnel = \App\Models\Tunnel::query()->create([
            'kode_aset' => 'TUN-EDIT-001',
            'nomor_bh' => 'BH-EDIT-001',
            'nama_terowongan' => 'Terowongan Edit',
            'km_hm' => 'KM 3+000',
            'status_operasi' => 'Operasi',
        ]);

        $this->actingAs($user)
            ->patchJson("/dashboard/master-data/terowongan/source-records/{$tunnel->tunnel_id}", [
                'nama_terowongan' => 'Terowongan Edit Baru',
                'lat' => -6.175392,
                'long' => 106.827153,
            ])
            ->assertOk()
            ->assertJsonPath('data.nama_terowongan', 'Terowongan Edit Baru')
            ->assertJsonPath('data.coordinates.lat', -6.175392)
            ->assertJsonPath('data.coordinates.long', 106.827153);

        $this->assertDatabaseHas('m_tunnels', [
            'tunnel_id' => $tunnel->tunnel_id,
            'nama_terowongan' => 'Terowongan Edit Baru',
            'lat' => -6.175392,
            'long' => 106.827153,
        ], 'tunnel');

        $upload = UploadedFile::fake()->create('ded-terowongan.pdf', 128, 'application/pdf');

        $response = $this->actingAs($user)
            ->post("/dashboard/master-data/terowongan/source-records/{$tunnel->tunnel_id}", [
                '_method' => 'PATCH',
                'nama_terowongan' => 'Terowongan Edit Baru',
                'docs' => [
                    'no_ded_bed_kajian_teknis' => 'DED/TUN/2026/001',
                ],
                'docs_files' => [
                    'ded_bed_kajian_teknis' => $upload,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.docs.no_ded_bed_kajian_teknis', 'DED/TUN/2026/001')
            ->assertJsonPath('data.docs.ded_bed_kajian_teknis.file_name', 'ded-terowongan.pdf');

        $path = $response->json('data.docs.ded_bed_kajian_teknis.path');
        $this->assertIsString($path);
        $this->assertStringStartsWith('tunnels/docs/', $path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan/documents/'.$path)
            ->assertOk();

        $this->actingAs($user)
            ->deleteJson("/dashboard/master-data/terowongan/source-records/{$tunnel->tunnel_id}")
            ->assertOk();

        $this->assertNotNull(
            \App\Models\Tunnel::withTrashed()
                ->where('tunnel_id', $tunnel->tunnel_id)
                ->value('deleted_at'),
        );
    }

    public function test_admin_can_create_import_export_and_download_tunnel_csv_template(): void
    {
        $this->ensureTunnelSchema();

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->postJson('/dashboard/master-data/terowongan/source-records', [
                'kode_aset' => 'TUN-DASH-001',
                'nomor_bh' => 'BH-DASH-001',
                'nama_terowongan' => 'Terowongan Dashboard',
                'km_hm' => 'KM 1+000',
                'status_operasi' => 'Operasi',
                'structure' => [
                    'jenis_struktur' => 'Beton',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.nama_terowongan', 'Terowongan Dashboard')
            ->assertJsonPath('data.structure.jenis_struktur', 'Beton');

        $template = $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan/template')
            ->assertOk()
            ->assertDownload('template-terowongan.csv');
        $this->assertStringContainsString('nama_terowongan', $template->streamedContent());

        $export = $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan/export')
            ->assertOk();
        $this->assertStringContainsString('Terowongan Dashboard', $export->streamedContent());

        $csv = implode("\n", [
            'kode_aset,nomor_bh,nama_terowongan,km_hm,status_operasi,structure.jenis_struktur',
            'TUN-IMPORT-001,BH-IMPORT-001,Terowongan Import,KM 2+000,Operasi,Batuan',
        ]);

        $this->actingAs($user)
            ->postJson('/dashboard/master-data/terowongan/import', [
                'file' => UploadedFile::fake()->createWithContent('terowongan.csv', $csv),
            ])
            ->assertOk()
            ->assertJsonPath('data.created', 1);

        $this->assertDatabaseHas('m_tunnels', [
            'kode_aset' => 'TUN-IMPORT-001',
            'nama_terowongan' => 'Terowongan Import',
        ], 'tunnel');
        $this->assertDatabaseHas('m_tunnel_structures', [
            'jenis_struktur' => 'Batuan',
        ], 'tunnel');
    }

    public function test_admin_can_open_each_tunnel_database_table_from_submenu(): void
    {
        $this->ensureTunnelSchema();
        $this->seed(TunnelLookupSeeder::class);
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan')
            ->assertOk()
            ->assertSee('nav-child-link-combine', false)
            ->assertSee('nav-child-link-master', false)
            ->assertSee('nav-child-link-detail', false)
            ->assertSee('nav-child-link-lookup', false)
            ->assertSee('m_tunnels')
            ->assertSee('m_tunnel_structures')
            ->assertSee('m_tunnel_specs')
            ->assertSee('m_tunnel_docs')
            ->assertSee('m_tunnel_lookup_lintas')
            ->assertSee('m_tunnel_lookup_wilayah_kerja')
            ->assertSee('m_tunnel_lookup_wilayah_operasi');

        $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan/tables/m_tunnels')
            ->assertOk()
            ->assertSee('data-tunnel-source-table-app', false)
            ->assertSee('data-tunnel-source-table-coordinate-modal', false)
            ->assertSee('data-tunnel-source-table-coordinate-open', false)
            ->assertSee('data-tunnel-source-table-generated-id', false)
            ->assertSee('data-tunnel-source-table-delete', false)
            ->assertSee('BTP KELAS II PALEMBANG')
            ->assertSee('KAMALPIER - KALIANGET')
            ->assertSee('Template CSV')
            ->assertSee('Import CSV')
            ->assertSee('Export CSV')
            ->assertSee('Tambah')
            ->assertSee('Edit')
            ->assertSee('m_tunnels');

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/terowongan/tables/m_tunnel_specs/rows')
            ->assertOk()
            ->assertJsonPath('meta.tunnel_source_table.table', 'm_tunnel_specs');
    }

    public function test_admin_can_manage_warehouse_source_table_from_dashboard(): void
    {
        $this->ensureWarehouseSchema();
        $this->seed(WarehouseSourceSeeder::class);
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/dashboard/master-data/gudang')
            ->assertOk()
            ->assertSee('Gudang')
            ->assertSee('prasarana_warehouse')
            ->assertSee('data-tunnel-source-table-app', false)
            ->assertSee('data-tunnel-source-table-coordinate-modal', false)
            ->assertSee('data-tunnel-source-table-coordinate-open', false)
            ->assertSee('Template CSV')
            ->assertSee('Import CSV')
            ->assertSee('Export CSV')
            ->assertSee('Tambah')
            ->assertSee('Koordinat');

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/gudang/tables/m_gudang/rows?search=Payakabung')
            ->assertOk()
            ->assertJsonPath('meta.warehouse_source_table.table', 'm_gudang')
            ->assertJsonPath('data.0.data.nama_gudang', 'Gudang Prasarana Perkeretaapian Payakabung');

        $createResponse = $this->actingAs($user)
            ->postJson('/dashboard/master-data/gudang/tables/m_gudang/rows', [
                'data' => [
                    'nama_gudang' => 'Gudang Dashboard',
                    'tipe_gudang' => 'Gudang Utama',
                    'lat' => -6.175392,
                    'long' => 106.827153,
                    'active' => '1',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.data.nama_gudang', 'Gudang Dashboard')
            ->assertJsonPath('data.data.lat', -6.175392)
            ->assertJsonPath('data.data.long', 106.827153);

        $rowKey = $createResponse->json('data.row_key');
        $kodeGudang = $createResponse->json('data.data.kode_gudang');

        $this->assertIsString($kodeGudang);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $kodeGudang);
        $this->assertSame($kodeGudang, $rowKey);
        $this->assertSame($kodeGudang, $createResponse->json('data.data.id_gudang'));

        $this->actingAs($user)
            ->patchJson("/dashboard/master-data/gudang/tables/m_gudang/rows/{$rowKey}", [
                'data' => [
                    'nama_gudang' => 'Gudang Dashboard Baru',
                    'active' => '0',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.data.nama_gudang', 'Gudang Dashboard Baru')
            ->assertJsonPath('data.data.active', 0);

        $this->assertDatabaseHas('m_gudang', [
            'id_gudang' => $kodeGudang,
            'kode_gudang' => $kodeGudang,
            'nama_gudang' => 'Gudang Dashboard Baru',
            'active' => 0,
        ], 'warehouse');

        $this->actingAs($user)
            ->deleteJson("/dashboard/master-data/gudang/tables/m_gudang/rows/{$rowKey}")
            ->assertOk();

        $this->assertNotNull(
            \DB::connection('warehouse')
                ->table('m_gudang')
                ->where('kode_gudang', $kodeGudang)
                ->value('deleted_at'),
        );
    }

    public function test_admin_can_view_seeded_tunnel_lookup_tables_from_dashboard(): void
    {
        $this->ensureTunnelSchema();
        $this->seed(TunnelLookupSeeder::class);
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan/tables/m_tunnel_lookup_wilayah_kerja')
            ->assertOk()
            ->assertSee('Lookup Wilayah Kerja')
            ->assertSee('Template CSV')
            ->assertSee('Import CSV')
            ->assertSee('Export CSV')
            ->assertSee('Tambah');

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/terowongan/tables/m_tunnel_lookup_lintas/rows')
            ->assertOk()
            ->assertJsonPath('meta.tunnel_source_table.table', 'm_tunnel_lookup_lintas')
            ->assertJsonPath('data.0.data.nama', 'Kamalpier - Kalianget');

        $this->assertDatabaseHas('m_tunnel_lookup_wilayah_operasi', [
            'kode' => '1',
            'nama' => 'DAOP I',
            'active' => 1,
        ], 'tunnel');
    }

    public function test_admin_can_create_rows_in_tunnel_database_tables_from_modal_endpoint(): void
    {
        $this->ensureTunnelSchema();
        $user = User::factory()->admin()->create();
        $assetCode = 'TUN-TABLE-'.Str::upper(Str::random(8));

        $createTunnel = $this->actingAs($user)
            ->postJson('/dashboard/master-data/terowongan/tables/m_tunnels/rows', [
                'data' => [
                    'kode_aset' => $assetCode,
                    'nomor_bh' => 'BH-'.$assetCode,
                    'nama_terowongan' => 'Terowongan Modal Tabel',
                    'status_operasi' => 'Operasi',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.table', 'm_tunnels')
            ->assertJsonPath('data.data.nama_terowongan', 'Terowongan Modal Tabel');

        $tunnelId = $createTunnel->json('data.data.tunnel_id');

        $this->actingAs($user)
            ->postJson('/dashboard/master-data/terowongan/tables/m_tunnel_specs/rows', [
                'data' => [
                    'tunnel_id' => $tunnelId,
                    'jumlah_jalur' => 1,
                    'jenis_jalur' => 'Tunggal',
                    'gauge_m' => '1.067',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.table', 'm_tunnel_specs')
            ->assertJsonPath('data.data.tunnel_id', $tunnelId);

        $this->actingAs($user)
            ->patchJson('/dashboard/master-data/terowongan/tables/m_tunnels/rows/'.$createTunnel->json('data.row_key'), [
                'data' => [
                    'nama_terowongan' => 'Terowongan Modal Tabel Edit',
                    'status_operasi' => 'Non Operasi',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.table', 'm_tunnels')
            ->assertJsonPath('data.data.nama_terowongan', 'Terowongan Modal Tabel Edit')
            ->assertJsonPath('data.data.status_operasi', 'Non Operasi');

        $this->actingAs($user)
            ->patchJson('/dashboard/master-data/terowongan/tables/m_tunnel_specs/rows/'.$tunnelId, [
                'data' => [
                    'jenis_jalur' => 'Ganda',
                    'gauge_m' => '1.435',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.table', 'm_tunnel_specs')
            ->assertJsonPath('data.data.jenis_jalur', 'Ganda');

        $this->assertDatabaseHas('m_tunnels', [
            'kode_aset' => $assetCode,
            'nama_terowongan' => 'Terowongan Modal Tabel Edit',
            'status_operasi' => 'Non Operasi',
        ], 'tunnel');
        $this->assertDatabaseHas('m_tunnel_specs', [
            'tunnel_id' => $tunnelId,
            'jenis_jalur' => 'Ganda',
            'gauge_m' => '1.435',
        ], 'tunnel');

        $lookup = $this->actingAs($user)
            ->postJson('/dashboard/master-data/terowongan/tables/m_tunnel_lookup_lintas/rows', [
                'data' => [
                    'kode' => 'TEST - LKP',
                    'nama' => 'Test Lookup Lintasan',
                    'active' => 1,
                    'sort_order' => 99,
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.table', 'm_tunnel_lookup_lintas')
            ->assertJsonPath('data.data.kode', 'TEST - LKP');

        $lookupId = $lookup->json('data.data.id');
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $lookupId);

        $this->actingAs($user)
            ->patchJson('/dashboard/master-data/terowongan/tables/m_tunnel_lookup_lintas/rows/'.$lookupId, [
                'data' => [
                    'nama' => 'Test Lookup Lintasan Edit',
                    'active' => 0,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.data.nama', 'Test Lookup Lintasan Edit')
            ->assertJsonPath('data.data.active', 0);

        $this->assertDatabaseHas('m_tunnel_lookup_lintas', [
            'id' => $lookupId,
            'kode' => 'TEST - LKP',
            'nama' => 'Test Lookup Lintasan Edit',
            'active' => 0,
        ], 'tunnel');

        $this->actingAs($user)
            ->deleteJson('/dashboard/master-data/terowongan/tables/m_tunnel_lookup_lintas/rows/'.$lookupId)
            ->assertOk();

        $this->assertNotNull(
            \Illuminate\Support\Facades\DB::connection('tunnel')
                ->table('m_tunnel_lookup_lintas')
                ->where('id', $lookupId)
                ->value('deleted_at'),
        );
    }

    public function test_admin_can_import_export_and_download_template_for_tunnel_database_tables(): void
    {
        $this->ensureTunnelSchema();
        $user = User::factory()->admin()->create();
        $assetCode = 'TUN-CSV-'.Str::upper(Str::random(8));

        $template = $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan/tables/m_tunnels/template')
            ->assertOk()
            ->assertDownload('template-m_tunnels.csv');
        $this->assertStringContainsString('kode_aset,nomor_bh,nama_terowongan', $template->streamedContent());

        $csv = implode("\n", [
            'kode_aset,nomor_bh,nama_terowongan,km_hm,status_operasi',
            "{$assetCode},BH-{$assetCode},Terowongan Table CSV,KM 9+000,Operasi",
        ]);

        $this->actingAs($user)
            ->postJson('/dashboard/master-data/terowongan/tables/m_tunnels/import', [
                'file' => UploadedFile::fake()->createWithContent('m_tunnels.csv', $csv),
            ])
            ->assertOk()
            ->assertJsonPath('data.created', 1);

        $this->assertDatabaseHas('m_tunnels', [
            'kode_aset' => $assetCode,
            'nama_terowongan' => 'Terowongan Table CSV',
        ], 'tunnel');

        $export = $this->actingAs($user)
            ->get('/dashboard/master-data/terowongan/tables/m_tunnels/export')
            ->assertOk();
        $this->assertStringContainsString('Terowongan Table CSV', $export->streamedContent());
    }

    public function test_dashboard_system_route_requires_authentication(): void
    {
        $this->get('/dashboard/system')
            ->assertRedirect('/login');
    }

    public function test_dashboard_system_route_returns_json_overview_for_authenticated_user(): void
    {
        $this->seed();
        $user = User::factory()->superadmin()->create();

        $this->actingAs($user)
            ->getJson('/dashboard/system')
            ->assertOk()
            ->assertJsonPath('application.name', config('app.name'))
            ->assertJsonPath('application.debug', config('app.debug'))
            ->assertJsonStructure([
                'application',
                'health' => ['status', 'checks'],
                'metrics',
                'modules',
                'infrastructure_domains',
                'user_roles',
                'api_routes',
            ])
            ->assertJsonPath('infrastructure_domains.0.connection', 'core');
    }

    public function test_non_privileged_user_cannot_access_system_snapshot(): void
    {
        $user = User::factory()->viewer()->create();

        $this->actingAs($user)
            ->get('/dashboard/system')
            ->assertForbidden();
    }

    public function test_swagger_docs_require_authentication(): void
    {
        $this->get('/docs/swagger')
            ->assertRedirect('/login');

        $this->get('/docs/openapi')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_swagger_and_openapi_json(): void
    {
        $this->seed();
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/docs/swagger')
            ->assertOk()
            ->assertSee('Master Data API Docs')
            ->assertSee('/docs/openapi');

        $this->actingAs($user)
            ->getJson('/docs/openapi')
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.0')
            ->assertJsonPath('paths./api/v1/health.get.summary', 'Ringkasan health dan readiness aplikasi');
    }

    public function test_seeder_does_not_create_dummy_users(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_swagger_assets_are_accessible(): void
    {
        $this->get('/docs/openapi/asset/swagger-ui.css')
            ->assertOk();

        $this->get('/docs/openapi/asset/swagger-ui-bundle.js')
            ->assertOk();
    }

    private function ensureTunnelSchema(): void
    {
        if (! Schema::connection('tunnel')->hasTable('m_tunnels')) {
            (require base_path('database/migrations/2026_06_13_080000_create_tunnel_source_tables.php'))->up();
        }

        if (! Schema::connection('tunnel')->hasTable('m_tunnel_lookup_lintas')) {
            (require base_path('database/migrations/2026_06_13_090000_create_tunnel_lookup_tables.php'))->up();
        }
    }

    private function ensureWarehouseSchema(): void
    {
        if (! Schema::connection('warehouse')->hasTable('m_gudang')) {
            (require base_path('database/migrations/2026_06_14_000000_create_warehouse_source_tables.php'))->up();
        }
    }
}
