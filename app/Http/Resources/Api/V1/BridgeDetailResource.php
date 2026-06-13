<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BridgeDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];
        $actor = $request->user();
        $canViewMetadata = $actor instanceof User && $actor->resolveRole()->canViewSensitiveMetadata();

        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->value ?? $this->status,
            'source' => [
                'system' => $this->source_system,
                'table' => $this->source_table,
                'source_id' => $this->source_id,
            ],
            'bridge_number' => $data['bridge_number'] ?? null,
            'bridge_kind' => $data['bridge_kind'] ?? null,
            'direction' => $data['direction'] ?? null,
            'location' => [
                'km_hm' => $data['km_hm'] ?? null,
                'province_code' => $data['province_code'] ?? null,
                'province_name' => $data['province_name'] ?? null,
                'city_code' => $data['city_code'] ?? null,
                'city_name' => $data['city_name'] ?? null,
                'operational_area_code' => $data['operational_area_code'] ?? null,
                'operational_area_name' => $data['operational_area_name'] ?? null,
                'wil_ker' => $data['wil_ker'] ?? null,
                'wil_ker_name' => $data['wil_ker_name'] ?? null,
                'lintas_code' => $data['lintas_code'] ?? null,
                'lintas_name' => $data['lintas_name'] ?? null,
                'station_start_code' => $data['station_start_code'] ?? null,
                'station_start_name' => $data['station_start_name'] ?? null,
                'station_end_code' => $data['station_end_code'] ?? null,
                'station_end_name' => $data['station_end_name'] ?? null,
            ],
            'coordinates' => [
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'legacy_latitude_raw' => $data['legacy_latitude_raw'] ?? null,
                'legacy_longitude_raw' => $data['legacy_longitude_raw'] ?? null,
            ],
            'media' => [
                'photos' => collect(range(1, 4))
                    ->map(fn (int $index): array => [
                        'path' => $data["photo_{$index}"] ?? null,
                        'caption' => $data["caption_{$index}"] ?? null,
                    ])
                    ->filter(fn (array $item): bool => $item['path'] !== null || $item['caption'] !== null)
                    ->values()
                    ->all(),
                'document_path' => $data['document_path'] ?? null,
                'video_path' => $data['video_path'] ?? null,
            ],
            'structures' => [
                'profile' => $data['profile'] ?? (object) [],
                'spans' => $data['spans'] ?? [],
                'substructures' => $data['substructures'] ?? [],
            ],
            'assessment' => $data['assessment'] ?? (object) [],
            'metadata' => $canViewMetadata ? ($this->metadata ?? (object) []) : null,
            'synced_at' => $this->synced_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
