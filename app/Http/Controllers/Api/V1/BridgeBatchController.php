<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BridgeBatchRequest;
use App\Http\Requests\Api\V1\BridgeChangedRequest;
use App\Services\BridgeBatchService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class BridgeBatchController extends Controller
{
    public function __construct(
        private readonly BridgeBatchService $bridgeBatchService,
    ) {}

    #[OA\Get(
        path: '/api/v1/master/bridges/batch',
        operationId: 'bridgeMasterBatch',
        summary: 'Mengambil batch master jembatan',
        description: 'Endpoint ini mengambil data utama jembatan dalam jumlah besar menggunakan cursor berbasis id agar integrasi aplikasi lain tidak bergantung pada OFFSET dan tetap aman untuk sekitar 3.076 record.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 500)),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000)),
            new OA\Parameter(name: 'last_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000)),
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'updated_since', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 401, description: 'Autentikasi bearer token diperlukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function batch(BridgeBatchRequest $request): JsonResponse
    {
        $result = $this->bridgeBatchService->batch($request->validated());

        return ApiResponse::success('Batch data jembatan berhasil diambil.', $result['data'], $result['meta']);
    }

    #[OA\Get(
        path: '/api/v1/master/bridges/full-batch',
        operationId: 'bridgeMasterFullBatch',
        summary: 'Mengambil batch jembatan lengkap',
        description: 'Endpoint ini mengambil data jembatan lengkap dengan limit kecil. Data utama diambil lebih dulu, lalu detail one-to-many seperti bentang, baja, beton, bawah, survey, dan perawatan dimuat dengan whereIn dan dikembalikan sebagai array terpisah agar tidak terjadi duplikasi akibat JOIN besar.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 100)),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000)),
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'updated_since', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 401, description: 'Autentikasi bearer token diperlukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function fullBatch(BridgeBatchRequest $request): JsonResponse
    {
        $result = $this->bridgeBatchService->fullBatch($request->validated());

        return ApiResponse::success('Full batch data jembatan berhasil diambil.', $result['data'], $result['meta']);
    }

    #[OA\Get(
        path: '/api/v1/master/bridges/changed',
        operationId: 'bridgeMasterChanged',
        summary: 'Mengambil data jembatan yang berubah',
        description: 'Endpoint ini digunakan aplikasi lain untuk sinkronisasi incremental berdasarkan kolom updated_at sehingga tidak perlu menarik seluruh master jembatan setiap saat.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'since', in: 'query', required: true, schema: new OA\Schema(type: 'string', example: '2026-06-01 00:00:00')),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 500)),
            new OA\Parameter(name: 'cursor', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 401, description: 'Autentikasi bearer token diperlukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function changed(BridgeChangedRequest $request): JsonResponse
    {
        $result = $this->bridgeBatchService->changed($request->validated());

        return ApiResponse::success('Data perubahan jembatan berhasil diambil.', $result['data'], $result['meta']);
    }
}
