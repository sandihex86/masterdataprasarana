<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListBridgeSourceTableRecordRequest;
use App\Services\ReferenceSourceTableService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReferenceController extends Controller
{
    public function __construct(
        private readonly ReferenceSourceTableService $referenceSourceTableService,
    ) {}

    #[OA\Get(
        path: '/api/v1/references/tables',
        operationId: 'referenceTableCatalog',
        summary: 'Katalog tabel referensi',
        description: 'Mengambil daftar tabel Referensi yang dikelola pada database prasarana_referensi beserta jumlah row aktif.',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Katalog referensi berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Katalog tabel referensi berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReferenceTableCatalogResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function tables(): JsonResponse
    {
        return ApiResponse::success(
            'Katalog tabel referensi berhasil diambil.',
            $this->referenceSourceTableService->catalog(),
        );
    }

    #[OA\Get(
        path: '/api/v1/references/tables/{table}/schema',
        operationId: 'referenceTableSchema',
        summary: 'Schema tabel referensi',
        description: 'Mengambil metadata kolom, primary key, required columns, dan index dari satu tabel Referensi.',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_stasiun'))],
        responses: [
            new OA\Response(response: 200, description: 'Schema referensi berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Tabel tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function schema(string $table): JsonResponse
    {
        return ApiResponse::success(
            'Schema tabel referensi berhasil diambil.',
            $this->referenceSourceTableService->schema($table),
        );
    }

    #[OA\Get(
        path: '/api/v1/references/tables/{table}/records',
        operationId: 'referenceTableRecords',
        summary: 'Daftar record tabel referensi',
        description: 'Mengambil row aktif dari satu tabel Referensi dengan pagination dan pencarian lintas kolom.',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_stasiun')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Gambir')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 25)),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data referensi berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data tabel referensi berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReferenceTableRowResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Tabel tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function index(ListBridgeSourceTableRecordRequest $request, string $table): JsonResponse
    {
        $records = $this->referenceSourceTableService->paginate($table, $request->validated());

        return ApiResponse::paginated(
            'Data tabel referensi berhasil diambil.',
            $records->items(),
            $records,
            [
                'reference_source_table' => [
                    'table' => $table,
                ],
            ],
        );
    }

    #[OA\Post(
        path: '/api/v1/references/tables/{table}/records',
        operationId: 'referenceTableRecordStore',
        summary: 'Tambah record tabel referensi',
        description: 'Membuat row baru pada tabel Referensi. Payload dikirim dalam properti `data` sesuai kolom tabel.',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'provinsi'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReferenceTableRecordRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Record referensi berhasil dibuat', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function store(Request $request, string $table): JsonResponse
    {
        return ApiResponse::success(
            'Data tabel referensi berhasil dibuat.',
            $this->referenceSourceTableService->create($table, $request->validate([
                'data' => ['required', 'array'],
            ])),
            status: 201,
        );
    }

    #[OA\Get(
        path: '/api/v1/references/tables/{table}/records/{rowKey}',
        operationId: 'referenceTableRecordShow',
        summary: 'Detail record tabel referensi',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'm_stasiun')),
            new OA\Parameter(name: 'rowKey', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '1')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Record referensi berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Record tidak ditemukan atau tabel tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function show(string $table, string $rowKey): JsonResponse
    {
        return ApiResponse::success(
            'Data tabel referensi berhasil diambil.',
            $this->referenceSourceTableService->find($table, $rowKey),
        );
    }

    #[OA\Patch(
        path: '/api/v1/references/tables/{table}/records/{rowKey}',
        operationId: 'referenceTableRecordUpdate',
        summary: 'Perbarui record tabel referensi',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'provinsi')),
            new OA\Parameter(name: 'rowKey', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '1')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReferenceTableRecordRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Record referensi berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    #[OA\Put(
        path: '/api/v1/references/tables/{table}/records/{rowKey}',
        operationId: 'referenceTableRecordReplace',
        summary: 'Perbarui record tabel referensi',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'provinsi')),
            new OA\Parameter(name: 'rowKey', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '1')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReferenceTableRecordRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Record referensi berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function update(Request $request, string $table, string $rowKey): JsonResponse
    {
        return ApiResponse::success(
            'Data tabel referensi berhasil diperbarui.',
            $this->referenceSourceTableService->update($table, $rowKey, $request->validate([
                'data' => ['required', 'array'],
            ])),
        );
    }

    #[OA\Delete(
        path: '/api/v1/references/tables/{table}/records/{rowKey}',
        operationId: 'referenceTableRecordDestroy',
        summary: 'Hapus record tabel referensi',
        description: 'Melakukan soft delete pada row Referensi jika tabel memiliki kolom `deleted_at`.',
        tags: ['References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: 'provinsi')),
            new OA\Parameter(name: 'rowKey', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '1')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Record referensi berhasil dihapus', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Record tidak ditemukan atau tabel tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function destroy(string $table, string $rowKey): JsonResponse
    {
        $this->referenceSourceTableService->delete($table, $rowKey);

        return ApiResponse::success('Data tabel referensi berhasil dihapus.');
    }
}
