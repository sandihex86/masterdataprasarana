<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ApiMeta',
    properties: [
        new OA\Property(property: 'request_id', type: 'string', nullable: true, example: 'baf54e58-867f-4f35-bd50-5aa8d3b4fd8d'),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-06-11T10:00:00Z'),
    ],
    type: 'object'
)]
final class ApiMetaSchema
{
}
