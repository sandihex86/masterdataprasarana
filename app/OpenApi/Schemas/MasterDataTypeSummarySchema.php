<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MasterDataTypeSummary',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'station'),
        new OA\Property(property: 'name', type: 'string', example: 'Stasiun'),
    ],
    type: 'object'
)]
final class MasterDataTypeSummarySchema
{
}
