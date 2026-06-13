<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListBridgeSourceApiRequest;
use App\Http\Requests\Api\V1\StoreBridgeSourceRecordRequest;
use App\Http\Requests\Api\V1\UpdateBridgeSourceRecordRequest;
use App\Http\Resources\Api\V1\BridgeSourceDetailResource;
use App\Http\Resources\Api\V1\BridgeSourceSummaryResource;
use App\Services\BridgeSource\BridgeSourceCrudService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class BridgeController extends Controller
{
    public function __construct(
        private readonly BridgeSourceCrudService $bridgeSourceCrudService,
    ) {}

    #[OA\Get(
        path: '/api/v1/bridges/metadata',
        operationId: 'bridgeSourceMetadata',
        summary: 'Metadata source modul Jembatan',
        description: 'Ringkasan profesional untuk modul source Jembatan berbasis tabel `m_jembatan` beserta relasi profil, bentang, struktur bawah, pelindung, asesmen, dan tabel lookup. Endpoint ini dipakai client untuk memahami struktur data, relasi, dan endpoint CRUD yang tersedia pada API v1.',
        tags: ['Bridge Source'],
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Metadata bridge source berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Metadata bridge source berhasil diambil.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'source_table', type: 'string', example: 'm_jembatan'),
                                new OA\Property(property: 'record_count', type: 'integer', example: 3076),
                                new OA\Property(property: 'data_mode', type: 'string', example: 'database'),
                                new OA\Property(property: 'relation_map', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
                                new OA\Property(property: 'source_tables', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
                                new OA\Property(property: 'endpoints', type: 'object', additionalProperties: true),
                            ]
                        ),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function metadata(): JsonResponse
    {
        return ApiResponse::success(
            'Metadata bridge source berhasil diambil.',
            [
                'source_table' => 'm_jembatan',
                'record_count' => $this->bridgeSourceCrudService->count(),
                'data_mode' => $this->bridgeSourceCrudService->isDatabaseSourceAvailable() ? 'database' : 'unavailable',
                'relation_map' => $this->bridgeSourceCrudService->relationMap(),
                'source_tables' => $this->bridgeSourceCrudService->tableCatalog(),
                'endpoints' => [
                    'list' => route('api.v1.bridges.index'),
                    'store' => route('api.v1.bridges.store'),
                    'show' => route('api.v1.bridges.show', ['bridgeUniqid' => '__uniqid__']),
                    'update' => route('api.v1.bridges.update', ['bridgeUniqid' => '__uniqid__']),
                    'delete' => route('api.v1.bridges.destroy', ['bridgeUniqid' => '__uniqid__']),
                ],
            ],
        );
    }

    #[OA\Get(
        path: '/api/v1/bridges',
        operationId: 'bridgeSourceIndex',
        summary: 'Daftar source data Jembatan',
        description: 'Mengembalikan daftar ringkas source data jembatan dari tabel `m_jembatan`, lengkap dengan route summary, wilayah kerja, jumlah relasi aktif, total panjang profil, dan asesmen total untuk konsumsi aplikasi utama.',
        tags: ['Bridge Source'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar source jembatan berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data bridge source berhasil diambil.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BridgeSourceSummaryResource')),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'request_id', type: 'string', nullable: true),
                                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta'),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(ListBridgeSourceApiRequest $request): JsonResponse
    {
        $records = $this->bridgeSourceCrudService->paginate($request->validated());

        return ApiResponse::paginated(
            'Data bridge source berhasil diambil.',
            BridgeSourceSummaryResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    #[OA\Post(
        path: '/api/v1/bridges',
        operationId: 'bridgeSourceStore',
        summary: 'Tambah source data jembatan',
        description: 'Membuat data induk source jembatan baru pada tabel `m_jembatan`, sekaligus dapat menyimpan profil, bentang, struktur bawah, pelindung, dan asesmen total dalam satu payload.',
        tags: ['Bridge Source'],
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BridgeSourceStoreRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Source jembatan berhasil dibuat',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data bridge source berhasil dibuat.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/BridgeSourceDetailResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function store(StoreBridgeSourceRecordRequest $request): JsonResponse
    {
        $actor = $request->user();

        return ApiResponse::success(
            'Data bridge source berhasil dibuat.',
            BridgeSourceDetailResource::make(
                $this->bridgeSourceCrudService->create($request->validated(), $actor)
            )->resolve(),
            status: 201,
        );
    }

    #[OA\Get(
        path: '/api/v1/bridges/{bridgeUniqid}',
        operationId: 'bridgeSourceShow',
        summary: 'Detail source data satu jembatan',
        description: 'Menampilkan detail penuh source data jembatan berdasarkan `uniqid`, termasuk identitas, kewilayahan, profil struktur, bentang, struktur bawah, pelindung, asesmen total, media, dan atribut source tambahan dari tabel induk.',
        tags: ['Bridge Source'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'bridgeUniqid', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '6498347da7db7')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail bridge source berhasil diambil',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data bridge source berhasil diambil.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/BridgeSourceDetailResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Uniqid bridge source tidak ditemukan'),
        ]
    )]
    public function show(string $bridgeUniqid): JsonResponse
    {
        $record = $this->bridgeSourceCrudService->find($bridgeUniqid);

        if ($record === null) {
            throw ValidationException::withMessages([
                'uniqid' => ['Data jembatan source tidak ditemukan.'],
            ]);
        }

        return ApiResponse::success(
            'Data bridge source berhasil diambil.',
            BridgeSourceDetailResource::make($record)->resolve(),
        );
    }

    #[OA\Patch(
        path: '/api/v1/bridges/{bridgeUniqid}',
        operationId: 'bridgeSourceUpdate',
        summary: 'Perbarui source data jembatan',
        description: 'Memperbarui source data jembatan berdasarkan `uniqid`. Payload mendukung update parsial untuk tabel induk maupun relasi utama seperti profil, bentang, struktur bawah, pelindung, dan asesmen.',
        tags: ['Bridge Source'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'bridgeUniqid', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '6498347da7db7')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BridgeSourceUpdateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Source jembatan berhasil diperbarui',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data bridge source berhasil diperbarui.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/BridgeSourceDetailResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
                    ]
                )
            ),
        ]
    )]
    public function update(UpdateBridgeSourceRecordRequest $request, string $bridgeUniqid): JsonResponse
    {
        $actor = $request->user();

        return ApiResponse::success(
            'Data bridge source berhasil diperbarui.',
            BridgeSourceDetailResource::make(
                $this->bridgeSourceCrudService->update($bridgeUniqid, $request->validated(), $actor)
            )->resolve(),
        );
    }

    #[OA\Delete(
        path: '/api/v1/bridges/{bridgeUniqid}',
        operationId: 'bridgeSourceDestroy',
        summary: 'Hapus source data jembatan',
        description: 'Melakukan penghapusan logis pada source data jembatan dengan menandai `deleted_at`, `active`, `status`, dan `statusdata` pada tabel induk.',
        tags: ['Bridge Source'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'bridgeUniqid', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '6498347da7db7')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Source jembatan berhasil dihapus'),
        ]
    )]
    public function destroy(string $bridgeUniqid): JsonResponse
    {
        $actor = request()->user();
        $this->bridgeSourceCrudService->delete($bridgeUniqid, $actor);

        return ApiResponse::success('Data bridge source berhasil dihapus.');
    }
}
