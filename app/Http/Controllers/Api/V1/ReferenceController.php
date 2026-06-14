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
        path: '/api/v1/references/entities',
        operationId: 'clusterReferenceEntityCatalog',
        summary: 'Katalog entitas Cluster References',
        description: 'Mengambil daftar entitas API yang tersedia pada Cluster References beserta alias, represented_table, strategi ID, jumlah row, dan endpoint operasional. Gunakan katalog ini sebagai discovery contract sebelum memanggil metadata, batch, search, atau by-id.',
        tags: ['Cluster References'],
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Katalog entitas referensi berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Katalog entitas referensi berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReferenceEntityCatalogResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function entities(): JsonResponse
    {
        return ApiResponse::success(
            'Katalog entitas referensi berhasil diambil.',
            $this->referenceSourceTableService->entityCatalog(),
        );
    }

    #[OA\Get(
        path: '/api/v1/references/{lookup}/metadata',
        operationId: 'referenceLookupMetadata',
        summary: 'Metadata entitas Cluster References',
        description: 'Mengambil metadata satu entitas referensi. Entitas utama: prasarana, lintas, stasiun, wilker, wilops, provinsi, kabupaten-kota, kecamatan, dan kelurahan. Response menjelaskan entity, represented_table, id_column untuk endpoint by-id, code_column untuk endpoint by-code, strategi ID, fields beserta format API, kolom wajib, dan endpoint operasional entitas.',
        tags: ['Cluster References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lookup', in: 'path', required: true, description: 'Nama entitas referensi atau alias kompatibilitas.', schema: new OA\Schema(type: 'string', enum: ['prasarana', 'lintas', 'stasiun', 'wilker', 'wilops', 'provinsi', 'kabupaten-kota', 'kecamatan', 'kelurahan'], example: 'provinsi')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Metadata lookup berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Metadata lookup referensi berhasil diambil.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/ReferenceLookupMetadataResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Lookup tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function lookupMetadata(string $lookup): JsonResponse
    {
        return ApiResponse::success(
            'Metadata lookup referensi berhasil diambil.',
            $this->referenceSourceTableService->entityMetadata($lookup),
        );
    }

    #[OA\Get(
        path: '/api/v1/references/{lookup}/batch',
        operationId: 'referenceLookupBatch',
        summary: 'Batch entitas Cluster References',
        description: 'Mengambil seluruh row aktif dari satu entitas Referensi untuk sinkronisasi dropdown/cache aplikasi lain. Endpoint ini tidak dipaginasi. Gunakan parameter `active` untuk entitas yang memiliki kolom active, `updated_since` untuk sinkronisasi inkremental, dan `q`/`keyword` untuk entitas virtual seperti wilops.',
        tags: ['Cluster References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lookup', in: 'path', required: true, description: 'Nama entitas referensi atau alias kompatibilitas.', schema: new OA\Schema(type: 'string', enum: ['prasarana', 'lintas', 'stasiun', 'wilker', 'wilops', 'provinsi', 'kabupaten-kota', 'kecamatan', 'kelurahan'], example: 'stasiun')),
            new OA\Parameter(name: 'active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'updated_since', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time', example: '2026-06-01 00:00:00')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Batch lookup berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Batch lookup referensi berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReferenceTableRowResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ReferenceBatchMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Lookup tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function lookupBatch(Request $request, string $lookup): JsonResponse
    {
        $validated = $request->validate([
            'active' => ['nullable', 'boolean'],
            'updated_since' => ['nullable', 'date'],
            'q' => ['nullable', 'string'],
            'keyword' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
        ]);
        $result = $this->referenceSourceTableService->entityBatch($lookup, $validated);

        return ApiResponse::success(
            'Batch lookup referensi berhasil diambil.',
            $result['data'],
            $result['meta'],
        );
    }

    #[OA\Get(
        path: '/api/v1/references/{lookup}/search',
        operationId: 'referenceLookupSearch',
        summary: 'Cari data entitas referensi',
        description: 'Mencari row referensi pada entitas lookup. Semua entitas Referensi mendukung endpoint ini: prasarana, lintas, stasiun, wilker, wilops, provinsi, kabupaten-kota, kecamatan, kelurahan, serta alias kompatibilitas seperti routes, stations, operation-areas, work-areas, provinces, cities, dan kabkot.',
        tags: ['Cluster References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lookup', in: 'path', required: true, description: 'Nama entitas referensi.', schema: new OA\Schema(type: 'string', enum: ['prasarana', 'lintas', 'stasiun', 'wilker', 'wilops', 'provinsi', 'kabupaten-kota', 'kecamatan', 'kelurahan'], example: 'stasiun')),
            new OA\Parameter(name: 'q', in: 'query', required: false, description: 'Kata kunci pencarian. Alias query `keyword` dan `search` juga diterima.', schema: new OA\Schema(type: 'string', example: 'Jakarta')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 25)),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hasil pencarian referensi berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Hasil pencarian referensi berhasil diambil.'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReferenceTableRowResource')),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Entitas tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function lookupSearch(Request $request, string $lookup): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string'],
            'keyword' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $records = $this->referenceSourceTableService->entitySearch($lookup, $validated);

        return ApiResponse::paginated(
            'Hasil pencarian referensi berhasil diambil.',
            $records->items(),
            $records,
            [
                'reference' => [
                    'entity' => $lookup,
                ],
            ],
        );
    }

    #[OA\Get(
        path: '/api/v1/references/{lookup}/{id}',
        operationId: 'referenceLookupById',
        summary: 'Ambil data entitas referensi berdasarkan ID',
        description: 'Mengambil satu row referensi berdasarkan ID publik entitas. Untuk prasarana, lintas, stasiun, dan wilker, ID publik memakai ULID. Untuk provinsi/kabupaten-kota/kecamatan/kelurahan, ID memakai kode wilayah dari CSV. Untuk wilops, ID adalah nilai wilayah operasi dan harus di-URL-encode.',
        tags: ['Cluster References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lookup', in: 'path', required: true, description: 'Nama entitas referensi.', schema: new OA\Schema(type: 'string', enum: ['prasarana', 'lintas', 'stasiun', 'wilker', 'wilops', 'provinsi', 'kabupaten-kota', 'kecamatan', 'kelurahan'], example: 'prasarana')),
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID publik sesuai metadata `id_column`.', schema: new OA\Schema(type: 'string', example: '01JREFEREN98N7269AAH1FXG19')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data referensi berdasarkan ID berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data lookup referensi berhasil diambil.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/ReferenceTableRowResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Entitas/ID tidak valid atau data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function lookupById(string $lookup, string $id): JsonResponse
    {
        return ApiResponse::success(
            'Data lookup referensi berhasil diambil.',
            $this->referenceSourceTableService->entityFind($lookup, $id),
        );
    }

    #[OA\Get(
        path: '/api/v1/references/{lookup}/kode/{kode}',
        operationId: 'referenceLookupByCode',
        summary: 'Ambil entitas referensi berdasarkan kode',
        description: 'Mengambil satu row lookup berdasarkan kode utama tabel. Kolom kode per lookup: prasarana=kode_prasarana, lintas=kode_lintas, wilker=kode_prasarana, sedangkan stasiun/provinsi/kabupaten-kota/kecamatan/kelurahan menggunakan id. Untuk kontrak baru, endpoint by-id tersedia di /api/v1/references/{lookup}/{id}.',
        tags: ['Cluster References'],
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lookup', in: 'path', required: true, description: 'Nama entitas referensi atau alias kompatibilitas.', schema: new OA\Schema(type: 'string', enum: ['prasarana', 'lintas', 'stasiun', 'wilker', 'wilops', 'provinsi', 'kabupaten-kota', 'kecamatan', 'kelurahan'], example: 'provinsi')),
            new OA\Parameter(name: 'kode', in: 'path', required: true, description: 'Kode lookup sesuai metadata `code_column`.', schema: new OA\Schema(type: 'string', example: '11')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lookup berdasarkan kode berhasil diambil', content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Data lookup referensi berhasil diambil.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/ReferenceTableRowResource'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
            ])),
            new OA\Response(response: 401, description: 'Token tidak valid atau tidak tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Lookup/kode tidak valid atau data tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function lookupByCode(string $lookup, string $kode): JsonResponse
    {
        if ($this->referenceSourceTableService->entityIsVirtual($lookup)) {
            return ApiResponse::success(
                'Data lookup referensi berhasil diambil.',
                $this->referenceSourceTableService->entityFind($lookup, $kode),
            );
        }

        $table = $this->referenceSourceTableService->tableForAlias($lookup);

        return ApiResponse::success(
            'Data lookup referensi berhasil diambil.',
            $this->referenceSourceTableService->findByCode($table, $kode),
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
