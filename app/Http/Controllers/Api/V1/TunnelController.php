<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListTunnelRequest;
use App\Http\Requests\Api\V1\StoreTunnelRequest;
use App\Http\Requests\Api\V1\UpdateTunnelRequest;
use App\Http\Requests\Api\V1\UpsertTunnelDocRequest;
use App\Http\Requests\Api\V1\UpsertTunnelSpecRequest;
use App\Http\Requests\Api\V1\UpsertTunnelStructureRequest;
use App\Http\Resources\Api\V1\TunnelDetailResource;
use App\Http\Resources\Api\V1\TunnelDocResource;
use App\Http\Resources\Api\V1\TunnelResource;
use App\Http\Resources\Api\V1\TunnelSpecResource;
use App\Http\Resources\Api\V1\TunnelStructureResource;
use App\Services\TunnelDocumentUploadService;
use App\Services\TunnelService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class TunnelController extends Controller
{
    public function __construct(
        private readonly TunnelService $tunnelService,
        private readonly TunnelDocumentUploadService $tunnelDocumentUploadService,
    ) {}

    #[OA\Get(
        path: '/api/tunnels',
        operationId: 'tunnelIndex',
        summary: 'Daftar master data terowongan',
        description: 'Mengambil daftar master data Terowongan Kereta dengan pagination, pencarian, filter wilayah/lintas/status/kondisi, dan sorting terbatas pada kolom aman.',
        tags: ['Tunnels'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Sasaksaat')),
            new OA\Parameter(name: 'id_wilayah_kerja', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'FK')),
            new OA\Parameter(name: 'id_lintas', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'FK')),
            new OA\Parameter(name: 'status_operasi', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Operasi')),
            new OA\Parameter(name: 'status_aset', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Aktif')),
            new OA\Parameter(name: 'kondisi_terakhir', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Baik')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 25)),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'updated_at')),
            new OA\Parameter(name: 'sort_dir', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], example: 'desc')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data tunnel berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data tunnel berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TunnelResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function index(ListTunnelRequest $request): JsonResponse
    {
        $records = $this->tunnelService->paginate($request->validated());

        return ApiResponse::paginated(
            'Data tunnel berhasil diambil.',
            TunnelResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    #[OA\Post(
        path: '/api/tunnels',
        operationId: 'tunnelStore',
        summary: 'Tambah master data terowongan',
        description: 'Membuat tunnel baru. `tunnel_id` digenerate otomatis sebagai ULID dan payload dapat menyertakan nested `structure`, `specs`, dan `docs`.',
        tags: ['Tunnels'],
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelStoreRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Tunnel berhasil dibuat', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data tunnel berhasil dibuat.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/TunnelDetailResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function store(StoreTunnelRequest $request): JsonResponse
    {
        $payload = $this->tunnelDocumentUploadService->mergeUploadedFiles($request, $request->validated());

        return ApiResponse::success(
            'Data tunnel berhasil dibuat.',
            TunnelDetailResource::make($this->tunnelService->create($payload))->resolve(),
            status: 201,
        );
    }

    #[OA\Get(
        path: '/api/tunnels/{tunnel_id}',
        operationId: 'tunnelShow',
        summary: 'Detail master data terowongan',
        description: 'Mengambil detail satu tunnel berdasarkan public identifier `tunnel_id`, lengkap dengan struktur, spesifikasi, dan dokumen.',
        tags: ['Tunnels'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '01JY0000000000000000000000'))],
        responses: [
            new OA\Response(response: 200, description: 'Detail tunnel berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data tunnel berhasil diambil.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/TunnelDetailResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function show(string $tunnel_id): JsonResponse
    {
        return ApiResponse::success(
            'Data tunnel berhasil diambil.',
            TunnelDetailResource::make($this->tunnelService->find($tunnel_id))->resolve(),
        );
    }

    #[OA\Patch(
        path: '/api/tunnels/{tunnel_id}',
        operationId: 'tunnelUpdate',
        summary: 'Perbarui master data terowongan',
        description: 'Memperbarui tunnel berdasarkan `tunnel_id`. Payload mendukung update parsial dan nested detail jika `structure`, `specs`, atau `docs` disertakan.',
        tags: ['Tunnels'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Tunnel berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    #[OA\Put(
        path: '/api/tunnels/{tunnel_id}',
        operationId: 'tunnelReplace',
        summary: 'Perbarui master data terowongan',
        description: 'Memperbarui tunnel berdasarkan `tunnel_id`. Method PUT disediakan sebagai alias update penuh/parsial sesuai pola route existing.',
        tags: ['Tunnels'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelUpdateRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Tunnel berhasil diperbarui', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function update(UpdateTunnelRequest $request, string $tunnel_id): JsonResponse
    {
        $payload = $this->tunnelDocumentUploadService->mergeUploadedFiles($request, $request->validated());

        return ApiResponse::success(
            'Data tunnel berhasil diperbarui.',
            TunnelDetailResource::make($this->tunnelService->update($tunnel_id, $payload))->resolve(),
        );
    }

    #[OA\Delete(
        path: '/api/tunnels/{tunnel_id}',
        operationId: 'tunnelDestroy',
        summary: 'Hapus master data terowongan',
        description: 'Melakukan soft delete pada tunnel berdasarkan `tunnel_id` tanpa mengekspos id internal database.',
        tags: ['Tunnels'],
        security: [['sanctumBearer' => []]],
        parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Tunnel berhasil dihapus', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function destroy(string $tunnel_id): JsonResponse
    {
        $this->tunnelService->delete($tunnel_id);

        return ApiResponse::success('Data tunnel berhasil dihapus.');
    }

    #[OA\Get(path: '/api/tunnels/{tunnel_id}/structure', operationId: 'tunnelStructureShow', summary: 'Detail struktur tunnel', description: 'Mengambil detail struktur tunnel berdasarkan `tunnel_id`.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Struktur tunnel berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function structure(string $tunnel_id): JsonResponse
    {
        return ApiResponse::success('Struktur tunnel berhasil diambil.', TunnelStructureResource::make($this->tunnelService->structure($tunnel_id))->resolve());
    }

    #[OA\Patch(path: '/api/tunnels/{tunnel_id}/structure', operationId: 'tunnelStructureUpsert', summary: 'Simpan struktur tunnel', description: 'Membuat atau memperbarui detail struktur tunnel berdasarkan `tunnel_id`.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelStructure')), responses: [new OA\Response(response: 200, description: 'Struktur tunnel berhasil disimpan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    #[OA\Put(path: '/api/tunnels/{tunnel_id}/structure', operationId: 'tunnelStructureReplace', summary: 'Simpan struktur tunnel', description: 'Alias PUT untuk membuat atau memperbarui detail struktur tunnel.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelStructure')), responses: [new OA\Response(response: 200, description: 'Struktur tunnel berhasil disimpan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function upsertStructure(UpsertTunnelStructureRequest $request, string $tunnel_id): JsonResponse
    {
        return ApiResponse::success('Struktur tunnel berhasil disimpan.', TunnelStructureResource::make($this->tunnelService->upsertStructure($tunnel_id, $request->validated()))->resolve());
    }

    #[OA\Get(path: '/api/tunnels/{tunnel_id}/specs', operationId: 'tunnelSpecsShow', summary: 'Detail spesifikasi tunnel', description: 'Mengambil spesifikasi teknis tunnel berdasarkan `tunnel_id`.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Spesifikasi tunnel berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function specs(string $tunnel_id): JsonResponse
    {
        return ApiResponse::success('Spesifikasi tunnel berhasil diambil.', TunnelSpecResource::make($this->tunnelService->specs($tunnel_id))->resolve());
    }

    #[OA\Patch(path: '/api/tunnels/{tunnel_id}/specs', operationId: 'tunnelSpecsUpsert', summary: 'Simpan spesifikasi tunnel', description: 'Membuat atau memperbarui spesifikasi teknis tunnel berdasarkan `tunnel_id`.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelSpec')), responses: [new OA\Response(response: 200, description: 'Spesifikasi tunnel berhasil disimpan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    #[OA\Put(path: '/api/tunnels/{tunnel_id}/specs', operationId: 'tunnelSpecsReplace', summary: 'Simpan spesifikasi tunnel', description: 'Alias PUT untuk membuat atau memperbarui spesifikasi teknis tunnel.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelSpec')), responses: [new OA\Response(response: 200, description: 'Spesifikasi tunnel berhasil disimpan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function upsertSpecs(UpsertTunnelSpecRequest $request, string $tunnel_id): JsonResponse
    {
        return ApiResponse::success('Spesifikasi tunnel berhasil disimpan.', TunnelSpecResource::make($this->tunnelService->upsertSpecs($tunnel_id, $request->validated()))->resolve());
    }

    #[OA\Get(path: '/api/tunnels/{tunnel_id}/docs', operationId: 'tunnelDocsShow', summary: 'Detail dokumen tunnel', description: 'Mengambil dokumen teknis tunnel berdasarkan `tunnel_id`.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Dokumen tunnel berhasil diambil', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function docs(string $tunnel_id): JsonResponse
    {
        return ApiResponse::success('Dokumen tunnel berhasil diambil.', TunnelDocResource::make($this->tunnelService->docs($tunnel_id))->resolve());
    }

    #[OA\Patch(path: '/api/tunnels/{tunnel_id}/docs', operationId: 'tunnelDocsUpsert', summary: 'Simpan dokumen tunnel', description: 'Membuat atau memperbarui dokumen teknis tunnel berdasarkan `tunnel_id`. Nomor dokumen nullable dan tidak unik.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelDoc')), responses: [new OA\Response(response: 200, description: 'Dokumen tunnel berhasil disimpan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    #[OA\Put(path: '/api/tunnels/{tunnel_id}/docs', operationId: 'tunnelDocsReplace', summary: 'Simpan dokumen tunnel', description: 'Alias PUT untuk membuat atau memperbarui dokumen teknis tunnel.', tags: ['Tunnels'], security: [['sanctumBearer' => []]], parameters: [new OA\Parameter(name: 'tunnel_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TunnelDoc')), responses: [new OA\Response(response: 200, description: 'Dokumen tunnel berhasil disimpan', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')), new OA\Response(response: 400, description: 'Input tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 404, description: 'Data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')), new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse'))])]
    public function upsertDocs(UpsertTunnelDocRequest $request, string $tunnel_id): JsonResponse
    {
        $payload = $this->tunnelDocumentUploadService->mergeUploadedFiles($request, $request->validated(), nestedDocs: false);

        return ApiResponse::success('Dokumen tunnel berhasil disimpan.', TunnelDocResource::make($this->tunnelService->upsertDocs($tunnel_id, $payload))->resolve());
    }
}
