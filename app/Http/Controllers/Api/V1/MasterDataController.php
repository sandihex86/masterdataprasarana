<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMasterDataRequest;
use App\Http\Requests\Api\V1\StoreMasterDataRequest;
use App\Http\Requests\Api\V1\UpdateMasterDataRequest;
use App\Http\Resources\Api\V1\MasterDataResource;
use App\Models\MasterData;
use App\Services\MasterData\MasterDataQueryService;
use App\Services\MasterData\MasterDataWriteService;
use App\Support\ApiResponse;
use OpenApi\Attributes as OA;

class MasterDataController extends Controller
{
    public function __construct(
        private readonly MasterDataQueryService $queryService,
        private readonly MasterDataWriteService $writeService,
    ) {}

    #[OA\Get(
        path: '/api/v1/master-data',
        operationId: 'masterDataIndex',
        summary: 'Daftar master data',
        tags: ['Master Data'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'code', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'parent_code', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'source_system', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'source_table', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar master data berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data berhasil diambil.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/MasterDataResource')
                        ),
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
            new OA\Response(response: 401, description: 'Autentikasi diperlukan'),
            new OA\Response(response: 403, description: 'Akses ditolak'),
        ]
    )]
    public function index(ListMasterDataRequest $request)
    {
        $this->authorize('viewAny', MasterData::class);

        $records = $this->queryService->paginate($request);

        return ApiResponse::paginated(
            'Data berhasil diambil.',
            MasterDataResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    #[OA\Get(
        path: '/api/v1/master-data/{uuid}',
        operationId: 'masterDataShow',
        summary: 'Detail master data berdasarkan UUID',
        tags: ['Master Data'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail master data berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data berhasil diambil.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/MasterDataResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function show(MasterData $masterData)
    {
        $this->authorize('view', $masterData);
        $masterData->load('type');

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataResource::make($masterData)->resolve(),
        );
    }

    #[OA\Post(
        path: '/api/v1/master-data',
        operationId: 'masterDataStore',
        summary: 'Membuat master data baru',
        tags: ['Master Data'],
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/MasterDataStoreRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Data berhasil dibuat'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function store(StoreMasterDataRequest $request)
    {
        $this->authorize('create', MasterData::class);

        $record = $this->writeService->create($request->validated());

        return ApiResponse::success(
            'Data berhasil dibuat.',
            MasterDataResource::make($record)->resolve(),
            status: 201,
        );
    }

    #[OA\Put(
        path: '/api/v1/master-data/{uuid}',
        operationId: 'masterDataUpdate',
        summary: 'Memperbarui master data',
        tags: ['Master Data'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/MasterDataUpdateRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diperbarui'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    #[OA\Patch(
        path: '/api/v1/master-data/{uuid}',
        operationId: 'masterDataPatch',
        summary: 'Memperbarui sebagian field master data',
        tags: ['Master Data'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/MasterDataUpdateRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diperbarui'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function update(UpdateMasterDataRequest $request, MasterData $masterData)
    {
        $this->authorize('update', $masterData);

        $record = $this->writeService->update($masterData, $request->validated());

        return ApiResponse::success(
            'Data berhasil diperbarui.',
            MasterDataResource::make($record)->resolve(),
        );
    }

    #[OA\Delete(
        path: '/api/v1/master-data/{uuid}',
        operationId: 'masterDataDestroy',
        summary: 'Soft delete master data',
        tags: ['Master Data'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil dihapus'),
        ]
    )]
    public function destroy(MasterData $masterData)
    {
        $this->authorize('delete', $masterData);

        $this->writeService->delete($masterData);

        return ApiResponse::success('Data berhasil dihapus.');
    }

    #[OA\Post(
        path: '/api/v1/master-data/{uuid}/restore',
        operationId: 'masterDataRestore',
        summary: 'Memulihkan master data yang sudah dihapus',
        tags: ['Master Data'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil dipulihkan'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function restore(string $masterDataUuid)
    {
        $record = MasterData::withTrashed()->where('uuid', $masterDataUuid)->firstOrFail();
        $this->authorize('restore', $record);

        $record = $this->writeService->restore($record);

        return ApiResponse::success(
            'Data berhasil dipulihkan.',
            MasterDataResource::make($record)->resolve(),
        );
    }
}
