<?php

namespace App\Services;

class BridgeGeoService extends BridgeService
{
    public function geoJson(array $filters): array
    {
        $features = $this->filteredBridgeQuery($filters)
            ->orderBy('id')
            ->get()
            ->filter(fn (object $row): bool => $this->validLatitude($row->lat) && $this->validLongitude($row->lon))
            ->map(fn (object $row): array => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$this->coordinate($row->lon), $this->coordinate($row->lat)],
                ],
                'properties' => [
                    'kode_jembatan' => $row->uniqid,
                    'nama' => $row->nama,
                    'no_bh' => $row->no_bh,
                    'jenis' => $row->jenis,
                    'lintas' => $this->referenceLabel('m_lintas', $row->lintas),
                ],
            ])
            ->values()
            ->all();

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }
}
