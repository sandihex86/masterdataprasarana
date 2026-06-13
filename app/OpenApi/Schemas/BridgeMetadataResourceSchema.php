<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeMetadataResource',
    properties: [
        new OA\Property(property: 'module', type: 'object', additionalProperties: true),
        new OA\Property(property: 'record_count', type: 'integer', example: 3077),
        new OA\Property(property: 'active_record_count', type: 'integer', example: 3050),
        new OA\Property(property: 'source_system', type: 'string', example: 'legacy_jembatan'),
        new OA\Property(property: 'source_tables', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'searchable_fields', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'visible_fields', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'filters', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'fields', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'endpoints', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
    ],
    type: 'object'
)]
final class BridgeMetadataResourceSchema
{
}
