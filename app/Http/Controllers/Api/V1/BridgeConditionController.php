<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BridgeConditionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Attributes as OA;

class BridgeConditionController extends Controller
{
    public function __construct(
        private readonly BridgeConditionService $bridgeConditionService,
    ) {}

    #[OA\Get(
        path: '/api/v1/bridges/{kode_jembatan}/condition',
        operationId: 'bridgeCondition',
        summary: 'Mengambil nilai kondisi jembatan',
        description: 'Endpoint ini mengambil nilai kondisi jembatan dari m_jembatan_nilai_atas, m_jembatan_nilai_bawah, m_jembatan_nilai_pelindung, dan m_jembatan_nilai_total.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'kode_jembatan', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function condition(string $kodeJembatan): JsonResponse
    {
        $condition = $this->bridgeConditionService->condition($kodeJembatan);

        if ($condition === null) {
            throw new NotFoundHttpException('Data jembatan tidak ditemukan.');
        }

        return ApiResponse::success('Nilai kondisi jembatan berhasil diambil.', $condition);
    }
}
