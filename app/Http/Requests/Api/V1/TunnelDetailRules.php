<?php

namespace App\Http\Requests\Api\V1;

final class TunnelDetailRules
{
    public static function structure(string $prefix, int $nextYear): array
    {
        return [
            $prefix.'tunnel_id' => ['prohibited'],
            $prefix.'jenis_struktur' => ['nullable', 'string', 'max:100'],
            $prefix.'material_struktur' => ['nullable', 'string', 'max:100'],
            $prefix.'material_lining' => ['nullable', 'string', 'max:100'],
            $prefix.'material_portal' => ['nullable', 'string', 'max:100'],
            $prefix.'material_invert' => ['nullable', 'string', 'max:100'],
            $prefix.'metode_konstruksi' => ['nullable', 'string', 'max:100'],
            $prefix.'waterproofing' => ['nullable', 'string', 'max:100'],
            $prefix.'tahun_rehabilitasi_terakhir' => ['nullable', 'integer', 'between:1800,'.$nextYear],
        ];
    }

    public static function specs(string $prefix): array
    {
        return [
            $prefix.'tunnel_id' => ['prohibited'],
            $prefix.'jumlah_jalur' => ['nullable', 'integer', 'min:1', 'max:255'],
            $prefix.'jenis_jalur' => ['nullable', 'string', 'max:50'],
            $prefix.'gauge_m' => ['nullable', 'numeric', 'min:0'],
            $prefix.'lebar_bersih_m' => ['nullable', 'numeric', 'min:0'],
            $prefix.'tinggi_bersih_m' => ['nullable', 'numeric', 'min:0'],
            $prefix.'clearance_horizontal_mm' => ['nullable', 'integer', 'min:1'],
            $prefix.'clearance_vertikal_mm' => ['nullable', 'integer', 'min:1'],
            $prefix.'bentuk_penampang' => ['nullable', 'string', 'max:100'],
            $prefix.'gradien_persen' => ['nullable', 'numeric', 'min:0'],
            $prefix.'radius_lengkung_m' => ['nullable', 'numeric', 'min:0'],
            $prefix.'catatan_teknis' => ['nullable', 'string'],
        ];
    }

    public static function docs(string $prefix): array
    {
        return [
            $prefix.'tunnel_id' => ['prohibited'],
            $prefix.'no_ded_bed_kajian_teknis' => ['nullable', 'string', 'max:100'],
            $prefix.'ded_bed_kajian_teknis' => ['nullable'],
            $prefix.'no_spesifikasi_teknis' => ['nullable', 'string', 'max:100'],
            $prefix.'spesifikasi_teknis' => ['nullable'],
            $prefix.'no_shop_drawing' => ['nullable', 'string', 'max:100'],
            $prefix.'shop_drawing' => ['nullable'],
            $prefix.'no_as_built_drawing' => ['nullable', 'string', 'max:100'],
            $prefix.'as_built_drawing' => ['nullable'],
            $prefix.'no_dok_hasil_uji' => ['nullable', 'string', 'max:100'],
            $prefix.'dok_hasil_uji' => ['nullable'],
        ];
    }
}
