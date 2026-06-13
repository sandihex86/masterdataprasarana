<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceTableRecordUpdateRequest',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            additionalProperties: true,
            example: ['nama' => 'Wilayah Kerja Jakarta Revisi', 'active' => 1]
        ),
    ],
    type: 'object'
)]
final class BridgeSourceTableRecordUpdateRequestSchema
{
}
