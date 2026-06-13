<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TunnelSpecResource extends JsonResource
{
    /**
     * @return array<string, mixed>|null
     */
    public function toArray(Request $request): ?array
    {
        if ($this->resource === null) {
            return null;
        }

        return [
            'tunnel_id' => $this->tunnel_id,
            'jumlah_jalur' => $this->jumlah_jalur,
            'jenis_jalur' => $this->jenis_jalur,
            'gauge_m' => $this->decimalValue($this->gauge_m),
            'lebar_bersih_m' => $this->decimalValue($this->lebar_bersih_m),
            'tinggi_bersih_m' => $this->decimalValue($this->tinggi_bersih_m),
            'clearance_horizontal_mm' => $this->clearance_horizontal_mm,
            'clearance_vertikal_mm' => $this->clearance_vertikal_mm,
            'bentuk_penampang' => $this->bentuk_penampang,
            'gradien_persen' => $this->decimalValue($this->gradien_persen),
            'radius_lengkung_m' => $this->decimalValue($this->radius_lengkung_m),
            'catatan_teknis' => $this->catatan_teknis,
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
