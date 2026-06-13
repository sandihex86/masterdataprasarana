<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BridgeGeoService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BridgeMapController extends Controller
{
    public function __construct(
        private readonly BridgeGeoService $bridgeGeoService,
    ) {}

    #[OA\Get(
        path: '/api/v1/master/bridges/geojson',
        operationId: 'bridgeGeoJson',
        summary: 'Mengambil GeoJSON jembatan',
        description: 'Endpoint ini mengembalikan titik lokasi jembatan dalam format GeoJSON FeatureCollection dan hanya memasukkan data dengan lat/lon valid.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'wil_op', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'wil_ker', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'lintas', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function geoJson(Request $request): JsonResponse
    {
        return ApiResponse::success(
            'GeoJSON jembatan berhasil diambil.',
            $this->bridgeGeoService->geoJson($request->query()),
        );
    }
}
