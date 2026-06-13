<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ApiSuccessResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Data berhasil diambil.'),
        new OA\Property(property: 'data', nullable: true, oneOf: [
            new OA\Schema(ref: '#/components/schemas/BridgeDetail'),
            new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/BridgeListItem')),
            new OA\Schema(type: 'object', additionalProperties: true),
        ]),
        new OA\Property(property: 'meta', type: 'object', additionalProperties: true),
    ]
)]
#[OA\Schema(
    schema: 'ApiErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Data tidak ditemukan.'),
        new OA\Property(property: 'errors', type: 'object', additionalProperties: true),
    ]
)]
#[OA\Schema(
    schema: 'BridgeListItem',
    type: 'object',
    properties: [
        new OA\Property(property: 'kode_jembatan', type: 'string', example: '6498347da7db7'),
        new OA\Property(property: 'nama', type: 'string', example: 'Jembatan Citeras'),
        new OA\Property(property: 'no_bh', type: 'string', nullable: true, example: '334'),
        new OA\Property(property: 'jenis', type: 'string', nullable: true, example: '2'),
        new OA\Property(property: 'km_hm', type: 'string', nullable: true, example: '78+613'),
        new OA\Property(property: 'lintas', type: 'string', nullable: true, example: 'Citeras - Rangkasbitung'),
        new OA\Property(property: 'stasiun1', type: 'string', nullable: true, example: 'Citeras'),
        new OA\Property(property: 'stasiun2', type: 'string', nullable: true, example: 'Rangkasbitung'),
        new OA\Property(property: 'wilayah_operasi', type: 'string', nullable: true, example: 'Daop 1 Jakarta'),
        new OA\Property(property: 'wilayah_kerja', type: 'string', nullable: true, example: 'BTP Kelas I Jakarta'),
        new OA\Property(property: 'id_prov', type: 'string', nullable: true, example: '31'),
        new OA\Property(property: 'id_kabkot', type: 'string', nullable: true, example: '3171'),
        new OA\Property(property: 'lat', type: 'number', format: 'float', nullable: true, example: -6.2),
        new OA\Property(property: 'lon', type: 'number', format: 'float', nullable: true, example: 106.8),
        new OA\Property(property: 'active', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'status', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'statusdata', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'BridgeDetail',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/BridgeListItem'),
        new OA\Schema(
            type: 'object',
            properties: [
                new OA\Property(property: 'profil', ref: '#/components/schemas/BridgeProfile', nullable: true),
                new OA\Property(property: 'nilai_kondisi_terakhir', ref: '#/components/schemas/BridgeCondition', nullable: true),
                new OA\Property(property: 'perawatan_terakhir', ref: '#/components/schemas/BridgeMaintenance', nullable: true),
                new OA\Property(property: 'survey_terakhir', ref: '#/components/schemas/BridgeSurvey', nullable: true),
            ]
        ),
    ]
)]
#[OA\Schema(schema: 'BridgeProfile', type: 'object', additionalProperties: true, properties: [
    new OA\Property(property: 'perpotongan', type: 'string', nullable: true, example: 'Sungai'),
    new OA\Property(property: 'jml_lintasan', type: 'integer', nullable: true, example: 1),
    new OA\Property(property: 'jml_bentang', type: 'integer', nullable: true, example: 1),
    new OA\Property(property: 'pjg_total', type: 'string', nullable: true, example: '20'),
    new OA\Property(property: 'thn_selesai', type: 'string', nullable: true, example: '2020'),
])]
#[OA\Schema(schema: 'BridgeSpan', type: 'object', additionalProperties: true)]
#[OA\Schema(schema: 'BridgeCondition', type: 'object', additionalProperties: true)]
#[OA\Schema(schema: 'BridgeMaintenance', type: 'object', additionalProperties: true)]
#[OA\Schema(schema: 'BridgeSurvey', type: 'object', additionalProperties: true)]
#[OA\Schema(schema: 'BridgeGeoJson', type: 'object', properties: [
    new OA\Property(property: 'type', type: 'string', example: 'FeatureCollection'),
    new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
])]
final class BridgeApiSchemas
{
}
