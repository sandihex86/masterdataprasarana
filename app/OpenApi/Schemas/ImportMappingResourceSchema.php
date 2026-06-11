<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportMappingResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string', example: 'Mapping Legacy Stasiun'),
        new OA\Property(property: 'source_system', type: 'string', example: 'legacy_djka'),
        new OA\Property(property: 'source_table', type: 'string', example: 'legacy_stations'),
        new OA\Property(property: 'entity_type', type: 'string', example: 'station'),
        new OA\Property(property: 'version', type: 'integer', example: 1),
        new OA\Property(property: 'mapping', type: 'object', additionalProperties: true),
        new OA\Property(property: 'transformations', type: 'object', additionalProperties: true),
        new OA\Property(property: 'validation_rules', type: 'object', additionalProperties: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
final class ImportMappingResourceSchema
{
}
