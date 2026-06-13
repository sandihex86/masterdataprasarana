<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListBridgeSourceTableRecordRequest;
use App\Http\Requests\Api\V1\StoreBridgeSourceTableRecordRequest;
use App\Http\Requests\Api\V1\UpdateBridgeSourceTableRecordRequest;
use App\Http\Resources\Api\V1\BridgeSourceTableCatalogResource;
use App\Http\Resources\Api\V1\BridgeSourceTableRowResource;
use App\Http\Resources\Api\V1\BridgeSourceTableSchemaResource;
use App\Services\BridgeSource\BridgeSourceTableCrudService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class BridgeSourceTableController extends Controller
{
    public function __construct(
        private readonly BridgeSourceTableCrudService $bridgeSourceTableCrudService,
    ) {}

    #[OA\Get(
        path: '/api/v1/bridge-source/tables',
        operationId: 'bridgeSourceTableCatalog',
        summary: 'Katalog tabel source bridge CRUD',
        description: 'Mengembalikan daftar tabel source/lookup yang diizinkan untuk API CRUD modul Jembatan, termasuk endpoint schema dan record pada masing-masing tabel.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Katalog tabel source bridge berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Katalog tabel source bridge berhasil diambil.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BridgeSourceTableCatalogResource')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function catalog(): JsonResponse
    {
        return ApiResponse::success(
            'Katalog tabel source bridge berhasil diambil.',
            BridgeSourceTableCatalogResource::collection($this->bridgeSourceTableCrudService->catalog())->resolve(),
        );
    }

    #[OA\Get(
        path: '/api/v1/bridge-source/tables/{table}/schema',
        operationId: 'bridgeSourceTableSchema',
        summary: 'Struktur data satu tabel source bridge',
        description: 'Menampilkan struktur kolom, primary key, unique key, required columns, dan index dari satu tabel source bridge seperti `m_kabkot`, `m_lintas`, `m_stasiun`, dan lainnya.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_kabkot')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Schema tabel source berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Schema tabel source berhasil diambil.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/BridgeSourceTableSchemaResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function schema(string $table): JsonResponse
    {
        return ApiResponse::success(
            'Schema tabel source berhasil diambil.',
            BridgeSourceTableSchemaResource::make($this->bridgeSourceTableCrudService->schema($table))->resolve(),
        );
    }

    #[OA\Get(
        path: '/api/v1/bridge-source/tables/{table}/records',
        operationId: 'bridgeSourceTableRecordIndex',
        summary: 'Daftar record pada tabel source bridge',
        description: 'Mengambil daftar record paginasi dari tabel source bridge yang diizinkan untuk API CRUD, lengkap dengan `row_key` dan payload data mentah setiap baris.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_wilayah_kerja')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar record tabel source berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data tabel source berhasil diambil.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BridgeSourceTableRowResource')),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'request_id', type: 'string', nullable: true),
                                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta'),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(ListBridgeSourceTableRecordRequest $request, string $table): JsonResponse
    {
        $records = $this->bridgeSourceTableCrudService->paginate($table, $request->validated());

        return ApiResponse::paginated(
            'Data tabel source berhasil diambil.',
            BridgeSourceTableRowResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    #[OA\Post(
        path: '/api/v1/bridge-source/tables/{table}/records',
        operationId: 'bridgeSourceTableRecordStore',
        summary: 'Tambah record ke tabel source bridge',
        description: 'Membuat record baru pada tabel source/lookup bridge yang diizinkan oleh sistem. Payload dikirim dalam objek `data` dan akan difilter berdasarkan kolom tabel aktual.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_wilayah_kerja')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BridgeSourceTableRecordStoreRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Record tabel source berhasil dibuat',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Record tabel source berhasil dibuat.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/BridgeSourceTableRowResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function store(StoreBridgeSourceTableRecordRequest $request, string $table): JsonResponse
    {
        return ApiResponse::success(
            'Record tabel source berhasil dibuat.',
            BridgeSourceTableRowResource::make(
                $this->bridgeSourceTableCrudService->create($table, $request->validated(), $request->user())
            )->resolve(),
            status: 201,
        );
    }

    #[OA\Get(
        path: '/api/v1/bridge-source/tables/{table}/records/{rowKey}',
        operationId: 'bridgeSourceTableRecordShow',
        summary: 'Detail satu record tabel source bridge',
        description: 'Mengambil satu record dari tabel source bridge berdasarkan `row_key` yang diselesaikan dari kolom `uniqid`, `kode`, `uuid`, `code`, `id`, atau `nama` bila tersedia.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_wilayah_kerja')),
            new OA\Parameter(name: 'rowKey', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '62e60276e589f')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail record tabel source berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Detail record tabel source berhasil diambil.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/BridgeSourceTableRowResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function show(string $table, string $rowKey): JsonResponse
    {
        return ApiResponse::success(
            'Detail record tabel source berhasil diambil.',
            BridgeSourceTableRowResource::make($this->bridgeSourceTableCrudService->find($table, $rowKey))->resolve(),
        );
    }

    #[OA\Patch(
        path: '/api/v1/bridge-source/tables/{table}/records/{rowKey}',
        operationId: 'bridgeSourceTableRecordUpdate',
        summary: 'Perbarui record tabel source bridge',
        description: 'Memperbarui satu record dari tabel source/lookup bridge yang diizinkan menggunakan payload parsial `data`.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_wilayah_kerja')),
            new OA\Parameter(name: 'rowKey', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '62e60276e589f')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BridgeSourceTableRecordUpdateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Record tabel source berhasil diperbarui',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Record tabel source berhasil diperbarui.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/BridgeSourceTableRowResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function update(UpdateBridgeSourceTableRecordRequest $request, string $table, string $rowKey): JsonResponse
    {
        return ApiResponse::success(
            'Record tabel source berhasil diperbarui.',
            BridgeSourceTableRowResource::make(
                $this->bridgeSourceTableCrudService->update($table, $rowKey, $request->validated(), $request->user())
            )->resolve(),
        );
    }

    #[OA\Delete(
        path: '/api/v1/bridge-source/tables/{table}/records/{rowKey}',
        operationId: 'bridgeSourceTableRecordDelete',
        summary: 'Hapus record tabel source bridge',
        description: 'Menghapus satu record dari tabel source/lookup bridge yang diizinkan berdasarkan `row_key`.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_wilayah_kerja')),
            new OA\Parameter(name: 'rowKey', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '62e60276e589f')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Record tabel source berhasil dihapus'),
        ]
    )]
    public function destroy(string $table, string $rowKey): JsonResponse
    {
        $this->bridgeSourceTableCrudService->delete($table, $rowKey);

        return ApiResponse::success('Record tabel source berhasil dihapus.');
    }
}
