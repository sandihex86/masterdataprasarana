<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationMeta',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'per_page', type: 'integer', example: 25),
        new OA\Property(property: 'total', type: 'integer', example: 120),
        new OA\Property(property: 'last_page', type: 'integer', example: 5),
    ],
    type: 'object'
)]
final class PaginationMetaSchema
{
}
