<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class BridgeSourceDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bridge = $this->resource;
        $profile = is_array($bridge['profile'] ?? null) ? $bridge['profile'] : [];
        $spans = is_array($bridge['spans'] ?? null) ? $bridge['spans'] : [];
        $substructures = is_array($bridge['substructures'] ?? null) ? $bridge['substructures'] : [];
        $protection = is_array($bridge['protection'] ?? null) ? $bridge['protection'] : [];
        $assessment = is_array($bridge['assessment'] ?? null) ? $bridge['assessment'] : [];

        return [
            'source_table' => 'm_jembatan',
            'uniqid' => $bridge['uniqid'] ?? null,
            'headline' => [
                'bridge_number' => $bridge['no_bh'] ?? null,
                'bridge_kind' => $bridge['jenis'] ?? null,
                'route_summary' => $bridge['route_summary'] ?? null,
                'work_area' => $bridge['wil_ker_name'] ?? null,
                'km_hm' => $bridge['km_hm'] ?? null,
                'connected_tables_count' => 6,
                'span_count' => count($spans),
                'substructure_count' => count($substructures),
                'total_length' => $profile['pjg_total'] ?? null,
                'assessment_total' => $assessment['total'] ?? null,
            ],
            'identity_location' => [
                'uniqid' => $bridge['uniqid'] ?? null,
                'bridge_number' => $bridge['no_bh'] ?? null,
                'name' => $bridge['nama'] ?? null,
                'bridge_kind' => $bridge['jenis'] ?? null,
                'date' => $this->asIso($bridge['tanggal'] ?? null),
                'km_hm' => $bridge['km_hm'] ?? null,
                'direction' => $bridge['arah_bh'] ?? null,
                'coordinates' => [
                    'latitude' => $bridge['lat'] ?? null,
                    'longitude' => $bridge['lon'] ?? null,
                ],
            ],
            'territory_route' => [
                'wilayah_kerja' => $this->lookup($bridge['wil_ker'] ?? null, $bridge['wil_ker_name'] ?? null),
                'wilayah_operasi' => $this->lookup($bridge['wil_op'] ?? null, $bridge['wil_op_name'] ?? null),
                'provinsi' => $this->lookup($bridge['id_prov'] ?? null, $bridge['province_name'] ?? null),
                'kabupaten_kota' => $this->lookup($bridge['id_kabkot'] ?? null, $bridge['city_name'] ?? null),
                'lintas' => $this->lookup($bridge['lintas'] ?? null, $bridge['lintas_name'] ?? null),
                'stasiun_awal' => $this->lookup($bridge['stasiun1'] ?? null, $bridge['stasiun1_name'] ?? null),
                'stasiun_akhir' => $this->lookup($bridge['stasiun2'] ?? null, $bridge['stasiun2_name'] ?? null),
                'route_summary' => $bridge['route_summary'] ?? null,
            ],
            'profile' => [
                ...$profile,
                'created_at' => $this->asIso($profile['created_at'] ?? null),
                'updated_at' => $this->asIso($profile['updated_at'] ?? null),
            ],
            'spans' => array_map(fn (array $span): array => [
                ...$span,
                'created_at' => $this->asIso($span['created_at'] ?? null),
                'updated_at' => $this->asIso($span['updated_at'] ?? null),
            ], $spans),
            'substructures' => array_map(fn (array $row): array => [
                ...$row,
                'created_at' => $this->asIso($row['created_at'] ?? null),
                'updated_at' => $this->asIso($row['updated_at'] ?? null),
            ], $substructures),
            'protection' => [
                ...$protection,
                'created_at' => $this->asIso($protection['created_at'] ?? null),
                'updated_at' => $this->asIso($protection['updated_at'] ?? null),
            ],
            'assessment' => [
                ...$assessment,
                'kesimpulan_label' => $this->assessmentLabel($assessment['kesimpulan'] ?? null),
                'created_at' => $this->asIso($assessment['created_at'] ?? null),
                'updated_at' => $this->asIso($assessment['updated_at'] ?? null),
            ],
            'media' => [
                'foto1' => $bridge['foto1'] ?? null,
                'caption1' => $bridge['caption1'] ?? null,
                'foto2' => $bridge['foto2'] ?? null,
                'caption2' => $bridge['caption2'] ?? null,
                'foto3' => $bridge['foto3'] ?? null,
                'caption3' => $bridge['caption3'] ?? null,
                'foto4' => $bridge['foto4'] ?? null,
                'caption4' => $bridge['caption4'] ?? null,
                'dokumen' => $bridge['dokumen'] ?? null,
                'video' => $bridge['video'] ?? null,
                'catatan' => $bridge['catatan'] ?? null,
            ],
            'source_attributes' => [
                'id' => $bridge['id'] ?? null,
                'active' => $bridge['active'] ?? null,
                'status' => $bridge['status'] ?? null,
                'statusdata' => $bridge['statusdata'] ?? null,
                'created_by' => $bridge['created_by'] ?? null,
                'created_at' => $this->asIso($bridge['created_at'] ?? null),
                'updated_by' => $bridge['updated_by'] ?? null,
                'updated_at' => $this->asIso($bridge['updated_at'] ?? null),
                'deleted_at' => $this->asIso($bridge['deleted_at'] ?? null),
            ],
            'relations' => $bridge['relations'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lookup(mixed $code, mixed $label): array
    {
        return [
            'code' => $code,
            'label' => $label,
            'display' => $label && $code && (string) $label !== (string) $code
                ? $label.' ('.$code.')'
                : ($label ?? $code),
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

    private function assessmentLabel(mixed $value): ?string
    {
        return match ((string) $value) {
            '1' => 'Baik',
            '2' => 'Sedang',
            '3' => 'Rusak Ringan',
            '4' => 'Rusak Berat',
            default => null,
        };
    }
}
