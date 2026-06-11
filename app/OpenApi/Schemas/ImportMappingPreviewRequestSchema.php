<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportMappingPreviewRequest',
    properties: [
        new OA\Property(property: 'mapping_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'limit', type: 'integer', nullable: true, example: 10),
        new OA\Property(property: 'mapping', type: 'object', additionalProperties: true),
    ],
    type: 'object'
)]
final class ImportMappingPreviewRequestSchema
{
}
