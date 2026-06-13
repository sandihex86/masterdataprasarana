<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TunnelStructureResource extends JsonResource
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
            'jenis_struktur' => $this->jenis_struktur,
            'material_struktur' => $this->material_struktur,
            'material_lining' => $this->material_lining,
            'material_portal' => $this->material_portal,
            'material_invert' => $this->material_invert,
            'metode_konstruksi' => $this->metode_konstruksi,
            'waterproofing' => $this->waterproofing,
            'tahun_rehabilitasi_terakhir' => $this->tahun_rehabilitasi_terakhir,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
