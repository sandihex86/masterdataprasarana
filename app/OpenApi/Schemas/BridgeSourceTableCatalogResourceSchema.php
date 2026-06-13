<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceTableCatalogResource',
    properties: [
        new OA\Property(property: 'table', type: 'string', example: 'm_kabkot'),
        new OA\Property(property: 'label', type: 'string', example: 'Kabupaten/Kota'),
        new OA\Property(property: 'description', type: 'string', example: 'Lookup kabupaten/kota source bridge.'),
        new OA\Property(property: 'row_count', type: 'integer', example: 161),
        new OA\Property(property: 'endpoints', type: 'object', additionalProperties: true),
    ],
    type: 'object'
)]
final class BridgeSourceTableCatalogResourceSchema
{
}
