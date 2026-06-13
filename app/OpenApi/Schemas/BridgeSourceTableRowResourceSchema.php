<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceTableRowResource',
    properties: [
        new OA\Property(property: 'table', type: 'string', example: 'm_wilayah_kerja'),
        new OA\Property(property: 'row_key', type: 'string', example: '62e60276e589f'),
        new OA\Property(property: 'data', type: 'object', additionalProperties: true),
    ],
    type: 'object'
)]
final class BridgeSourceTableRowResourceSchema
{
}
