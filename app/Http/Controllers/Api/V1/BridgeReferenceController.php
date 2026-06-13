<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BridgeReferenceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Attributes as OA;

class BridgeReferenceController extends Controller
{
    public function __construct(
        private readonly BridgeReferenceService $bridgeReferenceService,
    ) {}

    #[OA\Get(path: '/api/v1/references/provinces', operationId: 'bridgeReferenceProvinces', summary: 'Mengambil referensi provinsi', description: 'Endpoint ini menyediakan data m_provinsi untuk dropdown dan filter Bridge API.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function provinces(Request $request): JsonResponse
    {
        return $this->responseFor('provinces', $request);
    }

    #[OA\Get(path: '/api/v1/references/cities', operationId: 'bridgeReferenceCities', summary: 'Mengambil referensi kabupaten/kota', description: 'Endpoint ini menyediakan data m_kabkot untuk dropdown dan filter Bridge API.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function cities(Request $request): JsonResponse
    {
        return $this->responseFor('cities', $request);
    }

    #[OA\Get(path: '/api/v1/references/operation-areas', operationId: 'bridgeReferenceOperationAreas', summary: 'Mengambil referensi wilayah operasi', description: 'Endpoint ini menyediakan data m_wilayah_operasi untuk dropdown dan filter Bridge API.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function operationAreas(Request $request): JsonResponse
    {
        return $this->responseFor('operation-areas', $request);
    }

    #[OA\Get(path: '/api/v1/references/work-areas', operationId: 'bridgeReferenceWorkAreas', summary: 'Mengambil referensi wilayah kerja', description: 'Endpoint ini menyediakan data m_wilayah_kerja untuk dropdown dan filter Bridge API.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function workAreas(Request $request): JsonResponse
    {
        return $this->responseFor('work-areas', $request);
    }

    #[OA\Get(path: '/api/v1/references/routes', operationId: 'bridgeReferenceRoutes', summary: 'Mengambil referensi lintas', description: 'Endpoint ini menyediakan data m_lintas untuk dropdown dan filter Bridge API.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function routes(Request $request): JsonResponse
    {
        return $this->responseFor('routes', $request);
    }

    #[OA\Get(path: '/api/v1/references/stations', operationId: 'bridgeReferenceStations', summary: 'Mengambil referensi stasiun', description: 'Endpoint ini menyediakan data m_stasiun untuk dropdown dan filter Bridge API.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function stations(Request $request): JsonResponse
    {
        return $this->responseFor('stations', $request);
    }

    #[OA\Get(path: '/api/v1/references/segments', operationId: 'bridgeReferenceSegments', summary: 'Mengambil referensi petak', description: 'Endpoint ini menyediakan data m_petak untuk dropdown dan filter Bridge API.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1000))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function segments(Request $request): JsonResponse
    {
        return $this->responseFor('segments', $request);
    }

    private function responseFor(string $type, Request $request): JsonResponse
    {
        $data = $this->bridgeReferenceService->list($type, $request->query());

        if ($data === null) {
            throw new NotFoundHttpException('Referensi tidak ditemukan.');
        }

        return ApiResponse::success('Data referensi berhasil diambil.', $data);
    }
}
