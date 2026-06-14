<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WarehouseBatchRequest;
use App\Http\Requests\Api\V1\ListWarehouseRequest;
use App\Http\Requests\Api\V1\StoreWarehouseRequest;
use App\Http\Requests\Api\V1\UpdateWarehouseRequest;
use App\Http\Resources\Api\V1\WarehouseResource;
use App\Services\WarehouseService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseService $warehouseService,
    ) {}

    #[OA\Get(
        path: '/api/v1/warehouses',
        operationId: 'warehouseIndex',
        summary: 'Daftar master data gudang',
        description: 'Mengambil daftar master data gudang dari database prasarana_warehouse dengan pagination, pencarian, filter wilayah/status, dan sorting terbatas pada kolom aman.',
        tags: ['Warehouses'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Payakabung')),
            new OA\Parameter(name: 'tipe_gudang', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Material')),
            new OA\Parameter(name: 'id_wilker', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'DIVRE3')),
            new OA\Parameter(name: 'id_prov', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: '16')),
            new OA\Parameter(name: 'id_kabkot', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: '1607')),
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 25)),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'updated_at')),
            new OA\Parameter(name: 'sort_dir', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], example: 'desc')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data gudang berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data gudang berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/WarehouseResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function index(ListWarehouseRequest $request): JsonResponse
    {
        $records = $this->warehouseService->paginate($request->validated());

        return ApiResponse::paginated(
            'Data gudang berhasil diambil.',
            WarehouseResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    #[OA\Post(
        path: '/api/v1/warehouses',
        operationId: 'warehouseStore',
        summary: 'Tambah master data gudang',
        description: 'Membuat gudang baru. `id_gudang` dan `kode_gudang` digenerate otomatis memakai ULID dan selalu memiliki nilai yang sama.',
        tags: ['Warehouses'],
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/WarehouseStoreRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Gudang berhasil dibuat', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data gudang berhasil dibuat.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/WarehouseResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        return ApiResponse::success(
            'Data gudang berhasil dibuat.',
            WarehouseResource::make($this->warehouseService->create($request->validated()))->resolve(),
            status: 201,
        );
    }

    #[OA\Get(
        path: '/api/v1/warehouses/batch',
        operationId: 'warehouseBatch',
        summary: 'Batch semua master data gudang',
        description: 'Mengambil seluruh data gudang non-deleted dari database prasarana_warehouse untuk kebutuhan sinkronisasi aplikasi lain. `id_gudang` dan `kode_gudang` selalu bernilai sama.',
        tags: ['Warehouses'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'updated_since', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time', example: '2026-06-01 00:00:00')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Batch data gudang berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Batch data gudang berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/WarehouseResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function batch(WarehouseBatchRequest $request): JsonResponse
    {
        $result = $this->warehouseService->batch($request->validated());

        return ApiResponse::success(
            'Batch data gudang berhasil diambil.',
            WarehouseResource::collection($result['data'])->resolve(),
            $result['meta'],
        );
    }

    #[OA\Get(
        path: '/api/v1/warehouses/{id_gudang}',
        operationId: 'warehouseShow',
        summary: 'Detail master data gudang',
        description: 'Mengambil detail satu gudang berdasarkan public identifier `id_gudang`. Nilai `kode_gudang` sama dengan `id_gudang`.',
        tags: ['Warehouses'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'id_gudang', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '01JY0000000000000000000000'))],
        responses: [
            new OA\Response(response: 200, description: 'Detail gudang berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data gudang berhasil diambil.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/WarehouseResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function show(string $id_gudang): JsonResponse
    {
        return ApiResponse::success(
            'Data gudang berhasil diambil.',
            WarehouseResource::make($this->warehouseService->find($id_gudang))->resolve(),
        );
    }

    #[OA\Patch(
        path: '/api/v1/warehouses/{id_gudang}',
        operationId: 'warehouseUpdate',
        summary: 'Perbarui master data gudang',
        description: 'Memperbarui gudang berdasarkan `id_gudang`. Field identitas `id_gudang` dan `kode_gudang` tidak dapat diubah manual.',
        tags: ['Warehouses'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'id_gudang', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/WarehouseUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Gudang berhasil diperbarui', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data gudang berhasil diperbarui.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/WarehouseResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    #[OA\Put(
        path: '/api/v1/warehouses/{id_gudang}',
        operationId: 'warehouseReplace',
        summary: 'Perbarui master data gudang',
        description: 'Alias PUT untuk memperbarui gudang berdasarkan `id_gudang`.',
        tags: ['Warehouses'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'id_gudang', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/WarehouseUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Gudang berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function update(UpdateWarehouseRequest $request, string $id_gudang): JsonResponse
    {
        return ApiResponse::success(
            'Data gudang berhasil diperbarui.',
            WarehouseResource::make($this->warehouseService->update($id_gudang, $request->validated()))->resolve(),
        );
    }

    #[OA\Delete(
        path: '/api/v1/warehouses/{id_gudang}',
        operationId: 'warehouseDestroy',
        summary: 'Hapus master data gudang',
        description: 'Melakukan soft delete pada gudang berdasarkan `id_gudang` tanpa mengekspos id internal database.',
        tags: ['Warehouses'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'id_gudang', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Gudang berhasil dihapus', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function destroy(string $id_gudang): JsonResponse
    {
        $this->warehouseService->delete($id_gudang);

        return ApiResponse::success('Data gudang berhasil dihapus.');
    }
}
