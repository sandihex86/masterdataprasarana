<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class BridgeIntegrationController extends Controller
{
    #[OA\Get(
        path: '/api/v1/integration/health',
        operationId: 'bridgeIntegrationHealth',
        summary: 'Health check Bridge API',
        description: 'Endpoint ini digunakan sistem lain untuk memastikan Master Data Jembatan API berjalan dan siap diakses.',
        tags: ['Bridges'],
        responses: [
            new OA\Response(response: 200, description: 'Bridge API berjalan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function health(): JsonResponse
    {
        return ApiResponse::success('Bridge API is running', [
            'service' => 'Master Data Jembatan API',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
