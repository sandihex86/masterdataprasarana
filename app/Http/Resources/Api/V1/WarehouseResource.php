<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_gudang' => $this->value('id_gudang'),
            'kode_gudang' => $this->value('kode_gudang'),
            'nama_gudang' => $this->value('nama_gudang'),
            'tipe_gudang' => $this->value('tipe_gudang'),
            'id_wilker' => $this->value('id_wilker'),
            'id_prov' => $this->value('id_prov'),
            'id_kabkot' => $this->value('id_kabkot'),
            'coordinates' => [
                'lat' => $this->decimalValue($this->value('lat')),
                'long' => $this->decimalValue($this->value('long')),
            ],
            'active' => $this->boolValue($this->value('active')),
            'created_at' => $this->dateValue($this->value('created_at')),
            'updated_at' => $this->dateValue($this->value('updated_at')),
        ];
    }

    private function value(string $key): mixed
    {
        if (is_array($this->resource)) {
            return $this->resource[$key] ?? null;
        }

        return $this->resource->{$key} ?? null;
    }

    private function decimalValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function boolValue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_string($value) ? $value : (string) $value;
    }
}
