<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BridgeSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status?->value ?? $this->status,
            'bridge_number' => $data['bridge_number'] ?? null,
            'bridge_kind' => $data['bridge_kind'] ?? null,
            'direction' => $data['direction'] ?? null,
            'km_hm' => $data['km_hm'] ?? null,
            'lintas_code' => $data['lintas_code'] ?? null,
            'province_code' => $data['province_code'] ?? null,
            'city_code' => $data['city_code'] ?? null,
            'operational_area_code' => $data['operational_area_code'] ?? null,
            'station_start_code' => $data['station_start_code'] ?? null,
            'station_end_code' => $data['station_end_code'] ?? null,
            'coordinates' => [
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
