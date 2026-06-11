<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportMappingRequest',
    required: ['name', 'mapping'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Mapping Legacy Stasiun'),
        new OA\Property(property: 'version', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'is_active', type: 'boolean', nullable: true, example: true),
        new OA\Property(property: 'validation_rules', type: 'object', additionalProperties: true),
        new OA\Property(
            property: 'mapping',
            type: 'object',
            properties: [
                new OA\Property(property: 'source_system', type: 'string', example: 'legacy_djka'),
                new OA\Property(property: 'source_table', type: 'string', example: 'legacy_stations'),
                new OA\Property(property: 'entity_type', type: 'string', example: 'station'),
                new OA\Property(
                    property: 'identity',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'source_id', type: 'string', nullable: true, example: 'id_stasiun'),
                        new OA\Property(property: 'code', type: 'string', example: 'kode_stasiun'),
                    ]
                ),
                new OA\Property(property: 'columns', type: 'object', additionalProperties: true),
                new OA\Property(property: 'data', type: 'object', additionalProperties: true),
                new OA\Property(property: 'transformations', type: 'object', additionalProperties: true),
                new OA\Property(property: 'status', type: 'string', nullable: true, example: 'active'),
            ]
        ),
    ],
    type: 'object'
)]
final class ImportMappingRequestSchema
{
}
