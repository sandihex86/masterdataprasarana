<?php

namespace App\Http\Controllers\Web;

use App\Enums\MasterDataStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMasterDataRequest;
use App\Http\Requests\Api\V1\ListTunnelRequest;
use App\Http\Requests\Api\V1\StoreMasterDataRequest;
use App\Http\Requests\Api\V1\StoreTunnelRequest;
use App\Http\Requests\Api\V1\UpdateMasterDataRequest;
use App\Http\Requests\Api\V1\UpdateTunnelRequest;
use App\Http\Requests\Web\GenerateApiClientTokenRequest;
use App\Http\Requests\Web\ListBridgeSourceRequest;
use App\Http\Requests\Web\ListSuperadminRecordsRequest;
use App\Http\Requests\Web\StoreApiClientRequest;
use App\Http\Requests\Web\StoreBridgeSourceRequest;
use App\Http\Requests\Web\StoreManagedUserRequest;
use App\Http\Requests\Web\UpdateApiClientRequest;
use App\Http\Requests\Web\UpdateBridgeSourceRequest;
use App\Http\Requests\Web\UpdateManagedUserRequest;
use App\Http\Resources\Api\V1\MasterDataResource;
use App\Http\Resources\Api\V1\TunnelDetailResource;
use App\Http\Resources\Api\V1\TunnelResource;
use App\Models\ApiClient;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Models\Tunnel;
use App\Models\User;
use App\Services\BridgeSource\BridgeSourceCrudService;
use App\Services\BridgeSource\BridgeSourceDumpService;
use App\Services\Dashboard\DashboardService;
use App\Services\MasterData\MasterDataQueryService;
use App\Services\MasterData\MasterDataWriteService;
use App\Services\ReferenceSourceTableService;
use App\Services\SuperAdmin\ApiClientManagementService;
use App\Services\SuperAdmin\UserManagementService;
use App\Services\TunnelDocumentUploadService;
use App\Services\TunnelService;
use App\Services\TunnelSourceTableService;
use App\Services\WarehouseSourceTableService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    private const MASTER_DATA_PAGES = [
        'jembatan' => [
            'label' => 'Jembatan',
            'type_code' => 'bridge',
        ],
        'terowongan' => [
            'label' => 'Terowongan',
            'type_code' => 'tunnel',
        ],
        'gudang' => [
            'label' => 'Gudang',
            'type_code' => 'warehouse',
        ],
        'referensi' => [
            'label' => 'Referensi',
            'type_code' => 'reference',
        ],
    ];

    private const SUPERADMIN_PAGES = [
        'superadmin-users',
        'superadmin-api-clients',
    ];

    private const TUNNEL_CSV_COLUMNS = [
        'kode_aset',
        'nomor_bh',
        'nama_terowongan',
        'id_wilayah_kerja',
        'id_lintas',
        'km_hm',
        'panjang_m',
        'tahun_bangunan',
        'tahun_operasi',
        'umur_tahun',
        'lat',
        'long',
        'status_operasi',
        'status_aset',
        'kondisi_terakhir',
        'tgl_inspeksi_terakhir',
        'structure.jenis_struktur',
        'structure.material_struktur',
        'structure.material_lining',
        'structure.material_portal',
        'structure.material_invert',
        'structure.metode_konstruksi',
        'structure.waterproofing',
        'structure.tahun_rehabilitasi_terakhir',
        'specs.jumlah_jalur',
        'specs.jenis_jalur',
        'specs.gauge_m',
        'specs.lebar_bersih_m',
        'specs.tinggi_bersih_m',
        'specs.clearance_horizontal_mm',
        'specs.clearance_vertikal_mm',
        'specs.bentuk_penampang',
        'specs.gradien_persen',
        'specs.radius_lengkung_m',
        'specs.catatan_teknis',
        'docs.no_ded_bed_kajian_teknis',
        'docs.ded_bed_kajian_teknis',
        'docs.no_spesifikasi_teknis',
        'docs.spesifikasi_teknis',
        'docs.no_shop_drawing',
        'docs.shop_drawing',
        'docs.no_as_built_drawing',
        'docs.as_built_drawing',
        'docs.no_dok_hasil_uji',
        'docs.dok_hasil_uji',
    ];

    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly MasterDataQueryService $masterDataQueryService,
        private readonly MasterDataWriteService $masterDataWriteService,
        private readonly BridgeSourceCrudService $bridgeSourceCrudService,
        private readonly BridgeSourceDumpService $bridgeSourceDumpService,
        private readonly UserManagementService $userManagementService,
        private readonly ApiClientManagementService $apiClientManagementService,
        private readonly TunnelService $tunnelService,
        private readonly TunnelSourceTableService $tunnelSourceTableService,
        private readonly TunnelDocumentUploadService $tunnelDocumentUploadService,
        private readonly WarehouseSourceTableService $warehouseSourceTableService,
        private readonly ReferenceSourceTableService $referenceSourceTableService,
    ) {}

    public function index(): View
    {
        $user = request()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $this->renderPage($this->defaultPageFor($user));
    }

    public function documentation(): View
    {
        return $this->renderPage('documentation');
    }

    public function quickMenu(): RedirectResponse
    {
        return redirect()->route('dashboard.index');
    }

    public function moduleStatus(): RedirectResponse
    {
        return redirect()->route('dashboard.index');
    }

    public function masterData(): RedirectResponse
    {
        return redirect()->route('dashboard.master-data.entity', [
            'entity' => array_key_first(self::MASTER_DATA_PAGES),
        ]);
    }

    public function masterDataEntity(string $entity): View
    {
        return $this->renderPage('master-data-entity', [
            'masterDataPage' => $this->resolveMasterDataPage($entity),
        ]);
    }

    public function bridgeSourceTable(string $table): View
    {
        return $this->renderPage('bridge-source-table', [
            'bridgeSourceTablePage' => $this->resolveBridgeSourceTablePage($table),
        ]);
    }

    public function tunnelSourceTable(string $table): View
    {
        return $this->renderPage('tunnel-source-table', [
            'tunnelSourceTablePage' => $this->resolveTunnelSourceTablePage($table),
        ]);
    }

    public function warehouseSourceTable(string $table): View
    {
        return $this->renderPage('warehouse-source-table', [
            'warehouseSourceTablePage' => $this->resolveWarehouseSourceTablePage($table),
        ]);
    }

    public function referenceSourceTable(string $table): View
    {
        return $this->renderPage('reference-source-table', [
            'referenceSourceTablePage' => $this->resolveReferenceSourceTablePage($table),
        ]);
    }

    public function monitoring(): RedirectResponse
    {
        return redirect()->route('dashboard.index');
    }

    public function superadminUsers(): View
    {
        return $this->renderPage('superadmin-users', [
            'superadminUserPage' => $this->resolveSuperadminUserPage(),
        ]);
    }

    public function superadminApiClients(): View
    {
        return $this->renderPage('superadmin-api-clients', [
            'superadminApiClientPage' => $this->resolveSuperadminApiClientPage(),
        ]);
    }

    public function system(): JsonResponse
    {
        return response()->json($this->dashboardService->overview());
    }

    public function bridgeMetadataFieldValues(string $field): JsonResponse
    {
        $payload = $this->dashboardService->bridgeFieldUniqueValues($field);

        if ($payload === null) {
            return ApiResponse::error('Field metadata jembatan tidak ditemukan.', 'BRIDGE_METADATA_FIELD_NOT_FOUND', 404);
        }

        return ApiResponse::success('Unique value field berhasil diambil.', $payload);
    }

    public function superadminUserRecords(ListSuperadminRecordsRequest $request): JsonResponse
    {
        $this->ensureSuperadminAccess();
        $records = $this->userManagementService->paginate($request->validated());

        return ApiResponse::paginated(
            'Data user berhasil diambil.',
            $records->items(),
            $records,
            [
                'superadmin' => [
                    'entity' => 'users',
                ],
            ],
        );
    }

    public function superadminUserRecord(User $user): JsonResponse
    {
        $this->ensureSuperadminAccess();

        return ApiResponse::success(
            'Detail user berhasil diambil.',
            $this->userManagementService->detail($user),
        );
    }

    public function storeSuperadminUserRecord(StoreManagedUserRequest $request): JsonResponse
    {
        $this->ensureSuperadminAccess();

        return ApiResponse::success(
            'User berhasil dibuat.',
            $this->userManagementService->create($request->validated()),
            status: 201,
        );
    }

    public function updateSuperadminUserRecord(UpdateManagedUserRequest $request, User $user): JsonResponse
    {
        $actor = $this->ensureSuperadminAccess();

        return ApiResponse::success(
            'User berhasil diperbarui.',
            $this->userManagementService->update($user, $request->validated(), $actor),
        );
    }

    public function destroySuperadminUserRecord(User $user): JsonResponse
    {
        $actor = $this->ensureSuperadminAccess();
        $this->userManagementService->delete($user, $actor);

        return ApiResponse::success('User berhasil dihapus.');
    }

    public function superadminApiClientRecords(ListSuperadminRecordsRequest $request): JsonResponse
    {
        $this->ensureSuperadminAccess();
        $records = $this->apiClientManagementService->paginate($request->validated());

        return ApiResponse::paginated(
            'Data client API berhasil diambil.',
            $records->items(),
            $records,
            [
                'superadmin' => [
                    'entity' => 'api_clients',
                ],
            ],
        );
    }

    public function superadminApiClientRecord(ApiClient $apiClient): JsonResponse
    {
        $this->ensureSuperadminAccess();

        return ApiResponse::success(
            'Detail client API berhasil diambil.',
            $this->apiClientManagementService->detail($apiClient),
        );
    }

    public function storeSuperadminApiClientRecord(StoreApiClientRequest $request): JsonResponse
    {
        $actor = $this->ensureSuperadminAccess();

        return ApiResponse::success(
            'Client API berhasil dibuat.',
            $this->apiClientManagementService->create($request->validated(), $actor),
            status: 201,
        );
    }

    public function updateSuperadminApiClientRecord(UpdateApiClientRequest $request, ApiClient $apiClient): JsonResponse
    {
        $actor = $this->ensureSuperadminAccess();

        return ApiResponse::success(
            'Client API berhasil diperbarui.',
            $this->apiClientManagementService->update($apiClient, $request->validated(), $actor),
        );
    }

    public function destroySuperadminApiClientRecord(ApiClient $apiClient): JsonResponse
    {
        $this->ensureSuperadminAccess();
        $this->apiClientManagementService->delete($apiClient);

        return ApiResponse::success('Client API berhasil dihapus.');
    }

    public function generateSuperadminApiClientToken(GenerateApiClientTokenRequest $request, ApiClient $apiClient): JsonResponse
    {
        $this->ensureSuperadminAccess();

        return ApiResponse::success(
            'Bearer token berhasil dibuat. Simpan token ini sekarang karena hanya ditampilkan satu kali.',
            $this->apiClientManagementService->generateToken($apiClient, $request->validated()),
            status: 201,
        );
    }

    public function masterDataRecords(ListMasterDataRequest $request, string $entity): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $config = $this->resolveMasterDataPage($entity);
        $this->authorize('viewAny', MasterData::class);

        $request->merge([
            'type' => $config['type_code'],
        ]);

        $records = $this->masterDataQueryService->paginate($request);

        return ApiResponse::paginated(
            'Data berhasil diambil.',
            MasterDataResource::collection($records->getCollection())->resolve(),
            $records,
            [
                'master_data' => [
                    'entity' => $config['key'],
                    'type_code' => $config['type_code'],
                    'label' => $config['label'],
                ],
            ],
        );
    }

    public function masterDataRecord(string $entity, MasterData $masterData): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $config = $this->resolveMasterDataPage($entity);
        $this->ensureMasterDataRecordMatches($config['type_code'], $masterData);
        $this->authorize('view', $masterData);

        $masterData->load('type');

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataResource::make($masterData)->resolve(),
        );
    }

    public function storeMasterDataRecord(StoreMasterDataRequest $request, string $entity): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $config = $this->resolveMasterDataPage($entity);
        $this->authorize('create', MasterData::class);

        $payload = array_merge($request->validated(), [
            'entity_type' => $config['type_code'],
        ]);

        $record = $this->masterDataWriteService->create($payload);

        return ApiResponse::success(
            'Data berhasil dibuat.',
            MasterDataResource::make($record)->resolve(),
            status: 201,
        );
    }

    public function updateMasterDataRecord(UpdateMasterDataRequest $request, string $entity, MasterData $masterData): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $config = $this->resolveMasterDataPage($entity);
        $this->ensureMasterDataRecordMatches($config['type_code'], $masterData);
        $this->authorize('update', $masterData);

        $payload = array_merge($request->validated(), [
            'entity_type' => $config['type_code'],
        ]);

        $record = $this->masterDataWriteService->update($masterData, $payload);

        return ApiResponse::success(
            'Data berhasil diperbarui.',
            MasterDataResource::make($record)->resolve(),
        );
    }

    public function bridgeSourceRecords(ListBridgeSourceRequest $request): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        $usesDatabaseSource = $this->bridgeSourceCrudService->isDatabaseSourceAvailable();
        $records = $usesDatabaseSource
            ? $this->bridgeSourceCrudService->paginate($request->validated())
            : $this->bridgeSourceDumpService->paginateCombined($request->validated());

        return ApiResponse::paginated(
            'Data jembatan source berhasil diambil.',
            $records->items(),
            $records,
            [
                'bridge_source' => [
                    'entity' => 'jembatan',
                    'label' => 'Jembatan',
                    'main_table' => 'm_jembatan',
                    'data_mode' => $usesDatabaseSource ? 'database' : 'dump',
                ],
            ],
        );
    }

    public function bridgeSourceRecord(string $bridgeUniqid): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $record = $this->bridgeSourceCrudService->isDatabaseSourceAvailable()
            ? $this->bridgeSourceCrudService->find($bridgeUniqid)
            : $this->bridgeSourceDumpService->findCombined($bridgeUniqid);

        if ($record === null) {
            abort(404);
        }

        return ApiResponse::success(
            'Data jembatan source berhasil diambil.',
            $record,
        );
    }

    public function storeBridgeSourceRecord(StoreBridgeSourceRequest $request): JsonResponse
    {
        $this->ensureBridgeSourceDatabaseWritable();
        $actor = $this->currentDashboardUser();

        return ApiResponse::success(
            'Data jembatan source berhasil dibuat.',
            $this->bridgeSourceCrudService->create($request->validated(), $actor),
            status: 201,
        );
    }

    public function updateBridgeSourceRecord(UpdateBridgeSourceRequest $request, string $bridgeUniqid): JsonResponse
    {
        $this->ensureBridgeSourceDatabaseWritable();
        $actor = $this->currentDashboardUser();

        return ApiResponse::success(
            'Data jembatan source berhasil diperbarui.',
            $this->bridgeSourceCrudService->update($bridgeUniqid, $request->validated(), $actor),
        );
    }

    public function destroyBridgeSourceRecord(string $bridgeUniqid): JsonResponse
    {
        $this->ensureBridgeSourceDatabaseWritable();
        $actor = $this->currentDashboardUser();
        $this->bridgeSourceCrudService->delete($bridgeUniqid, $actor);

        return ApiResponse::success('Data jembatan source berhasil dihapus.');
    }

    public function bridgeSourceTableRows(ListBridgeSourceRequest $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $rows = $this->bridgeSourceCrudService->isDatabaseSourceAvailable()
            ? $this->bridgeSourceCrudService->paginateTable($table, $request->validated())
            : $this->bridgeSourceDumpService->paginate($table, $request->validated());
        $tablePage = $this->resolveBridgeSourceTablePage($table);

        return ApiResponse::paginated(
            'Data tabel source jembatan berhasil diambil.',
            $rows->items(),
            $rows,
            [
                'bridge_source_table' => [
                    'table' => $tablePage['table'],
                    'label' => $tablePage['label'],
                ],
            ],
        );
    }

    public function tunnelSourceRecords(ListTunnelRequest $request): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $records = $this->tunnelService->paginate($request->validated());

        return ApiResponse::paginated(
            'Data terowongan berhasil diambil.',
            TunnelResource::collection($records->getCollection())->resolve(),
            $records,
            [
                'tunnel_source' => [
                    'entity' => 'terowongan',
                    'label' => 'Terowongan',
                    'main_table' => 'm_tunnels',
                    'data_mode' => 'database',
                ],
            ],
        );
    }

    public function tunnelSourceRecord(string $tunnelId): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        return ApiResponse::success(
            'Data terowongan berhasil diambil.',
            TunnelDetailResource::make($this->tunnelService->find($tunnelId))->resolve(),
        );
    }

    public function tunnelSourceTableRows(ListBridgeSourceRequest $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $rows = $this->tunnelSourceTableService->paginate($table, $request->validated());
        $tablePage = $this->resolveTunnelSourceTablePage($table);

        return ApiResponse::paginated(
            'Data tabel terowongan berhasil diambil.',
            $rows->items(),
            $rows,
            [
                'tunnel_source_table' => [
                    'table' => $tablePage['table'],
                    'label' => $tablePage['label'],
                ],
            ],
        );
    }

    public function storeTunnelSourceTableRow(Request $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        return ApiResponse::success(
            'Data tabel terowongan berhasil dibuat.',
            $this->tunnelSourceTableService->create($table, $request->validate([
                'data' => ['required', 'array'],
            ])),
            status: 201,
        );
    }

    public function updateTunnelSourceTableRow(Request $request, string $table, string $rowKey): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        return ApiResponse::success(
            'Data tabel terowongan berhasil diperbarui.',
            $this->tunnelSourceTableService->update($table, $rowKey, $request->validate([
                'data' => ['required', 'array'],
            ])),
        );
    }

    public function destroyTunnelSourceTableRow(string $table, string $rowKey): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $this->tunnelSourceTableService->delete($table, $rowKey);

        return ApiResponse::success('Data tabel terowongan berhasil dihapus.');
    }

    public function warehouseSourceTableRows(ListBridgeSourceRequest $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $rows = $this->warehouseSourceTableService->paginate($table, $request->validated());
        $tablePage = $this->resolveWarehouseSourceTablePage($table);

        return ApiResponse::paginated(
            'Data tabel gudang berhasil diambil.',
            $rows->items(),
            $rows,
            [
                'warehouse_source_table' => [
                    'table' => $tablePage['table'],
                    'label' => $tablePage['label'],
                ],
            ],
        );
    }

    public function storeWarehouseSourceTableRow(Request $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        return ApiResponse::success(
            'Data tabel gudang berhasil dibuat.',
            $this->warehouseSourceTableService->create($table, $request->validate([
                'data' => ['required', 'array'],
            ])),
            status: 201,
        );
    }

    public function updateWarehouseSourceTableRow(Request $request, string $table, string $rowKey): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        return ApiResponse::success(
            'Data tabel gudang berhasil diperbarui.',
            $this->warehouseSourceTableService->update($table, $rowKey, $request->validate([
                'data' => ['required', 'array'],
            ])),
        );
    }

    public function destroyWarehouseSourceTableRow(string $table, string $rowKey): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $this->warehouseSourceTableService->delete($table, $rowKey);

        return ApiResponse::success('Data tabel gudang berhasil dihapus.');
    }

    public function referenceSourceTableRows(ListBridgeSourceRequest $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $rows = $this->referenceSourceTableService->paginate($table, $request->validated());
        $tablePage = $this->resolveReferenceSourceTablePage($table);

        return ApiResponse::paginated(
            'Data tabel referensi berhasil diambil.',
            $rows->items(),
            $rows,
            [
                'reference_source_table' => [
                    'table' => $tablePage['table'],
                    'label' => $tablePage['label'],
                ],
            ],
        );
    }

    public function storeReferenceSourceTableRow(Request $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        return ApiResponse::success(
            'Data tabel referensi berhasil dibuat.',
            $this->referenceSourceTableService->create($table, $request->validate([
                'data' => ['required', 'array'],
            ])),
            status: 201,
        );
    }

    public function updateReferenceSourceTableRow(Request $request, string $table, string $rowKey): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        return ApiResponse::success(
            'Data tabel referensi berhasil diperbarui.',
            $this->referenceSourceTableService->update($table, $rowKey, $request->validate([
                'data' => ['required', 'array'],
            ])),
        );
    }

    public function destroyReferenceSourceTableRow(string $table, string $rowKey): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $this->referenceSourceTableService->delete($table, $rowKey);

        return ApiResponse::success('Data tabel referensi berhasil dihapus.');
    }

    public function importTunnelSourceTableRows(Request $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $validated['file'] ?? null;
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'file' => ['File CSV tidak valid.'],
            ]);
        }

        $result = $this->tunnelSourceTableService->importCsv($table, $file->getRealPath());

        if ($result['errors'] !== []) {
            return ApiResponse::error(
                'Sebagian data tabel terowongan gagal diimport.',
                'TUNNEL_TABLE_CSV_IMPORT_PARTIAL',
                422,
                [
                    'created' => [$result['created'].' data berhasil dibuat.'],
                    'rows' => $result['errors'],
                ],
            );
        }

        return ApiResponse::success('Import CSV tabel terowongan berhasil.', [
            'created' => $result['created'],
        ]);
    }

    public function exportTunnelSourceTableRows(string $table): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv($table.'-export-'.now()->format('Ymd-His').'.csv', function ($handle) use ($table): void {
            $this->tunnelSourceTableService->streamCsv($table, $handle);
        });
    }

    public function tunnelSourceTableCsvTemplate(string $table): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv('template-'.$table.'.csv', function ($handle) use ($table): void {
            $this->tunnelSourceTableService->streamCsv($table, $handle, includeRows: false);
        });
    }

    public function importWarehouseSourceTableRows(Request $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $validated['file'] ?? null;
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'file' => ['File CSV tidak valid.'],
            ]);
        }

        $result = $this->warehouseSourceTableService->importCsv($table, $file->getRealPath());

        if ($result['errors'] !== []) {
            return ApiResponse::error(
                'Sebagian data tabel gudang gagal diimport.',
                'WAREHOUSE_TABLE_CSV_IMPORT_PARTIAL',
                422,
                [
                    'created' => [$result['created'].' data berhasil dibuat.'],
                    'rows' => $result['errors'],
                ],
            );
        }

        return ApiResponse::success('Import CSV tabel gudang berhasil.', [
            'created' => $result['created'],
        ]);
    }

    public function exportWarehouseSourceTableRows(string $table): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv($table.'-export-'.now()->format('Ymd-His').'.csv', function ($handle) use ($table): void {
            $this->warehouseSourceTableService->streamCsv($table, $handle);
        });
    }

    public function warehouseSourceTableCsvTemplate(string $table): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv('template-'.$table.'.csv', function ($handle) use ($table): void {
            $this->warehouseSourceTableService->streamCsv($table, $handle, includeRows: false);
        });
    }

    public function importReferenceSourceTableRows(Request $request, string $table): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $validated['file'] ?? null;
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'file' => ['File CSV tidak valid.'],
            ]);
        }

        $result = $this->referenceSourceTableService->importCsv($table, $file->getRealPath());

        if ($result['errors'] !== []) {
            return ApiResponse::error(
                'Sebagian data tabel referensi gagal diimport.',
                'REFERENCE_TABLE_CSV_IMPORT_PARTIAL',
                422,
                [
                    'created' => [$result['created'].' data berhasil dibuat.'],
                    'rows' => $result['errors'],
                ],
            );
        }

        return ApiResponse::success('Import CSV tabel referensi berhasil.', [
            'created' => $result['created'],
        ]);
    }

    public function exportReferenceSourceTableRows(string $table): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv($table.'-export-'.now()->format('Ymd-His').'.csv', function ($handle) use ($table): void {
            $this->referenceSourceTableService->streamCsv($table, $handle);
        });
    }

    public function referenceSourceTableCsvTemplate(string $table): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv('template-'.$table.'.csv', function ($handle) use ($table): void {
            $this->referenceSourceTableService->streamCsv($table, $handle, includeRows: false);
        });
    }

    public function storeTunnelSourceRecord(StoreTunnelRequest $request): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $payload = $this->tunnelDocumentUploadService->mergeUploadedFiles($request, $request->validated());

        return ApiResponse::success(
            'Data terowongan berhasil dibuat.',
            TunnelDetailResource::make($this->tunnelService->create($payload))->resolve(),
            status: 201,
        );
    }

    public function updateTunnelSourceRecord(UpdateTunnelRequest $request, string $tunnel_id): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $payload = $this->tunnelDocumentUploadService->mergeUploadedFiles($request, $request->validated());

        return ApiResponse::success(
            'Data terowongan berhasil diperbarui.',
            TunnelDetailResource::make($this->tunnelService->update($tunnel_id, $payload))->resolve(),
        );
    }

    public function destroyTunnelSourceRecord(string $tunnel_id): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();
        $this->tunnelService->delete($tunnel_id);

        return ApiResponse::success('Data terowongan berhasil dihapus.');
    }

    public function importTunnelSourceRecords(Request $request): JsonResponse
    {
        $this->ensureOperationalDashboardAccess();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $validated['file'] ?? null;
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'file' => ['File CSV tidak valid.'],
            ]);
        }

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => ['File CSV tidak dapat dibaca.'],
            ]);
        }

        $created = 0;
        $errors = [];

        try {
            $headers = fgetcsv($handle);
            if (! is_array($headers) || $headers === []) {
                throw ValidationException::withMessages([
                    'file' => ['Header CSV tidak ditemukan.'],
                ]);
            }

            $headers = array_map(
                fn (string $header): string => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header),
                $headers,
            );

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($this->csvRowIsEmpty($row)) {
                    continue;
                }

                $payload = $this->tunnelPayloadFromCsvRow($headers, $row);
                $validator = Validator::make($payload, $this->tunnelImportRules());

                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'messages' => $validator->errors()->all(),
                    ];

                    continue;
                }

                $this->tunnelService->create($validator->validated());
                $created++;
            }
        } finally {
            fclose($handle);
        }

        if ($errors !== []) {
            return ApiResponse::error(
                'Sebagian data terowongan gagal diimport.',
                'TUNNEL_CSV_IMPORT_PARTIAL',
                422,
                [
                    'created' => [$created.' data berhasil dibuat.'],
                    'rows' => $errors,
                ],
            );
        }

        return ApiResponse::success('Import CSV terowongan berhasil.', [
            'created' => $created,
        ]);
    }

    public function exportTunnelSourceRecords(): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv('terowongan-export-'.now()->format('Ymd-His').'.csv', function ($handle): void {
            fputcsv($handle, self::TUNNEL_CSV_COLUMNS);

            Tunnel::query()
                ->with(['structure', 'specs', 'docs'])
                ->orderByDesc('updated_at')
                ->chunk(200, function ($records) use ($handle): void {
                    foreach ($records as $record) {
                        fputcsv($handle, $this->tunnelCsvRow($record));
                    }
                });
        });
    }

    public function tunnelCsvTemplate(): StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        return $this->downloadTunnelCsv('template-terowongan.csv', function ($handle): void {
            fputcsv($handle, self::TUNNEL_CSV_COLUMNS);
            fputcsv($handle, [
                'TUN-001',
                'BH-T-001',
                'Terowongan Contoh',
                'WK-01',
                'LNT-01',
                'KM 143+144',
                '949.50',
                '1902',
                '1906',
                '120',
                '-6.8300000',
                '107.4800000',
                'Operasi',
                'Aktif',
                'Baik',
                now()->toDateString(),
                'Batuan',
                'Beton',
                'Beton bertulang',
                'Beton',
                'Beton',
                'NATM',
                'Membran',
                '2024',
                '1',
                'Tunggal',
                '1.067',
                '4.50',
                '5.20',
                '3000',
                '4500',
                'Tapal kuda',
                '1.20',
                '350.00',
                'Catatan teknis contoh',
                'DED-001',
                '{"path":"tunnels/docs/ded.pdf"}',
                'SPT-001',
                '{"path":"tunnels/docs/spektek.pdf"}',
                'SD-001',
                '{"path":"tunnels/docs/shop-drawing.pdf"}',
                'ABD-001',
                '{"path":"tunnels/docs/as-built.pdf"}',
                'UJI-001',
                '{"path":"tunnels/docs/uji.pdf"}',
            ]);
        });
    }

    public function tunnelDocument(string $path): BinaryFileResponse|StreamedResponse
    {
        $this->ensureOperationalDashboardAccess();

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        abort_unless(str_starts_with($normalizedPath, 'tunnels/docs/'), 404);

        foreach ($this->tunnelDocumentCandidatePaths($normalizedPath) as $candidatePath) {
            if (! is_file($candidatePath) || ! is_readable($candidatePath)) {
                continue;
            }

            return response()->file($candidatePath, [
                'Content-Type' => File::mimeType($candidatePath) ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.basename($candidatePath).'"',
            ]);
        }

        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        return Storage::disk('public')->response($normalizedPath);
    }

    /**
     * @return array<int, string>
     */
    private function tunnelDocumentCandidatePaths(string $normalizedPath): array
    {
        return array_values(array_unique(array_filter([
            Storage::disk('public')->path($normalizedPath),
            storage_path('app/public/'.$normalizedPath),
            public_path('storage/'.$normalizedPath),
            public_path($normalizedPath),
        ])));
    }

    private function renderPage(string $page, array $extra = []): View
    {
        $user = request()->user();

        if (! $user instanceof User || ! $this->canAccessPage($user, $page)) {
            abort(403);
        }

        return view('dashboard.index', array_merge([
            'overview' => $this->dashboardService->overview(),
            'currentPage' => $page,
            'masterDataMenu' => $this->buildMasterDataMenu(),
        ], $extra));
    }

    private function defaultPageFor(User $user): string
    {
        return $this->canAccessPage($user, 'overview') ? 'overview' : 'documentation';
    }

    private function canAccessPage(User $user, string $page): bool
    {
        if ($page === 'documentation') {
            return true;
        }

        if (in_array($page, self::SUPERADMIN_PAGES, true)) {
            return $user->hasRole(UserRole::Superadmin);
        }

        if ($user->hasRole('superadmin', 'admin')) {
            return true;
        }

        return $user->hasRole(UserRole::Operator)
            && in_array($page, ['overview', 'master-data-entity', 'bridge-source-table', 'tunnel-source-table', 'warehouse-source-table', 'reference-source-table'], true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildMasterDataMenu(): array
    {
        $typeMap = MasterDataType::query()
            ->withCount('records')
            ->whereIn('code', array_column(self::MASTER_DATA_PAGES, 'type_code'))
            ->get()
            ->keyBy('code');
        $usesDatabaseSource = $this->bridgeSourceCrudService->isDatabaseSourceAvailable();
        $bridgeRecordCount = $usesDatabaseSource
            ? $this->bridgeSourceCrudService->count()
            : $this->bridgeSourceDumpService->countCombined();
        $tunnelRecordCount = $this->tunnelRecordCount();
        $warehouseRecordCount = $this->warehouseRecordCount();
        $referenceRecordCount = $this->referenceRecordCount();

        return collect(self::MASTER_DATA_PAGES)
            ->map(function (array $config, string $key) use ($typeMap, $bridgeRecordCount, $tunnelRecordCount, $warehouseRecordCount, $referenceRecordCount): array {
                /** @var MasterDataType|null $type */
                $type = $typeMap->get($config['type_code']);
                $isBridgeSource = $key === 'jembatan';
                $isTunnelSource = $key === 'terowongan';
                $isWarehouseSource = $key === 'gudang';
                $isReferenceSource = $key === 'referensi';
                $sourceChildren = match (true) {
                    $isBridgeSource => $this->bridgeSourceCrudService->isDatabaseSourceAvailable()
                        ? $this->bridgeSourceCrudService->tableCatalog()
                        : $this->bridgeSourceDumpService->tables(),
                    $isTunnelSource => $this->tunnelSourceTableService->catalog(),
                    $isWarehouseSource => $this->warehouseSourceTableService->catalog(),
                    $isReferenceSource => $this->referenceSourceTableService->catalog(),
                    default => [],
                };
                $recordCount = match (true) {
                    $isBridgeSource => $bridgeRecordCount,
                    $isTunnelSource => $tunnelRecordCount,
                    $isWarehouseSource => $warehouseRecordCount,
                    $isReferenceSource => $referenceRecordCount,
                    default => $type?->records_count ?? 0,
                };

                $primaryChildren = $isReferenceSource ? [] : [[
                    'key' => $key.'-records',
                    'type' => 'entity',
                    'kind' => 'combine',
                    'label' => 'Data '.$config['label'],
                    'href' => route('dashboard.master-data.entity', ['entity' => $key]),
                    'row_count' => $recordCount,
                ]];

                return [
                    'key' => $key,
                    'label' => $config['label'],
                    'type_code' => $config['type_code'],
                    'href' => route('dashboard.master-data.entity', ['entity' => $key]),
                    'record_count' => $recordCount,
                    'is_available' => $isBridgeSource || $isTunnelSource || $isWarehouseSource || $isReferenceSource || $type !== null,
                    'children' => [
                        ...$primaryChildren,
                        ...$sourceChildren,
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveMasterDataPage(string $entity): array
    {
        $config = self::MASTER_DATA_PAGES[$entity] ?? null;

        if ($config === null) {
            abort(404);
        }

        if ($entity === 'jembatan') {
            $usesDatabaseSource = $this->bridgeSourceCrudService->isDatabaseSourceAvailable();

            return [
                'key' => $entity,
                'label' => $config['label'],
                'type_code' => $config['type_code'],
                'mode' => 'bridge-source',
                'records_count' => $usesDatabaseSource
                    ? $this->bridgeSourceCrudService->count()
                    : $this->bridgeSourceDumpService->countCombined(),
                'type_exists' => true,
                'type_name' => $config['label'],
                'crud_enabled' => $usesDatabaseSource,
                'data_mode' => $usesDatabaseSource ? 'database' : 'dump',
                'columns' => [
                    'bridge_identity',
                    'route_summary',
                    'wilayah_summary',
                    'location_summary',
                    'structure_summary',
                    'assessment_summary',
                ],
                'relation_map' => $this->bridgeSourceCrudService->relationMap(),
                'list_endpoint' => route('dashboard.bridge-source.records.index'),
                'store_endpoint' => route('dashboard.bridge-source.records.store'),
                'delete_endpoint' => route('dashboard.bridge-source.records.destroy', ['bridgeUniqid' => '__bridge__']),
            ];
        }

        if ($entity === 'terowongan') {
            return [
                'key' => $entity,
                'label' => $config['label'],
                'type_code' => $config['type_code'],
                'mode' => 'tunnel-source',
                'records_count' => $this->tunnelRecordCount(),
                'type_exists' => true,
                'type_name' => $config['label'],
                'crud_enabled' => true,
                'data_mode' => 'database',
                'columns' => [
                    'nama_terowongan',
                    'nomor_bh',
                    'km_hm',
                    'id_wilayah_kerja',
                    'id_lintas',
                    'panjang_m',
                    'status_operasi',
                    'status_aset',
                    'updated_at',
                ],
                'list_endpoint' => route('dashboard.tunnel-source.records.index'),
                'store_endpoint' => route('dashboard.tunnel-source.records.store'),
                'update_endpoint' => route('dashboard.tunnel-source.records.update', ['tunnel_id' => '__tunnel__']),
                'delete_endpoint' => route('dashboard.tunnel-source.records.destroy', ['tunnel_id' => '__tunnel__']),
                'lookup_options' => $this->tunnelSourceTableService->tunnelLookupOptions(),
                'import_endpoint' => route('dashboard.tunnel-source.import'),
                'export_endpoint' => route('dashboard.tunnel-source.export'),
                'template_endpoint' => route('dashboard.tunnel-source.template'),
            ];
        }

        if ($entity === 'gudang') {
            $warehousePage = $this->warehouseSourceTableService->mainPage();

            return [
                ...$warehousePage,
                'key' => $entity,
                'label' => $config['label'],
                'type_code' => $config['type_code'],
                'mode' => 'warehouse-source',
                'records_count' => $this->warehouseRecordCount(),
                'type_exists' => true,
                'type_name' => $config['label'],
                'crud_enabled' => true,
                'data_mode' => 'database',
            ];
        }

        if ($entity === 'referensi') {
            $referencePage = $this->referenceSourceTableService->mainPage();

            return [
                ...$referencePage,
                'key' => $entity,
                'label' => $config['label'],
                'type_code' => $config['type_code'],
                'mode' => 'reference-source',
                'records_count' => $this->referenceRecordCount(),
                'type_exists' => true,
                'type_name' => $config['label'],
                'crud_enabled' => true,
                'data_mode' => 'database',
            ];
        }

        $type = MasterDataType::query()
            ->withCount('records')
            ->where('code', $config['type_code'])
            ->first();

        $visibleFields = $type?->visible_fields;
        if (! is_array($visibleFields) || $visibleFields === []) {
            $visibleFields = ['code', 'name', 'status', 'updated_at'];
        }

        $searchableFields = $type?->searchable_fields;
        if (! is_array($searchableFields) || $searchableFields === []) {
            $searchableFields = ['code', 'name', 'description'];
        }

        return [
            'key' => $entity,
            'label' => $config['label'],
            'type_code' => $config['type_code'],
            'mode' => 'master-data',
            'records_count' => $type?->records_count ?? 0,
            'type_exists' => $type !== null,
            'type_name' => $type?->name ?? $config['label'],
            'visible_fields' => array_values($visibleFields),
            'searchable_fields' => array_values($searchableFields),
            'status_options' => MasterDataStatus::values(),
            'list_endpoint' => route('dashboard.master-data.records', ['entity' => $entity]),
            'store_endpoint' => route('dashboard.master-data.records.store', ['entity' => $entity]),
        ];
    }

    private function ensureMasterDataRecordMatches(string $typeCode, MasterData $masterData): void
    {
        if ($masterData->entity_type !== $typeCode) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveBridgeSourceTablePage(string $table): array
    {
        $page = $this->bridgeSourceCrudService->isDatabaseSourceAvailable()
            ? $this->bridgeSourceCrudService->tablePage($table)
            : $this->bridgeSourceDumpService->tablePage($table);

        return [
            ...$page,
            'parent_key' => 'jembatan',
            'mode' => 'bridge-source-table',
            'breadcrumb_label' => 'Jembatan',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveTunnelSourceTablePage(string $table): array
    {
        return [
            ...$this->tunnelSourceTableService->tablePage($table),
            'parent_key' => 'terowongan',
            'mode' => 'tunnel-source-table',
            'breadcrumb_label' => 'Terowongan',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveWarehouseSourceTablePage(string $table): array
    {
        return [
            ...$this->warehouseSourceTableService->tablePage($table),
            'parent_key' => 'gudang',
            'mode' => 'warehouse-source-table',
            'breadcrumb_label' => 'Gudang',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveReferenceSourceTablePage(string $table): array
    {
        return [
            ...$this->referenceSourceTableService->tablePage($table),
            'parent_key' => 'referensi',
            'mode' => 'reference-source-table',
            'breadcrumb_label' => 'Referensi',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSuperadminUserPage(): array
    {
        return [
            'label' => 'Manajemen User',
            'records_count' => User::query()->count(),
            'columns' => ['name', 'email', 'role_label', 'email_verified_at', 'updated_at'],
            'list_endpoint' => route('dashboard.superadmin.users.records.index'),
            'store_endpoint' => route('dashboard.superadmin.users.records.store'),
            'role_options' => collect(UserRole::cases())
                ->map(fn (UserRole $role): array => [
                    'value' => $role->value,
                    'label' => $role->label(),
                    'description' => $role->description(),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSuperadminApiClientPage(): array
    {
        return [
            'label' => 'Bearer Key API',
            'records_count' => ApiClient::query()->count(),
            'columns' => ['name', 'code', 'owner_email', 'access_tokens_count', 'is_active', 'expires_at', 'updated_at'],
            'list_endpoint' => route('dashboard.superadmin.api-clients.records.index'),
            'store_endpoint' => route('dashboard.superadmin.api-clients.records.store'),
            'token_endpoint' => route('dashboard.superadmin.api-clients.tokens.store', ['apiClient' => '__client__']),
            'ability_options' => $this->apiClientManagementService->abilityOptions(),
        ];
    }

    private function ensureOperationalDashboardAccess(): void
    {
        $this->currentDashboardUser();
    }

    private function ensureSuperadminAccess(): User
    {
        $user = request()->user();

        if (! $user instanceof User || ! $user->hasRole(UserRole::Superadmin)) {
            abort(403);
        }

        return $user;
    }

    private function currentDashboardUser(): User
    {
        $user = request()->user();

        if (! $user instanceof User || ! $user->hasRole('superadmin', 'admin', 'operator')) {
            abort(403);
        }

        return $user;
    }

    private function ensureBridgeSourceDatabaseWritable(): void
    {
        if ($this->bridgeSourceCrudService->isDatabaseSourceAvailable()) {
            return;
        }

        throw ValidationException::withMessages([
            'bridge_source' => ['Mode CRUD source database belum tersedia di environment ini. Halaman saat ini memakai fallback baca dari dump SQL.'],
        ]);
    }

    private function tunnelRecordCount(): int
    {
        if (! Schema::connection('tunnel')->hasTable('m_tunnels')) {
            return 0;
        }

        return Tunnel::query()->count();
    }

    private function warehouseRecordCount(): int
    {
        if (! Schema::connection('warehouse')->hasTable('m_gudang')) {
            return 0;
        }

        return (int) DB::connection('warehouse')->table('m_gudang')->whereNull('deleted_at')->count();
    }

    private function referenceRecordCount(): int
    {
        return (int) collect($this->referenceSourceTableService->catalog())->sum('row_count');
    }

    private function downloadTunnelCsv(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputs($handle, "\xEF\xBB\xBF");
            $writer($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    private function tunnelCsvRow(Tunnel $tunnel): array
    {
        return array_map(
            fn (string $column): mixed => $this->csvValue(data_get($tunnel, $column)),
            self::TUNNEL_CSV_COLUMNS,
        );
    }

    private function csvValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>
     */
    private function tunnelPayloadFromCsvRow(array $headers, array $row): array
    {
        $payload = [];

        foreach ($headers as $index => $header) {
            if (! in_array($header, self::TUNNEL_CSV_COLUMNS, true)) {
                continue;
            }

            $value = $this->normalizeCsvValue($row[$index] ?? null);

            if ($value === null) {
                continue;
            }

            Arr::set($payload, $header, $value);
        }

        return $payload;
    }

    private function normalizeCsvValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        if (
            (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) ||
            (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
        ) {
            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $trimmed;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function csvRowIsEmpty(array $row): bool
    {
        return collect($row)->every(fn ($value): bool => trim((string) $value) === '');
    }

    /**
     * @return array<string, mixed>
     */
    private function tunnelImportRules(): array
    {
        $nextYear = now()->year + 1;

        return [
            'kode_aset' => ['nullable', 'string', 'max:50', Rule::unique('tunnel.m_tunnels', 'kode_aset')->whereNull('deleted_at')],
            'nomor_bh' => ['nullable', 'string', 'max:50'],
            'nama_terowongan' => ['required', 'string', 'max:150'],
            'id_wilayah_kerja' => ['nullable', 'string', 'max:50'],
            'id_lintas' => ['nullable', 'string', 'max:50'],
            'km_hm' => ['nullable', 'string', 'max:30'],
            'panjang_m' => ['nullable', 'numeric', 'min:0'],
            'tahun_bangunan' => ['nullable', 'integer', 'between:1800,'.$nextYear],
            'tahun_operasi' => ['nullable', 'integer', 'between:1800,'.$nextYear],
            'umur_tahun' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'long' => ['nullable', 'numeric', 'between:-180,180'],
            'status_operasi' => ['nullable', 'string', 'max:30'],
            'status_aset' => ['nullable', 'string', 'max:30'],
            'kondisi_terakhir' => ['nullable', 'string', 'max:50'],
            'tgl_inspeksi_terakhir' => ['nullable', 'date'],
            'structure' => ['nullable', 'array'],
            'structure.jenis_struktur' => ['nullable', 'string', 'max:100'],
            'structure.material_struktur' => ['nullable', 'string', 'max:100'],
            'structure.material_lining' => ['nullable', 'string', 'max:100'],
            'structure.material_portal' => ['nullable', 'string', 'max:100'],
            'structure.material_invert' => ['nullable', 'string', 'max:100'],
            'structure.metode_konstruksi' => ['nullable', 'string', 'max:100'],
            'structure.waterproofing' => ['nullable', 'string', 'max:100'],
            'structure.tahun_rehabilitasi_terakhir' => ['nullable', 'integer', 'between:1800,'.$nextYear],
            'specs' => ['nullable', 'array'],
            'specs.jumlah_jalur' => ['nullable', 'integer', 'min:1', 'max:255'],
            'specs.jenis_jalur' => ['nullable', 'string', 'max:50'],
            'specs.gauge_m' => ['nullable', 'numeric', 'min:0'],
            'specs.lebar_bersih_m' => ['nullable', 'numeric', 'min:0'],
            'specs.tinggi_bersih_m' => ['nullable', 'numeric', 'min:0'],
            'specs.clearance_horizontal_mm' => ['nullable', 'integer', 'min:1'],
            'specs.clearance_vertikal_mm' => ['nullable', 'integer', 'min:1'],
            'specs.bentuk_penampang' => ['nullable', 'string', 'max:100'],
            'specs.gradien_persen' => ['nullable', 'numeric', 'min:0'],
            'specs.radius_lengkung_m' => ['nullable', 'numeric', 'min:0'],
            'specs.catatan_teknis' => ['nullable', 'string'],
            'docs' => ['nullable', 'array'],
            'docs.no_ded_bed_kajian_teknis' => ['nullable', 'string', 'max:100'],
            'docs.ded_bed_kajian_teknis' => ['nullable'],
            'docs.no_spesifikasi_teknis' => ['nullable', 'string', 'max:100'],
            'docs.spesifikasi_teknis' => ['nullable'],
            'docs.no_shop_drawing' => ['nullable', 'string', 'max:100'],
            'docs.shop_drawing' => ['nullable'],
            'docs.no_as_built_drawing' => ['nullable', 'string', 'max:100'],
            'docs.as_built_drawing' => ['nullable'],
            'docs.no_dok_hasil_uji' => ['nullable', 'string', 'max:100'],
            'docs.dok_hasil_uji' => ['nullable'],
        ];
    }
}
