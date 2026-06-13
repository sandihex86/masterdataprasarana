<?php

namespace App\Http\Controllers\Web;

use App\Enums\MasterDataStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMasterDataRequest;
use App\Http\Requests\Api\V1\StoreMasterDataRequest;
use App\Http\Requests\Api\V1\UpdateMasterDataRequest;
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
use App\Models\ApiClient;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Models\User;
use App\Services\BridgeSource\BridgeSourceCrudService;
use App\Services\BridgeSource\BridgeSourceDumpService;
use App\Services\Dashboard\DashboardService;
use App\Services\MasterData\MasterDataQueryService;
use App\Services\MasterData\MasterDataWriteService;
use App\Services\SuperAdmin\ApiClientManagementService;
use App\Services\SuperAdmin\UserManagementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const MASTER_DATA_PAGES = [
        'jembatan' => [
            'label' => 'Jembatan',
            'type_code' => 'bridge',
        ],
        'jalur' => [
            'label' => 'Jalur',
            'type_code' => 'railway_track',
        ],
        'fasilitas-operasional' => [
            'label' => 'Fasilitas Operasional',
            'type_code' => 'operational_facility',
        ],
        'sertifikat' => [
            'label' => 'Sertifikat',
            'type_code' => 'certificate',
        ],
        'gudang' => [
            'label' => 'Gudang',
            'type_code' => 'warehouse',
        ],
    ];

    private const SUPERADMIN_PAGES = [
        'superadmin-users',
        'superadmin-api-clients',
    ];

    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly MasterDataQueryService $masterDataQueryService,
        private readonly MasterDataWriteService $masterDataWriteService,
        private readonly BridgeSourceCrudService $bridgeSourceCrudService,
        private readonly BridgeSourceDumpService $bridgeSourceDumpService,
        private readonly UserManagementService $userManagementService,
        private readonly ApiClientManagementService $apiClientManagementService,
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

    public function quickMenu(): View
    {
        return $this->renderPage('quick-menu');
    }

    public function moduleStatus(): View
    {
        return $this->renderPage('module-status');
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

    public function monitoring(): View
    {
        return $this->renderPage('monitoring');
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

        return $user->hasRole('superadmin', 'admin');
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

        return collect(self::MASTER_DATA_PAGES)
            ->map(function (array $config, string $key) use ($typeMap, $bridgeRecordCount): array {
                /** @var MasterDataType|null $type */
                $type = $typeMap->get($config['type_code']);
                $isBridgeSource = $key === 'jembatan';
                $children = $isBridgeSource
                    ? ($this->bridgeSourceCrudService->isDatabaseSourceAvailable()
                        ? $this->bridgeSourceCrudService->tableCatalog()
                        : $this->bridgeSourceDumpService->tables())
                    : [];

                return [
                    'key' => $key,
                    'label' => $config['label'],
                    'type_code' => $config['type_code'],
                    'href' => route('dashboard.master-data.entity', ['entity' => $key]),
                    'record_count' => $isBridgeSource ? $bridgeRecordCount : ($type?->records_count ?? 0),
                    'is_available' => $isBridgeSource || $type !== null,
                    'children' => $children,
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

        if (! $user instanceof User || ! $user->hasRole('superadmin', 'admin')) {
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
}
