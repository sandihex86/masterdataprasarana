<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBridgeMaintenanceRequest;
use App\Services\BridgeMaintenanceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Attributes as OA;

class BridgeMaintenanceController extends Controller
{
    public function __construct(
        private readonly BridgeMaintenanceService $bridgeMaintenanceService,
    ) {}

    #[OA\Get(
        path: '/api/v1/bridges/{kode_jembatan}/maintenance',
        operationId: 'bridgeMaintenanceIndex',
        summary: 'Mengambil riwayat perawatan jembatan',
        description: 'Endpoint ini mengambil riwayat perawatan jembatan dari tabel m_jembatan_perawatan dengan pagination dan filter tanggal atau pemeriksa.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'kode_jembatan', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 50)),
            new OA\Parameter(name: 'tanggal_mulai', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'tanggal_selesai', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'pemeriksa', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function index(Request $request, string $kodeJembatan): JsonResponse
    {
        $records = $this->bridgeMaintenanceService->paginateForBridge($kodeJembatan, $request->query());

        if ($records === null) {
            throw new NotFoundHttpException('Data jembatan tidak ditemukan.');
        }

        return ApiResponse::paginated('Riwayat perawatan jembatan berhasil diambil.', $records->items(), $records);
    }

    #[OA\Post(
        path: '/api/v1/bridges/{kode_jembatan}/maintenance',
        operationId: 'bridgeMaintenanceStore',
        summary: 'Membuat data perawatan jembatan',
        description: 'Endpoint ini membuat data perawatan baru. Saat create, data referensi jembatan disalin dari m_jembatan berdasarkan kode_jembatan agar riwayat perawatan tetap membawa konteks master.',
        tags: ['Bridges'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'kode_jembatan', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object', required: ['tanggal'], properties: [new OA\Property(property: 'tanggal', type: 'string', format: 'date', example: '2026-06-13'), new OA\Property(property: 'pemeriksa', type: 'string', nullable: true, example: 'Petugas API'), new OA\Property(property: 'lat', type: 'number', nullable: true, example: -6.2), new OA\Property(property: 'lon', type: 'number', nullable: true, example: 106.8), new OA\Property(property: 'catatan', type: 'string', nullable: true), new OA\Property(property: 'dokumen', type: 'string', nullable: true), new OA\Property(property: 'active', type: 'boolean', nullable: true)])),
        responses: [
            new OA\Response(response: 200, description: 'Data berhasil disimpan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function store(StoreBridgeMaintenanceRequest $request, string $kodeJembatan): JsonResponse
    {
        $record = $this->bridgeMaintenanceService->create($kodeJembatan, $request->validated(), $request->user());

        if ($record === null) {
            throw new NotFoundHttpException('Data jembatan tidak ditemukan.');
        }

        return ApiResponse::success('Data perawatan jembatan berhasil disimpan.', $record, status: 201);
    }
}
