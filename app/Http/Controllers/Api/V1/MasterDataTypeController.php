<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMasterDataRequest;
use App\Http\Resources\Api\V1\MasterDataResource;
use App\Http\Resources\Api\V1\MasterDataTypeResource;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Services\MasterData\MasterDataQueryService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MasterDataTypeController extends Controller
{
    public function __construct(
        private readonly MasterDataQueryService $queryService,
    ) {}

    #[OA\Get(
        path: '/api/v1/master-data-types',
        operationId: 'masterDataTypeIndex',
        summary: 'Daftar tipe master data aktif',
        tags: ['Master Data Types'],
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar tipe berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data berhasil diambil.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MasterDataTypeResource')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        $this->authorize('viewAny', MasterDataType::class);

        $types = MasterDataType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataTypeResource::collection($types)->resolve(),
        );
    }

    #[OA\Get(
        path: '/api/v1/master-data-types/{code}',
        operationId: 'masterDataTypeShow',
        summary: 'Detail tipe master data berdasarkan kode',
        tags: ['Master Data Types'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail tipe berhasil diambil'),
            new OA\Response(response: 404, description: 'Tipe tidak ditemukan'),
        ]
    )]
    public function show(MasterDataType $masterDataType)
    {
        $this->authorize('view', $masterDataType);

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataTypeResource::make($masterDataType)->resolve(),
        );
    }

    #[OA\Get(
        path: '/api/v1/master-data-types/{code}/records',
        operationId: 'masterDataTypeRecords',
        summary: 'Daftar record berdasarkan tipe master data',
        tags: ['Master Data Types'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar record berhasil diambil'),
            new OA\Response(response: 404, description: 'Tipe tidak ditemukan'),
        ]
    )]
    public function records(ListMasterDataRequest $request, MasterDataType $masterDataType)
    {
        $this->authorize('view', $masterDataType);

        $request->merge(['type' => $masterDataType->code]);
        $records = $this->queryService->paginate($request);

        return ApiResponse::paginated(
            'Data berhasil diambil.',
            MasterDataResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    #[OA\Get(
        path: '/api/v1/master-data-types/{code}/records/{recordCode}',
        operationId: 'masterDataTypeRecord',
        summary: 'Detail record berdasarkan tipe dan kode record',
        tags: ['Master Data Types'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'recordCode', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail record berhasil diambil'),
            new OA\Response(response: 404, description: 'Record tidak ditemukan'),
        ]
    )]
    public function record(Request $request, MasterDataType $masterDataType, string $recordCode)
    {
        $this->authorize('view', $masterDataType);

        $record = MasterData::query()
            ->with('type')
            ->where('entity_type', $masterDataType->code)
            ->where('code', $recordCode)
            ->firstOrFail();

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataResource::make($record)->resolve(),
        );
    }
}
