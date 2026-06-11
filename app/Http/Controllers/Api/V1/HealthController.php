<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Throwable;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/v1/health/live',
        operationId: 'healthLive',
        summary: 'Liveness probe aplikasi',
        tags: ['Health'],
        security: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Aplikasi hidup',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                    ]
                )
            ),
        ]
    )]
    public function live()
    {
        return response()->json(['status' => 'ok']);
    }

    #[OA\Get(
        path: '/api/v1/health/ready',
        operationId: 'healthReady',
        summary: 'Readiness probe aplikasi',
        tags: ['Health'],
        security: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Aplikasi siap digunakan',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Aplikasi siap digunakan.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'ok'),
                                new OA\Property(
                                    property: 'checks',
                                    type: 'object',
                                    additionalProperties: new OA\AdditionalProperties(type: 'boolean')
                                ),
                            ]
                        ),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
            new OA\Response(response: 503, description: 'Sebagian layanan belum siap'),
        ]
    )]
    public function ready()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => is_writable(storage_path('app')),
        ];

        $status = in_array(false, $checks, true) ? 503 : 200;

        return ApiResponse::success(
            message: $status === 200 ? 'Aplikasi siap digunakan.' : 'Sebagian layanan belum siap.',
            data: [
                'status' => $status === 200 ? 'ok' : 'degraded',
                'checks' => $checks,
            ],
            status: $status,
        );
    }

    #[OA\Get(
        path: '/api/v1/health',
        operationId: 'healthSummary',
        summary: 'Ringkasan health dan readiness aplikasi',
        tags: ['Health'],
        security: [],
        responses: [
            new OA\Response(response: 200, description: 'Ringkasan health tersedia'),
            new OA\Response(response: 503, description: 'Aplikasi terdegradasi'),
        ]
    )]
    public function summary()
    {
        return $this->ready();
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
