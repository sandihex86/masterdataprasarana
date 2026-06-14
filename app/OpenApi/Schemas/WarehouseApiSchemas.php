<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'WarehouseCoordinates', type: 'object', properties: [
    new OA\Property(property: 'lat', type: 'number', format: 'float', nullable: true, example: -3.2831),
    new OA\Property(property: 'long', type: 'number', format: 'float', nullable: true, example: 104.9132),
])]
#[OA\Schema(schema: 'WarehouseResource', type: 'object', properties: [
    new OA\Property(property: 'id_gudang', type: 'string', readOnly: true, description: 'Public identifier gudang. Digenerate otomatis memakai ULID.', example: '01JY0000000000000000000000'),
    new OA\Property(property: 'kode_gudang', type: 'string', readOnly: true, description: 'Kode gudang, selalu sama dengan id_gudang.', example: '01JY0000000000000000000000'),
    new OA\Property(property: 'nama_gudang', type: 'string', example: 'Gudang Payakabung'),
    new OA\Property(property: 'tipe_gudang', type: 'string', nullable: true, example: 'Material'),
    new OA\Property(property: 'id_wilker', type: 'string', nullable: true, example: 'DIVRE3'),
    new OA\Property(property: 'id_prov', type: 'string', nullable: true, example: '16'),
    new OA\Property(property: 'id_kabkot', type: 'string', nullable: true, example: '1607'),
    new OA\Property(property: 'coordinates', ref: '#/components/schemas/WarehouseCoordinates'),
    new OA\Property(property: 'active', type: 'boolean', example: true),
    new OA\Property(property: 'created_at', type: 'string', nullable: true, example: '2026-06-14 09:00:00'),
    new OA\Property(property: 'updated_at', type: 'string', nullable: true, example: '2026-06-14 09:00:00'),
])]
#[OA\Schema(schema: 'WarehouseStoreRequest', required: ['nama_gudang'], type: 'object', properties: [
    new OA\Property(property: 'nama_gudang', type: 'string', example: 'Gudang Payakabung'),
    new OA\Property(property: 'tipe_gudang', type: 'string', nullable: true, example: 'Material'),
    new OA\Property(property: 'id_wilker', type: 'string', nullable: true, example: 'DIVRE3'),
    new OA\Property(property: 'id_prov', type: 'string', nullable: true, example: '16'),
    new OA\Property(property: 'id_kabkot', type: 'string', nullable: true, example: '1607'),
    new OA\Property(property: 'lat', type: 'number', format: 'float', nullable: true, example: -3.2831),
    new OA\Property(property: 'long', type: 'number', format: 'float', nullable: true, example: 104.9132),
    new OA\Property(property: 'active', type: 'boolean', nullable: true, example: true),
])]
#[OA\Schema(schema: 'WarehouseUpdateRequest', type: 'object', properties: [
    new OA\Property(property: 'nama_gudang', type: 'string', example: 'Gudang Payakabung Utama'),
    new OA\Property(property: 'tipe_gudang', type: 'string', nullable: true, example: 'Suku Cadang'),
    new OA\Property(property: 'id_wilker', type: 'string', nullable: true, example: 'DIVRE3'),
    new OA\Property(property: 'id_prov', type: 'string', nullable: true, example: '16'),
    new OA\Property(property: 'id_kabkot', type: 'string', nullable: true, example: '1607'),
    new OA\Property(property: 'lat', type: 'number', format: 'float', nullable: true, example: -3.28),
    new OA\Property(property: 'long', type: 'number', format: 'float', nullable: true, example: 104.91),
    new OA\Property(property: 'active', type: 'boolean', nullable: true, example: true),
])]
final class WarehouseApiSchemas
{
}
