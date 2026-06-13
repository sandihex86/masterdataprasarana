<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TunnelDocResource extends JsonResource
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
            'no_ded_bed_kajian_teknis' => $this->no_ded_bed_kajian_teknis,
            'ded_bed_kajian_teknis' => $this->ded_bed_kajian_teknis,
            'no_spesifikasi_teknis' => $this->no_spesifikasi_teknis,
            'spesifikasi_teknis' => $this->spesifikasi_teknis,
            'no_shop_drawing' => $this->no_shop_drawing,
            'shop_drawing' => $this->shop_drawing,
            'no_as_built_drawing' => $this->no_as_built_drawing,
            'as_built_drawing' => $this->as_built_drawing,
            'no_dok_hasil_uji' => $this->no_dok_hasil_uji,
            'dok_hasil_uji' => $this->dok_hasil_uji,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
