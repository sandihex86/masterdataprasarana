<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceTableSchemaResource',
    properties: [
        new OA\Property(property: 'table', type: 'string', example: 'm_kabkot'),
        new OA\Property(property: 'label', type: 'string', example: 'Kabupaten/Kota'),
        new OA\Property(property: 'description', type: 'string', example: 'Lookup kabupaten/kota source bridge.'),
        new OA\Property(property: 'row_count', type: 'integer', example: 161),
        new OA\Property(property: 'primary_key', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'unique_keys', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'required_columns', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'columns', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'indexes', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
    ],
    type: 'object'
)]
final class BridgeSourceTableSchemaResourceSchema
{
}
