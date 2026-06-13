<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeDetailResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'code', type: 'string', example: '62e60276e589f'),
        new OA\Property(property: 'name', type: 'string', example: 'BH 05A Cikampek - Tanjungrasa'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(
            property: 'source',
            type: 'object',
            properties: [
                new OA\Property(property: 'system', type: 'string', nullable: true, example: 'legacy_jembatan'),
                new OA\Property(property: 'table', type: 'string', nullable: true, example: 'm_jembatan'),
                new OA\Property(property: 'source_id', type: 'string', nullable: true, example: '62e60276e589f'),
            ]
        ),
        new OA\Property(property: 'bridge_number', type: 'string', nullable: true, example: '05A'),
        new OA\Property(property: 'bridge_kind', type: 'string', nullable: true, example: '2'),
        new OA\Property(property: 'direction', type: 'string', nullable: true, example: 'Hilir'),
        new OA\Property(property: 'location', type: 'object', additionalProperties: true),
        new OA\Property(property: 'coordinates', type: 'object', additionalProperties: true),
        new OA\Property(property: 'media', type: 'object', additionalProperties: true),
        new OA\Property(property: 'structures', type: 'object', additionalProperties: true),
        new OA\Property(property: 'assessment', type: 'object', additionalProperties: true),
        new OA\Property(property: 'metadata', type: 'object', nullable: true, additionalProperties: true),
        new OA\Property(property: 'synced_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
final class BridgeDetailResourceSchema
{
}
