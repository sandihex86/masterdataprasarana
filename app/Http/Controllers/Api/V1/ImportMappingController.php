<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PreviewImportMappingRequest;
use App\Http\Requests\Api\V1\StoreImportMappingRequest;
use App\Http\Resources\Api\V1\ImportMappingResource;
use App\Models\ImportMapping;
use App\Services\Import\MappingService;
use App\Support\ApiResponse;
use OpenApi\Attributes as OA;

class ImportMappingController extends Controller
{
    public function __construct(
        private readonly MappingService $mappingService,
    ) {}

    #[OA\Get(
        path: '/api/v1/import-mappings',
        operationId: 'importMappingIndex',
        summary: 'Daftar konfigurasi import mapping',
        tags: ['Import Mappings'],
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar mapping berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data berhasil diambil.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ImportMappingResource')),
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
    public function index()
    {
        $this->authorize('viewAny', ImportMapping::class);

        $mappings = ImportMapping::query()
            ->orderByDesc('updated_at')
            ->paginate(config('master-data.pagination.default_per_page'));

        return ApiResponse::paginated(
            'Data berhasil diambil.',
            ImportMappingResource::collection($mappings->getCollection())->resolve(),
            $mappings,
        );
    }

    #[OA\Get(
        path: '/api/v1/import-mappings/{uuid}',
        operationId: 'importMappingShow',
        summary: 'Detail konfigurasi import mapping',
        tags: ['Import Mappings'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail mapping berhasil diambil'),
            new OA\Response(response: 404, description: 'Mapping tidak ditemukan'),
        ]
    )]
    public function show(ImportMapping $importMapping)
    {
        $this->authorize('view', $importMapping);

        return ApiResponse::success(
            'Data berhasil diambil.',
            ImportMappingResource::make($importMapping)->resolve(),
        );
    }

    #[OA\Post(
        path: '/api/v1/import-mappings',
        operationId: 'importMappingStore',
        summary: 'Membuat konfigurasi import mapping baru',
        tags: ['Import Mappings'],
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ImportMappingRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Mapping berhasil dibuat'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function store(StoreImportMappingRequest $request)
    {
        $this->authorize('create', ImportMapping::class);

        $mapping = $this->mappingService->persist($request->validated());

        return ApiResponse::success(
            'Mapping berhasil dibuat.',
            ImportMappingResource::make($mapping)->resolve(),
            status: 201,
        );
    }

    #[OA\Put(
        path: '/api/v1/import-mappings/{uuid}',
        operationId: 'importMappingUpdate',
        summary: 'Memperbarui konfigurasi import mapping',
        tags: ['Import Mappings'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'uuid', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ImportMappingRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mapping berhasil diperbarui'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function update(StoreImportMappingRequest $request, ImportMapping $importMapping)
    {
        $this->authorize('update', $importMapping);

        $mapping = $this->mappingService->persist($request->validated(), $importMapping);

        return ApiResponse::success(
            'Mapping berhasil diperbarui.',
            ImportMappingResource::make($mapping)->resolve(),
        );
    }

    #[OA\Post(
        path: '/api/v1/import-mappings/preview',
        operationId: 'importMappingPreview',
        summary: 'Preview transformasi hasil import mapping',
        tags: ['Import Mappings'],
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ImportMappingPreviewRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Preview berhasil dibuat',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Preview mapping berhasil dibuat.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'items',
                                    type: 'array',
                                    items: new OA\Items(type: 'object', additionalProperties: true)
                                ),
                                new OA\Property(property: 'count', type: 'integer', example: 3),
                            ]
                        ),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function preview(PreviewImportMappingRequest $request)
    {
        $this->authorize('create', ImportMapping::class);

        $configuration = $request->validated('mapping');

        if ($request->filled('mapping_uuid')) {
            $configuration = ImportMapping::query()
                ->where('uuid', $request->string('mapping_uuid')->toString())
                ->firstOrFail(['mapping'])
                ->mapping;
        }

        $limit = $request->integer('limit', config('master-data.import.preview_limit'));
        $preview = $this->mappingService->preview($configuration, $limit);

        return ApiResponse::success(
            'Preview mapping berhasil dibuat.',
            [
                'items' => $preview,
                'count' => count($preview),
            ],
        );
    }
}
