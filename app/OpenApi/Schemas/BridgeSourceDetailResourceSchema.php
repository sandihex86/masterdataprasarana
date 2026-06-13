<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceDetailResource',
    properties: [
        new OA\Property(property: 'source_table', type: 'string', example: 'm_jembatan'),
        new OA\Property(property: 'uniqid', type: 'string', example: '6498347da7db7'),
        new OA\Property(property: 'headline', type: 'object', additionalProperties: true),
        new OA\Property(property: 'identity_location', type: 'object', additionalProperties: true),
        new OA\Property(property: 'territory_route', type: 'object', additionalProperties: true),
        new OA\Property(property: 'profile', type: 'object', additionalProperties: true),
        new OA\Property(property: 'spans', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'substructures', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
        new OA\Property(property: 'protection', type: 'object', additionalProperties: true),
        new OA\Property(property: 'assessment', type: 'object', additionalProperties: true),
        new OA\Property(property: 'media', type: 'object', additionalProperties: true),
        new OA\Property(property: 'source_attributes', type: 'object', additionalProperties: true),
        new OA\Property(property: 'relations', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
    ],
    type: 'object'
)]
final class BridgeSourceDetailResourceSchema
{
}
