<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MasterDataUpdateRequest',
    properties: [
        new OA\Property(property: 'source_system', type: 'string', nullable: true),
        new OA\Property(property: 'source_table', type: 'string', nullable: true),
        new OA\Property(property: 'source_id', type: 'string', nullable: true),
        new OA\Property(property: 'entity_type', type: 'string', nullable: true),
        new OA\Property(property: 'code', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'parent_code', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'data', type: 'object', additionalProperties: true),
        new OA\Property(property: 'metadata', type: 'object', additionalProperties: true),
        new OA\Property(property: 'status', type: 'string', nullable: true, example: 'inactive'),
        new OA\Property(property: 'synced_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
final class MasterDataUpdateRequestSchema
{
}
