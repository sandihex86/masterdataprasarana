<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceTableRecordStoreRequest',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            additionalProperties: true,
            example: ['kode' => 'WK-99', 'nama' => 'Wilayah Kerja Baru', 'active' => 1]
        ),
    ],
    type: 'object'
)]
final class BridgeSourceTableRecordStoreRequestSchema
{
}
