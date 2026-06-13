<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceUpdateRequest',
    properties: [
        new OA\Property(property: 'tanggal', type: 'string', format: 'date', nullable: true, example: '2023-06-25'),
        new OA\Property(property: 'wil_ker', type: 'string', nullable: true, example: '62e60276e589f'),
        new OA\Property(property: 'id_prov', type: 'string', nullable: true, example: '31'),
        new OA\Property(property: 'id_kabkot', type: 'string', nullable: true, example: '3171'),
        new OA\Property(property: 'wil_op', type: 'string', nullable: true, example: '1'),
        new OA\Property(property: 'lat', type: 'string', nullable: true, example: '-6.352918'),
        new OA\Property(property: 'lon', type: 'string', nullable: true, example: '106.261155'),
        new OA\Property(property: 'nama', type: 'string', nullable: true, example: 'Jembatan Citeras'),
        new OA\Property(property: 'lintas', type: 'string', nullable: true, example: 'LN-01'),
        new OA\Property(property: 'stasiun1', type: 'string', nullable: true, example: '62dd01682ed32'),
        new OA\Property(property: 'stasiun2', type: 'string', nullable: true, example: '62dd0168399bb'),
        new OA\Property(property: 'no_bh', type: 'string', nullable: true, example: '334'),
        new OA\Property(property: 'arah_bh', type: 'string', nullable: true, example: 'Hilir'),
        new OA\Property(property: 'jenis', type: 'string', nullable: true, example: '2'),
        new OA\Property(property: 'km_hm', type: 'string', nullable: true, example: '78+613'),
        new OA\Property(property: 'catatan', type: 'string', nullable: true),
        new OA\Property(property: 'active', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'status', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'statusdata', type: 'integer', nullable: true, example: 0),
        new OA\Property(property: 'profile', type: 'object', additionalProperties: true),
        new OA\Property(property: 'spans', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'substructures', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'protection', type: 'object', additionalProperties: true),
        new OA\Property(property: 'assessment', type: 'object', additionalProperties: true),
    ],
    type: 'object'
)]
final class BridgeSourceUpdateRequestSchema
{
}
