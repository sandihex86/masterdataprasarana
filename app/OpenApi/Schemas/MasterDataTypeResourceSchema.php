<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MasterDataTypeResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'code', type: 'string', example: 'station'),
        new OA\Property(property: 'name', type: 'string', example: 'Stasiun'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'validation_rules', type: 'object', additionalProperties: true),
        new OA\Property(property: 'searchable_fields', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'visible_fields', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'mapping_configuration', type: 'object', additionalProperties: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
final class MasterDataTypeResourceSchema
{
}
