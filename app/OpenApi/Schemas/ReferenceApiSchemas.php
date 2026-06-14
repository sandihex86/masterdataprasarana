<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ReferenceTableCatalogResource', type: 'object', properties: [
    new OA\Property(property: 'table', type: 'string', example: 'm_stasiun'),
    new OA\Property(property: 'entity', type: 'string', example: 'stasiun'),
    new OA\Property(property: 'represented_table', type: 'string', example: 'm_stasiun'),
    new OA\Property(property: 'alias', type: 'string', example: 'stasiun'),
    new OA\Property(property: 'label', type: 'string', example: 'Data Stasiun'),
    new OA\Property(property: 'description', type: 'string', example: 'Referensi stasiun dan koordinat dari database/data/m_stasiun.csv.'),
    new OA\Property(property: 'kind', type: 'string', example: 'lookup'),
    new OA\Property(property: 'code_column', type: 'string', example: 'id'),
    new OA\Property(property: 'href', type: 'string', example: 'https://prasarana.labdata.id/dashboard/master-data/referensi/tables/m_stasiun'),
    new OA\Property(property: 'row_count', type: 'integer', example: 729),
])]
#[OA\Schema(schema: 'ReferenceEntityCatalogResource', type: 'object', properties: [
    new OA\Property(property: 'entity', type: 'string', example: 'wilops'),
    new OA\Property(property: 'aliases', type: 'array', items: new OA\Items(type: 'string'), example: ['wilops', 'wilayah-operasi', 'operation-areas']),
    new OA\Property(property: 'label', type: 'string', example: 'Data Wilayah Operasi'),
    new OA\Property(property: 'description', type: 'string', example: 'Referensi wilayah operasi yang diturunkan dari kolom wilayah_operasi pada m_stasiun.'),
    new OA\Property(property: 'kind', type: 'string', example: 'lookup'),
    new OA\Property(property: 'table', type: 'string', example: 'm_stasiun'),
    new OA\Property(property: 'represented_table', type: 'string', example: 'm_stasiun'),
    new OA\Property(property: 'source_column', type: 'string', nullable: true, example: 'wilayah_operasi'),
    new OA\Property(property: 'id_column', type: 'string', example: 'id'),
    new OA\Property(property: 'code_column', type: 'string', example: 'id'),
    new OA\Property(property: 'id_strategy', type: 'string', enum: ['ulid', 'source_code', 'source_value'], example: 'source_value'),
    new OA\Property(property: 'row_count', type: 'integer', example: 10),
    new OA\Property(property: 'endpoints', type: 'object', additionalProperties: true, example: [
        'metadata' => 'https://prasarana.labdata.id/api/v1/references/wilops/metadata',
        'batch' => 'https://prasarana.labdata.id/api/v1/references/wilops/batch',
        'search' => 'https://prasarana.labdata.id/api/v1/references/wilops/search?q={keyword}',
        'by_id' => 'https://prasarana.labdata.id/api/v1/references/wilops/{id}',
        'by_code' => 'https://prasarana.labdata.id/api/v1/references/wilops/kode/{kode}',
    ]),
])]
#[OA\Schema(schema: 'ReferenceTableRowResource', type: 'object', properties: [
    new OA\Property(property: 'table', type: 'string', example: 'm_stasiun'),
    new OA\Property(property: 'entity', type: 'string', nullable: true, example: 'stasiun'),
    new OA\Property(property: 'row_key', type: 'string', example: '1'),
    new OA\Property(property: 'data', type: 'object', additionalProperties: true, example: [
        'internal_id' => 1,
        'id' => '01JREFEREN0R6TR5H26X9D9QJ1',
        'nama_stasiun' => 'Stasiun Jakarta Kota (JAKK)',
        'wilayah_operasi' => 'DAOP 1 JAKARTA',
        'lat' => -6.188,
        'long' => 106.815,
    ]),
])]
#[OA\Schema(schema: 'ReferenceLookupMetadataResource', type: 'object', properties: [
    new OA\Property(property: 'table', type: 'string', example: 'provinsi'),
    new OA\Property(property: 'entity', type: 'string', example: 'provinsi'),
    new OA\Property(property: 'represented_table', type: 'string', example: 'provinsi'),
    new OA\Property(property: 'source_column', type: 'string', nullable: true, description: 'Kolom sumber untuk entitas virtual seperti wilops.', example: 'wilayah_operasi'),
    new OA\Property(property: 'alias', type: 'string', example: 'provinsi'),
    new OA\Property(property: 'label', type: 'string', example: 'Data Provinsi'),
    new OA\Property(property: 'description', type: 'string', example: 'Referensi provinsi dari database/data/provinsi.csv.'),
    new OA\Property(property: 'kind', type: 'string', example: 'lookup'),
    new OA\Property(property: 'code_column', type: 'string', description: 'Kolom yang dipakai oleh endpoint /kode/{kode}.', example: 'id'),
    new OA\Property(property: 'id_column', type: 'string', description: 'Field yang dipakai endpoint /{id}.', example: 'id'),
    new OA\Property(property: 'id_strategy', type: 'string', enum: ['ulid', 'source_code', 'source_value'], example: 'source_code'),
    new OA\Property(property: 'database_name', type: 'string', example: 'prasarana_referensi'),
    new OA\Property(property: 'row_count', type: 'integer', example: 38),
    new OA\Property(property: 'columns', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
    new OA\Property(property: 'fields', type: 'array', description: 'Kontrak field API. Setiap item menjelaskan type, format, nullable, required, contoh nilai, dan deskripsi pemakaian.', items: new OA\Items(ref: '#/components/schemas/ReferenceFieldMetadata')),
    new OA\Property(property: 'visible_columns', type: 'array', items: new OA\Items(type: 'string'), example: ['id', 'name']),
    new OA\Property(property: 'required_columns', type: 'array', items: new OA\Items(type: 'string'), example: ['id', 'name']),
    new OA\Property(property: 'endpoints', type: 'object', additionalProperties: true, example: [
        'metadata' => 'https://prasarana.labdata.id/api/v1/references/provinsi/metadata',
        'batch' => 'https://prasarana.labdata.id/api/v1/references/provinsi/batch',
        'search' => 'https://prasarana.labdata.id/api/v1/references/provinsi/search?q={keyword}',
        'by_id' => 'https://prasarana.labdata.id/api/v1/references/provinsi/{id}',
        'by_code' => 'https://prasarana.labdata.id/api/v1/references/provinsi/kode/{kode}',
    ]),
])]
#[OA\Schema(schema: 'ReferenceFieldMetadata', type: 'object', properties: [
    new OA\Property(property: 'name', type: 'string', example: 'id'),
    new OA\Property(property: 'label', type: 'string', example: 'Id'),
    new OA\Property(property: 'type', type: 'string', enum: ['string', 'number', 'integer', 'boolean'], example: 'string'),
    new OA\Property(property: 'format', type: 'string', enum: ['ulid', 'source_code', 'source_value', 'code', 'text', 'decimal', 'integer', 'boolean', 'date', 'date-time', 'latitude', 'longitude'], example: 'source_code'),
    new OA\Property(property: 'database_type', type: 'string', nullable: true, example: 'varchar(32)'),
    new OA\Property(property: 'nullable', type: 'boolean', example: false),
    new OA\Property(property: 'required', type: 'boolean', example: true),
    new OA\Property(property: 'example', oneOf: [
        new OA\Schema(type: 'string'),
        new OA\Schema(type: 'number'),
        new OA\Schema(type: 'integer'),
        new OA\Schema(type: 'boolean'),
    ], example: '11'),
    new OA\Property(property: 'description', type: 'string', example: 'Kode sumber dari CSV yang dipakai sebagai ID publik.'),
])]
#[OA\Schema(schema: 'ReferenceBatchMeta', type: 'object', properties: [
    new OA\Property(property: 'reference', type: 'object', properties: [
        new OA\Property(property: 'table', type: 'string', example: 'provinsi'),
        new OA\Property(property: 'entity', type: 'string', example: 'provinsi'),
        new OA\Property(property: 'represented_table', type: 'string', example: 'provinsi'),
        new OA\Property(property: 'alias', type: 'string', example: 'provinsi'),
        new OA\Property(property: 'code_column', type: 'string', example: 'id'),
    ]),
    new OA\Property(property: 'total', type: 'integer', example: 38),
    new OA\Property(property: 'generated_at', type: 'string', format: 'date-time', example: '2026-06-14T12:00:00.000000Z'),
])]
#[OA\Schema(schema: 'ReferenceTableRecordRequest', required: ['data'], type: 'object', properties: [
    new OA\Property(property: 'data', type: 'object', additionalProperties: true, example: [
        'id' => '99',
        'name' => 'Contoh Referensi',
    ]),
])]
final class ReferenceApiSchemas
{
}
