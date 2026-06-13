<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class BridgeSourceSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bridge = $this->resource;
        $profile = is_array($bridge['profile'] ?? null) ? $bridge['profile'] : [];
        $assessment = is_array($bridge['assessment'] ?? null) ? $bridge['assessment'] : [];

        return [
            'uniqid' => $bridge['uniqid'] ?? null,
            'source_table' => 'm_jembatan',
            'headline' => [
                'bridge_number' => $bridge['no_bh'] ?? null,
                'bridge_kind' => $bridge['jenis'] ?? null,
                'route_summary' => $bridge['route_summary'] ?? null,
                'work_area' => $bridge['wil_ker_name'] ?? null,
                'km_hm' => $bridge['km_hm'] ?? null,
                'connected_tables_count' => 6,
                'span_count' => count($bridge['spans'] ?? []),
                'substructure_count' => count($bridge['substructures'] ?? []),
                'total_length' => $profile['pjg_total'] ?? null,
                'assessment_total' => $assessment['total'] ?? null,
            ],
            'identity' => [
                'name' => $bridge['nama'] ?? null,
                'direction' => $bridge['arah_bh'] ?? null,
                'coordinates' => [
                    'latitude' => $bridge['lat'] ?? null,
                    'longitude' => $bridge['lon'] ?? null,
                ],
            ],
            'territory_route' => [
                'wilayah_kerja' => $bridge['wil_ker_name'] ?? null,
                'stasiun_awal' => $bridge['stasiun1_name'] ?? null,
                'stasiun_akhir' => $bridge['stasiun2_name'] ?? null,
                'route_summary' => $bridge['route_summary'] ?? null,
            ],
            'updated_at' => $this->asIso($bridge['updated_at'] ?? null),
        ];
    }

    private function asIso(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value)->toIso8601String();
    }
}
