<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TunnelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tunnel_id' => $this->tunnel_id,
            'kode_aset' => $this->kode_aset,
            'nomor_bh' => $this->nomor_bh,
            'nama_terowongan' => $this->nama_terowongan,
            'id_wilayah_kerja' => $this->id_wilayah_kerja,
            'id_lintas' => $this->id_lintas,
            'km_hm' => $this->km_hm,
            'panjang_m' => $this->decimalValue($this->panjang_m),
            'tahun_bangunan' => $this->tahun_bangunan,
            'tahun_operasi' => $this->tahun_operasi,
            'umur_tahun' => $this->umur_tahun,
            'coordinates' => [
                'lat' => $this->decimalValue($this->lat),
                'long' => $this->decimalValue($this->long),
            ],
            'status_operasi' => $this->status_operasi,
            'status_aset' => $this->status_aset,
            'kondisi_terakhir' => $this->kondisi_terakhir,
            'tgl_inspeksi_terakhir' => $this->tgl_inspeksi_terakhir?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function decimalValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
