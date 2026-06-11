<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MasterDataResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'source_system', type: 'string', nullable: true, example: 'legacy_djka'),
        new OA\Property(property: 'source_table', type: 'string', nullable: true, example: 'mst_stasiun'),
        new OA\Property(property: 'source_id', type: 'string', nullable: true, example: '123'),
        new OA\Property(property: 'entity_type', type: 'string', example: 'station'),
        new OA\Property(property: 'code', type: 'string', example: 'GMR'),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'Gambir'),
        new OA\Property(property: 'parent_code', type: 'string', nullable: true, example: '31'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'data', type: 'object', additionalProperties: true),
        new OA\Property(property: 'metadata', type: 'object', nullable: true, additionalProperties: true),
        new OA\Property(property: 'checksum', type: 'string', nullable: true),
        new OA\Property(property: 'version', type: 'integer', example: 1),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'synced_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'deleted_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'type', ref: '#/components/schemas/MasterDataTypeSummary', nullable: true),
    ],
    type: 'object'
)]
final class MasterDataResourceSchema
{
}
