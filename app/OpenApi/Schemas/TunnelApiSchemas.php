<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'TunnelStructure', type: 'object', properties: [
    new OA\Property(property: 'tunnel_id', type: 'string', readOnly: true, example: '01JY0000000000000000000000'),
    new OA\Property(property: 'jenis_struktur', type: 'string', nullable: true, example: 'Terowongan batu'),
    new OA\Property(property: 'material_struktur', type: 'string', nullable: true, example: 'Beton'),
    new OA\Property(property: 'material_lining', type: 'string', nullable: true, example: 'Beton'),
    new OA\Property(property: 'material_portal', type: 'string', nullable: true, example: 'Pasangan batu'),
    new OA\Property(property: 'material_invert', type: 'string', nullable: true, example: 'Beton'),
    new OA\Property(property: 'metode_konstruksi', type: 'string', nullable: true, example: 'Konvensional'),
    new OA\Property(property: 'waterproofing', type: 'string', nullable: true, example: 'Ada'),
    new OA\Property(property: 'tahun_rehabilitasi_terakhir', type: 'integer', nullable: true, example: 2020),
])]
#[OA\Schema(schema: 'TunnelSpec', type: 'object', properties: [
    new OA\Property(property: 'tunnel_id', type: 'string', readOnly: true, example: '01JY0000000000000000000000'),
    new OA\Property(property: 'jumlah_jalur', type: 'integer', nullable: true, example: 1),
    new OA\Property(property: 'jenis_jalur', type: 'string', nullable: true, example: 'Tunggal'),
    new OA\Property(property: 'gauge_m', type: 'number', format: 'float', nullable: true, example: 1.067),
    new OA\Property(property: 'lebar_bersih_m', type: 'number', format: 'float', nullable: true, example: 4.5),
    new OA\Property(property: 'tinggi_bersih_m', type: 'number', format: 'float', nullable: true, example: 5.2),
    new OA\Property(property: 'clearance_horizontal_mm', type: 'integer', nullable: true, example: 4500),
    new OA\Property(property: 'clearance_vertikal_mm', type: 'integer', nullable: true, example: 5200),
    new OA\Property(property: 'bentuk_penampang', type: 'string', nullable: true, example: 'Tapal kuda'),
    new OA\Property(property: 'gradien_persen', type: 'number', format: 'float', nullable: true, example: 1.25),
    new OA\Property(property: 'radius_lengkung_m', type: 'number', format: 'float', nullable: true, example: 300),
    new OA\Property(property: 'catatan_teknis', type: 'string', nullable: true, example: 'Data teknis awal dari master tunnel.'),
])]
#[OA\Schema(schema: 'TunnelDoc', type: 'object', properties: [
    new OA\Property(property: 'tunnel_id', type: 'string', readOnly: true, example: '01JY0000000000000000000000'),
    new OA\Property(property: 'no_ded_bed_kajian_teknis', type: 'string', nullable: true, example: 'DED/TUNNEL/2024/001'),
    new OA\Property(property: 'ded_bed_kajian_teknis', nullable: true, oneOf: [new OA\Schema(type: 'object', additionalProperties: true), new OA\Schema(type: 'string')]),
    new OA\Property(property: 'no_spesifikasi_teknis', type: 'string', nullable: true, example: 'SPES/TUNNEL/2024/001'),
    new OA\Property(property: 'spesifikasi_teknis', nullable: true, oneOf: [new OA\Schema(type: 'object', additionalProperties: true), new OA\Schema(type: 'string')]),
    new OA\Property(property: 'no_shop_drawing', type: 'string', nullable: true, example: 'SHOP/TUNNEL/2024/001'),
    new OA\Property(property: 'shop_drawing', nullable: true, oneOf: [new OA\Schema(type: 'object', additionalProperties: true), new OA\Schema(type: 'string')]),
    new OA\Property(property: 'no_as_built_drawing', type: 'string', nullable: true, example: 'ABD/TUNNEL/2024/001'),
    new OA\Property(property: 'as_built_drawing', nullable: true, oneOf: [new OA\Schema(type: 'object', additionalProperties: true), new OA\Schema(type: 'string')]),
    new OA\Property(property: 'no_dok_hasil_uji', type: 'string', nullable: true, example: 'UJI/TUNNEL/2024/001'),
    new OA\Property(property: 'dok_hasil_uji', nullable: true, oneOf: [new OA\Schema(type: 'object', additionalProperties: true), new OA\Schema(type: 'string')]),
])]
#[OA\Schema(schema: 'TunnelResource', type: 'object', properties: [
    new OA\Property(property: 'tunnel_id', type: 'string', example: '01JY0000000000000000000000'),
    new OA\Property(property: 'kode_aset', type: 'string', nullable: true, example: 'PRAS-04-01'),
    new OA\Property(property: 'nomor_bh', type: 'string', nullable: true, example: '503'),
    new OA\Property(property: 'nama_terowongan', type: 'string', example: 'Sasaksaat'),
    new OA\Property(property: 'id_wilayah_kerja', type: 'string', nullable: true, example: 'FK'),
    new OA\Property(property: 'id_lintas', type: 'string', nullable: true, example: 'FK'),
    new OA\Property(property: 'km_hm', type: 'string', nullable: true, example: '143+144'),
    new OA\Property(property: 'panjang_m', type: 'number', format: 'float', nullable: true, example: 950),
    new OA\Property(property: 'tahun_bangunan', type: 'integer', nullable: true, example: 1904),
    new OA\Property(property: 'tahun_operasi', type: 'integer', nullable: true, example: 1906),
    new OA\Property(property: 'umur_tahun', type: 'integer', nullable: true, example: 120),
    new OA\Property(property: 'coordinates', type: 'object', properties: [
        new OA\Property(property: 'lat', type: 'number', format: 'float', nullable: true, example: -6.8321),
        new OA\Property(property: 'long', type: 'number', format: 'float', nullable: true, example: 107.4521),
    ]),
    new OA\Property(property: 'status_operasi', type: 'string', nullable: true, example: 'Operasi'),
    new OA\Property(property: 'status_aset', type: 'string', nullable: true, example: 'Aktif'),
    new OA\Property(property: 'kondisi_terakhir', type: 'string', nullable: true, example: 'Baik'),
    new OA\Property(property: 'tgl_inspeksi_terakhir', type: 'string', format: 'date', nullable: true, example: '2026-06-13'),
])]
#[OA\Schema(schema: 'TunnelDetailResource', allOf: [
    new OA\Schema(ref: '#/components/schemas/TunnelResource'),
    new OA\Schema(type: 'object', properties: [
        new OA\Property(property: 'structure', ref: '#/components/schemas/TunnelStructure', nullable: true),
        new OA\Property(property: 'specs', ref: '#/components/schemas/TunnelSpec', nullable: true),
        new OA\Property(property: 'docs', ref: '#/components/schemas/TunnelDoc', nullable: true),
    ]),
])]
#[OA\Schema(schema: 'TunnelStoreRequest', required: ['nama_terowongan'], allOf: [
    new OA\Schema(ref: '#/components/schemas/TunnelResource'),
    new OA\Schema(type: 'object', properties: [
        new OA\Property(property: 'structure', ref: '#/components/schemas/TunnelStructure', nullable: true),
        new OA\Property(property: 'specs', ref: '#/components/schemas/TunnelSpec', nullable: true),
        new OA\Property(property: 'docs', ref: '#/components/schemas/TunnelDoc', nullable: true),
    ]),
])]
#[OA\Schema(schema: 'TunnelUpdateRequest', allOf: [
    new OA\Schema(ref: '#/components/schemas/TunnelStoreRequest'),
])]
final class TunnelApiSchemas
{
}
