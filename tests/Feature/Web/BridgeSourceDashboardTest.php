<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Services\BridgeSource\BridgeSourceCrudService;
use App\Services\BridgeSource\BridgeSourceDumpService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class BridgeSourceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'master-data.bridge_source.connection' => config('database.default'),
        ]);

        $this->createBridgeSourceTables();
        $this->seedBridgeSourceFixtures();
    }

    public function test_admin_can_manage_bridge_source_records_from_dashboard(): void
    {
        $user = User::factory()->admin()->create([
            'name' => 'Admin Bridge',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/master-data/jembatan')
            ->assertOk()
            ->assertSee('nav-child-link-combine', false)
            ->assertSee('nav-child-link-master', false)
            ->assertSee('nav-child-link-detail', false)
            ->assertSee('nav-child-link-lookup', false)
            ->assertSee('Relasi Tabel Source Jembatan')
            ->assertSee('m_jembatan')
            ->assertSee('m_jembatan_bentang');

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/jembatan/source-records')
            ->assertOk()
            ->assertJsonPath('data.0.uniqid', 'bridge-source-001')
            ->assertJsonPath('data.0.wil_ker_name', 'Wilker Alpha')
            ->assertJsonPath('data.0.stasiun1_name', 'Stasiun Hulu');

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/jembatan/source-records/bridge-source-001')
            ->assertOk()
            ->assertJsonPath('data.profile.perpotongan', 'Sungai')
            ->assertJsonPath('data.spans.0.pjg_bentang', '20')
            ->assertJsonPath('data.protection.pelindung_arus_tipe', 'Beton');

        $payload = [
            'tanggal' => '2026-06-12',
            'wil_ker' => 'wilker-alpha',
            'id_prov' => '31',
            'id_kabkot' => '3171',
            'wil_op' => '1',
            'lat' => '-6.1001',
            'lon' => '106.8002',
            'nama' => 'Jembatan Baru',
            'lintas' => 'LN-01',
            'stasiun1' => 'ST01',
            'stasiun2' => 'ST02',
            'no_bh' => 'BH-9001',
            'arah_bh' => 'Hulu',
            'jenis' => 'Rangka Baja',
            'km_hm' => '10+200',
            'catatan' => 'Data baru dari dashboard source.',
            'active' => 1,
            'status' => 1,
            'statusdata' => 1,
            'profile' => [
                'perpotongan' => 'Jalan Raya',
                'jml_lintasan' => 2,
                'jml_bentang' => 2,
                'pjg_bentang1' => '15',
                'pjg_bentang2' => '18',
                'pjg_bentang3' => null,
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
                ['nomor' => 'P1', 'material' => 'Beton', 'tipe' => 'Pilar', 'manteling' => 'Baik', 'jenis' => 'Dalam', 'urut' => 1],
            ],
            'protection' => [
                'pelindung_arus_material' => 'Batu',
                'pelindung_arus_tipe' => 'Bronjong',
                'pengarah_arus_material' => 'Beton',
                'pengarah_arus_tipe' => 'Guide Wall',
                'pelindung_longsoran_material' => 'Geotekstil',
                'pelindung_longsoran_tipe' => 'Soil Nailing',
            ],
            'assessment' => [
                'total' => 88.5,
                'kesimpulan' => 1,
            ],
        ];

        $createResponse = $this->actingAs($user)
            ->postJson('/dashboard/master-data/jembatan/source-records', $payload)
            ->assertCreated()
            ->assertJsonPath('data.no_bh', 'BH-9001')
            ->assertJsonPath('data.profile.jml_bentang', 2);

        $createdUniqid = $createResponse->json('data.uniqid');

        $this->assertDatabaseHas('m_jembatan', [
            'uniqid' => $createdUniqid,
            'no_bh' => 'BH-9001',
            'nama' => 'Jembatan Baru',
        ]);
        $this->assertDatabaseHas('m_jembatan_profil', [
            'id_jembatan' => $createdUniqid,
            'pjg_total' => '33',
        ]);
        $this->assertDatabaseCount('m_jembatan_bentang', 4);

        $this->actingAs($user)
            ->patchJson("/dashboard/master-data/jembatan/source-records/{$createdUniqid}", [
                'no_bh' => 'BH-9001-REV',
                'jenis' => 'Beton Bertulang',
                'statusdata' => 0,
                'profile' => [
                    'perpotongan' => 'Sungai Besar',
                    'jml_lintasan' => 1,
                    'jml_bentang' => 1,
                    'pjg_bentang1' => '22',
                    'pjg_bentang2' => null,
                    'pjg_bentang3' => null,
                    'pjg_total' => '22',
                    'thn_selesai' => '2026',
                    'rm_bgn_atas' => '9',
                    'rm_bgn_bawah' => '12',
                    'active' => 1,
                ],
                'spans' => [
                    ['pjg_bentang' => '22', 'urut' => 1, 'active' => 1],
                ],
                'substructures' => [
                    ['nomor' => 'A1', 'material' => 'Baja', 'tipe' => 'Abutmen', 'manteling' => 'Cukup', 'jenis' => 'Dangkal', 'urut' => 1],
                ],
                'protection' => [
                    'pelindung_arus_material' => 'Beton',
                    'pelindung_arus_tipe' => 'Dinding Penahan',
                    'pengarah_arus_material' => 'Batu',
                    'pengarah_arus_tipe' => 'Susunan Batu',
                    'pelindung_longsoran_material' => 'Wiremesh',
                    'pelindung_longsoran_tipe' => 'Shotcrete',
                ],
                'assessment' => [
                    'total' => 91.25,
                    'kesimpulan' => 2,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.no_bh', 'BH-9001-REV')
            ->assertJsonPath('data.assessment.total', 91.25);

        $this->assertDatabaseHas('m_jembatan', [
            'uniqid' => $createdUniqid,
            'no_bh' => 'BH-9001-REV',
            'jenis' => 'Beton Bertulang',
            'statusdata' => 0,
        ]);
        $this->assertDatabaseHas('m_jembatan_nilai_total', [
            'id_jembatan' => $createdUniqid,
            'total' => 91.25,
            'kesimpulan' => 2,
        ]);
        $this->assertSame(
            1,
            DB::table('m_jembatan_bentang')->where('id_jembatan', $createdUniqid)->count(),
        );
        $this->assertSame(
            '22',
            DB::table('m_jembatan_bentang')->where('id_jembatan', $createdUniqid)->value('pjg_bentang'),
        );

        $this->actingAs($user)
            ->deleteJson("/dashboard/master-data/jembatan/source-records/{$createdUniqid}")
            ->assertOk();

        $this->assertDatabaseHas('m_jembatan', [
            'uniqid' => $createdUniqid,
            'active' => 0,
            'status' => 0,
            'statusdata' => 0,
        ]);
        $this->assertNotNull(DB::table('m_jembatan')->where('uniqid', $createdUniqid)->value('deleted_at'));
    }

    public function test_admin_can_open_bridge_source_sql_dump_table_submenu(): void
    {
        $user = User::factory()->admin()->create([
            'name' => 'Admin Dump',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/master-data/jembatan/tables/m_jembatan')
            ->assertOk()
            ->assertSee('m_jembatan')
            ->assertSee('Data utama jembatan hasil survey source.');

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/jembatan/tables/m_jembatan/rows?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.0.no_bh', 'BH-100')
            ->assertJsonPath('meta.pagination.per_page', 5);
    }

    public function test_admin_can_load_bridge_source_page_from_dump_fallback_when_database_source_is_unavailable(): void
    {
        $user = User::factory()->admin()->create([
            'name' => 'Admin Fallback',
        ]);

        $crudService = Mockery::mock(BridgeSourceCrudService::class);
        $crudService->shouldReceive('isDatabaseSourceAvailable')->andReturn(false);
        $crudService->shouldReceive('relationMap')->andReturn([
            [
                'table' => 'm_jembatan',
                'type' => 'root',
                'relation' => 'Induk data jembatan',
                'key' => 'uniqid',
                'target' => null,
            ],
        ]);

        $dumpService = Mockery::mock(BridgeSourceDumpService::class);
        $dumpService->shouldReceive('countCombined')->andReturn(1);
        $dumpService->shouldReceive('tables')->andReturn([]);
        $dumpService->shouldReceive('paginateCombined')->andReturn(new LengthAwarePaginator(
            [[
                'uniqid' => 'bridge-dump-001',
                'bridge_identity' => 'BH-001 | Jembatan Uji | Baja',
                'route_summary' => 'Stasiun A -> Stasiun B | Lintas Utama',
                'wilayah_summary' => 'Wilker Alpha | Operasi I',
                'location_summary' => 'KM/HM 10+200 | Jawa Barat, Bandung',
                'structure_summary' => '2 bentang | 1 struktur bawah',
                'assessment_summary' => 'nilai 91.5 | kesimpulan 1',
                'updated_at' => '2026-06-12 00:00:00',
            ]],
            1,
            25,
            1,
        ));
        $dumpService->shouldReceive('findCombined')->andReturn([
            'uniqid' => 'bridge-dump-001',
            'no_bh' => 'BH-001',
        ]);

        $this->instance(BridgeSourceCrudService::class, $crudService);
        $this->instance(BridgeSourceDumpService::class, $dumpService);

        $this->actingAs($user)
            ->get('/dashboard/master-data/jembatan')
            ->assertOk()
            ->assertSee('Source database belum tersedia');

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/jembatan/source-records')
            ->assertOk()
            ->assertJsonPath('meta.bridge_source.data_mode', 'dump')
            ->assertJsonPath('data.0.uniqid', 'bridge-dump-001');
    }

    public function test_viewer_cannot_access_bridge_source_dashboard_endpoints(): void
    {
        $user = User::factory()->viewer()->create();

        $this->actingAs($user)
            ->getJson('/dashboard/master-data/jembatan/source-records')
            ->assertForbidden();
    }

    private function createBridgeSourceTables(): void
    {
        if (Schema::hasTable('m_jembatan')) {
            return;
        }

        Schema::create('m_jembatan', function (Blueprint $table): void {
            $table->id();
            $table->string('uniqid', 64)->unique();
            $table->date('tanggal')->nullable();
            $table->string('wil_ker', 255)->nullable();
            $table->string('id_prov', 32)->nullable();
            $table->string('id_kabkot', 32)->nullable();
            $table->string('wil_op', 32)->nullable();
            $table->string('lat', 32)->default('');
            $table->string('lon', 32)->default('');
            $table->string('nama', 255)->default('');
            $table->string('lintas', 64)->nullable();
            $table->string('stasiun1', 64)->nullable();
            $table->string('stasiun2', 64)->nullable();
            $table->string('no_bh', 32)->nullable();
            $table->string('arah_bh', 255)->nullable();
            $table->string('jenis', 255)->nullable();
            $table->string('km_hm', 16)->nullable();
            $table->string('foto1', 255)->nullable();
            $table->string('foto2', 255)->nullable();
            $table->string('foto3', 255)->nullable();
            $table->string('foto4', 255)->nullable();
            $table->string('caption1', 255)->nullable();
            $table->string('caption2', 255)->nullable();
            $table->string('caption3', 255)->nullable();
            $table->string('caption4', 255)->nullable();
            $table->string('dokumen', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->integer('active')->default(1);
            $table->integer('status')->default(1);
            $table->integer('statusdata')->default(0);
            $table->string('created_by', 64)->default('');
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by', 64)->default('');
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('m_jembatan_profil', function (Blueprint $table): void {
            $table->id();
            $table->string('uniqid', 64);
            $table->string('id_jembatan', 64);
            $table->string('perpotongan', 255)->nullable();
            $table->tinyInteger('jml_lintasan')->nullable();
            $table->tinyInteger('jml_bentang')->nullable();
            $table->string('pjg_bentang1', 16)->nullable();
            $table->string('pjg_bentang2', 16)->nullable();
            $table->string('pjg_bentang3', 16)->nullable();
            $table->string('pjg_total', 32)->nullable();
            $table->string('thn_selesai', 4)->nullable();
            $table->string('rm_bgn_atas', 16)->nullable();
            $table->string('rm_bgn_bawah', 16)->nullable();
            $table->integer('active')->default(1);
            $table->string('created_by', 64)->default('');
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by', 64)->default('');
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('m_jembatan_bentang', function (Blueprint $table): void {
            $table->id();
            $table->string('uniqid', 64);
            $table->string('id_jembatan', 64);
            $table->string('pjg_bentang', 16)->nullable();
            $table->tinyInteger('urut')->default(1);
            $table->integer('active')->default(1);
            $table->string('created_by', 64)->default('');
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by', 64)->default('');
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('m_jembatan_bawah', function (Blueprint $table): void {
            $table->id();
            $table->string('uniqid', 64);
            $table->string('id_jembatan', 64);
            $table->string('created_by', 64)->default('');
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by', 64)->default('');
            $table->timestamp('updated_at')->nullable();
            $table->string('nomor', 255)->nullable();
            $table->string('material', 255)->nullable();
            $table->string('tipe', 255)->nullable();
            $table->string('manteling', 255)->nullable();
            $table->string('jenis', 255)->nullable();
            $table->tinyInteger('urut')->default(1);
        });

        Schema::create('m_jembatan_detil_3', function (Blueprint $table): void {
            $table->id();
            $table->string('uniqid', 64);
            $table->string('id_jembatan', 64);
            $table->string('created_by', 64)->default('');
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by', 64)->default('');
            $table->timestamp('updated_at')->nullable();
            $table->string('pelindung_arus_material', 255)->nullable();
            $table->string('pelindung_arus_tipe', 255)->nullable();
            $table->string('pengarah_arus_material', 255)->nullable();
            $table->string('pengarah_arus_tipe', 255)->nullable();
            $table->string('pelindung_longsoran_material', 255)->nullable();
            $table->string('pelindung_longsoran_tipe', 255)->nullable();
        });

        Schema::create('m_jembatan_nilai_total', function (Blueprint $table): void {
            $table->id();
            $table->string('uniqid', 64);
            $table->string('id_jembatan', 64);
            $table->string('created_by', 64)->default('');
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by', 64)->default('');
            $table->timestamp('updated_at')->nullable();
            $table->float('total')->nullable();
            $table->tinyInteger('kesimpulan')->nullable();
        });

        foreach (['m_provinsi', 'm_kabkot', 'm_lintas', 'm_stasiun', 'm_wilayah_kerja', 'm_wilayah_operasi'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName): void {
                $table->id();
                $table->string('uniqid', 64)->nullable();
                if ($tableName === 'm_kabkot') {
                    $table->string('id_prov', 32)->nullable();
                }
                if ($tableName === 'm_stasiun') {
                    $table->string('id_prov', 32)->nullable();
                    $table->string('id_kabkot', 32)->nullable();
                    $table->string('lat', 32)->nullable();
                    $table->string('lon', 32)->nullable();
                    $table->string('wil_op', 32)->nullable();
                }
                $table->string('kode', 255)->nullable();
                $table->string('nama', 255)->nullable();
                $table->integer('active')->default(1);
                $table->string('created_by', 64)->default('');
                $table->timestamp('created_at')->nullable();
                $table->string('updated_by', 64)->default('');
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    private function seedBridgeSourceFixtures(): void
    {
        DB::table('m_wilayah_kerja')->insert([
            'uniqid' => 'wilker-alpha',
            'kode' => 'WK-01',
            'nama' => 'Wilker Alpha',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_wilayah_operasi')->insert([
            'uniqid' => 'wilop-alpha',
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
            'uniqid' => 'lintas-alpha',
            'kode' => 'LN-01',
            'nama' => 'Lintas Utama',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
        DB::table('m_stasiun')->insert([
            [
                'uniqid' => 'station-hulu',
                'id_prov' => '31',
                'id_kabkot' => '3171',
                'kode' => 'ST01',
                'nama' => 'Stasiun Hulu',
                'wil_op' => '1',
                'active' => 1,
                'created_by' => 'Seeder',
                'created_at' => now(),
                'updated_by' => 'Seeder',
                'updated_at' => now(),
            ],
            [
                'uniqid' => 'station-hilir',
                'id_prov' => '31',
                'id_kabkot' => '3171',
                'kode' => 'ST02',
                'nama' => 'Stasiun Hilir',
                'wil_op' => '1',
                'active' => 1,
                'created_by' => 'Seeder',
                'created_at' => now(),
                'updated_by' => 'Seeder',
                'updated_at' => now(),
            ],
        ]);

        DB::table('m_jembatan')->insert([
            'uniqid' => 'bridge-source-001',
            'tanggal' => '2026-06-01',
            'wil_ker' => 'wilker-alpha',
            'id_prov' => '31',
            'id_kabkot' => '3171',
            'wil_op' => '1',
            'lat' => '-6.2',
            'lon' => '106.8',
            'nama' => 'Jembatan Sumber',
            'lintas' => 'LN-01',
            'stasiun1' => 'ST01',
            'stasiun2' => 'ST02',
            'no_bh' => 'BH-100',
            'arah_bh' => 'Hilir',
            'jenis' => 'Baja',
            'km_hm' => '5+100',
            'catatan' => 'Fixture awal.',
            'active' => 1,
            'status' => 1,
            'statusdata' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);

        DB::table('m_jembatan_profil')->insert([
            'uniqid' => 'profile-source-001',
            'id_jembatan' => 'bridge-source-001',
            'perpotongan' => 'Sungai',
            'jml_lintasan' => 1,
            'jml_bentang' => 2,
            'pjg_bentang1' => '20',
            'pjg_bentang2' => '25',
            'pjg_total' => '45',
            'thn_selesai' => '2020',
            'rm_bgn_atas' => '8',
            'rm_bgn_bawah' => '12',
            'active' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);

        DB::table('m_jembatan_bentang')->insert([
            [
                'uniqid' => 'span-source-001',
                'id_jembatan' => 'bridge-source-001',
                'pjg_bentang' => '20',
                'urut' => 1,
                'active' => 1,
                'created_by' => 'Seeder',
                'created_at' => now(),
                'updated_by' => 'Seeder',
                'updated_at' => now(),
            ],
            [
                'uniqid' => 'span-source-002',
                'id_jembatan' => 'bridge-source-001',
                'pjg_bentang' => '25',
                'urut' => 2,
                'active' => 1,
                'created_by' => 'Seeder',
                'created_at' => now(),
                'updated_by' => 'Seeder',
                'updated_at' => now(),
            ],
        ]);

        DB::table('m_jembatan_bawah')->insert([
            'uniqid' => 'sub-source-001',
            'id_jembatan' => 'bridge-source-001',
            'nomor' => 'P1',
            'material' => 'Beton',
            'tipe' => 'Pilar',
            'manteling' => 'Baik',
            'jenis' => 'Dalam',
            'urut' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);

        DB::table('m_jembatan_detil_3')->insert([
            'uniqid' => 'protect-source-001',
            'id_jembatan' => 'bridge-source-001',
            'pelindung_arus_material' => 'Batu',
            'pelindung_arus_tipe' => 'Beton',
            'pengarah_arus_material' => 'Baja',
            'pengarah_arus_tipe' => 'Guide',
            'pelindung_longsoran_material' => 'Beton',
            'pelindung_longsoran_tipe' => 'Shotcrete',
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);

        DB::table('m_jembatan_nilai_total')->insert([
            'uniqid' => 'assessment-source-001',
            'id_jembatan' => 'bridge-source-001',
            'total' => 92.5,
            'kesimpulan' => 1,
            'created_by' => 'Seeder',
            'created_at' => now(),
            'updated_by' => 'Seeder',
            'updated_at' => now(),
        ]);
    }
}
