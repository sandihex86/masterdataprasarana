<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BridgeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Attributes as OA;

class BridgeTechnicalController extends Controller
{
    public function __construct(
        private readonly BridgeService $bridgeService,
    ) {}

    #[OA\Get(path: '/api/v1/master/bridges/{kode_jembatan}/profile', operationId: 'bridgeProfile', summary: 'Mengambil profil teknis jembatan', description: 'Endpoint ini mengambil profil teknis utama dari tabel m_jembatan_profil berdasarkan kode_jembatan m_jembatan.uniqid.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'kode_jembatan', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function profile(string $kodeJembatan): JsonResponse
    {
        $detail = $this->bridgeService->findDetail($kodeJembatan);

        if ($detail === null) {
            throw new NotFoundHttpException('Data jembatan tidak ditemukan.');
        }

        return ApiResponse::success('Profil jembatan berhasil diambil.', $detail['profil']);
    }

    #[OA\Get(path: '/api/v1/master/bridges/{kode_jembatan}/spans', operationId: 'bridgeSpans', summary: 'Mengambil bentang jembatan', description: 'Endpoint ini mengambil daftar bentang jembatan dari tabel m_jembatan_bentang sebagai array terpisah.', tags: ['Bridges'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'kode_jembatan', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function spans(string $kodeJembatan): JsonResponse
    {
        $detail = $this->bridgeService->findDetail($kodeJembatan);

        if ($detail === null) {
            throw new NotFoundHttpException('Data jembatan tidak ditemukan.');
        }

        return ApiResponse::success('Bentang jembatan berhasil diambil.', $this->bridgeService->rowsByBridge('m_jembatan_bentang', $kodeJembatan));
    }
}
