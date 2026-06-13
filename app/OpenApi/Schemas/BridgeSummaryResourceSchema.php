<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSummaryResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'code', type: 'string', example: '62e60276e589f'),
        new OA\Property(property: 'name', type: 'string', example: 'BH 05A Cikampek - Tanjungrasa'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'bridge_number', type: 'string', nullable: true, example: '05A'),
        new OA\Property(property: 'bridge_kind', type: 'string', nullable: true, example: 'Box Culvert'),
        new OA\Property(property: 'direction', type: 'string', nullable: true, example: 'Hilir'),
        new OA\Property(property: 'km_hm', type: 'string', nullable: true, example: '84+842'),
        new OA\Property(property: 'lintas_code', type: 'string', nullable: true, example: 'LNT-01'),
        new OA\Property(property: 'province_code', type: 'string', nullable: true, example: '32'),
        new OA\Property(property: 'city_code', type: 'string', nullable: true, example: '3215'),
        new OA\Property(property: 'operational_area_code', type: 'string', nullable: true, example: '2'),
        new OA\Property(property: 'station_start_code', type: 'string', nullable: true, example: 'CKP'),
        new OA\Property(property: 'station_end_code', type: 'string', nullable: true, example: 'TJS'),
        new OA\Property(
            property: 'coordinates',
            type: 'object',
            properties: [
                new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: -6.34256),
                new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 106.272981),
            ]
        ),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
final class BridgeSummaryResourceSchema
{
}
