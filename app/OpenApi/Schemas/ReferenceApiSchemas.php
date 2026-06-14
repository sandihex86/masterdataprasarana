<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ReferenceTableCatalogResource', type: 'object', properties: [
    new OA\Property(property: 'table', type: 'string', example: 'm_stasiun'),
    new OA\Property(property: 'label', type: 'string', example: 'Data Stasiun'),
    new OA\Property(property: 'description', type: 'string', example: 'Referensi stasiun dan koordinat dari database/data/m_stasiun.csv.'),
    new OA\Property(property: 'kind', type: 'string', example: 'lookup'),
    new OA\Property(property: 'href', type: 'string', example: 'https://prasarana.labdata.id/dashboard/master-data/referensi/tables/m_stasiun'),
    new OA\Property(property: 'row_count', type: 'integer', example: 729),
])]
#[OA\Schema(schema: 'ReferenceTableRowResource', type: 'object', properties: [
    new OA\Property(property: 'table', type: 'string', example: 'm_stasiun'),
    new OA\Property(property: 'row_key', type: 'string', example: '1'),
    new OA\Property(property: 'data', type: 'object', additionalProperties: true, example: [
        'internal_id' => 1,
        'id' => 'STA-000001',
        'nama_stasiun' => 'Stasiun Jakarta Kota (JAKK)',
        'wilayah_operasi' => 'DAOP 1 JAKARTA',
        'lat' => -6.188,
        'long' => 106.815,
    ]),
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
