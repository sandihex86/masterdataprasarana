<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BridgeSourceSummaryResource',
    properties: [
        new OA\Property(property: 'uniqid', type: 'string', example: '6498347da7db7'),
        new OA\Property(property: 'source_table', type: 'string', example: 'm_jembatan'),
        new OA\Property(
            property: 'headline',
            type: 'object',
            properties: [
                new OA\Property(property: 'bridge_number', type: 'string', nullable: true, example: '334'),
                new OA\Property(property: 'bridge_kind', type: 'string', nullable: true, example: '2'),
                new OA\Property(property: 'route_summary', type: 'string', nullable: true, example: 'Citeras -> Rangkasbitung'),
                new OA\Property(property: 'work_area', type: 'string', nullable: true, example: 'BTP Kelas I Jakarta'),
                new OA\Property(property: 'km_hm', type: 'string', nullable: true, example: '78+613'),
                new OA\Property(property: 'connected_tables_count', type: 'integer', example: 6),
                new OA\Property(property: 'span_count', type: 'integer', example: 1),
                new OA\Property(property: 'substructure_count', type: 'integer', example: 2),
                new OA\Property(property: 'total_length', type: 'string', nullable: true, example: '20'),
                new OA\Property(property: 'assessment_total', type: 'number', format: 'float', nullable: true, example: 4),
            ]
        ),
        new OA\Property(property: 'identity', type: 'object', additionalProperties: true),
        new OA\Property(property: 'territory_route', type: 'object', additionalProperties: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
final class BridgeSourceSummaryResourceSchema
{
}
