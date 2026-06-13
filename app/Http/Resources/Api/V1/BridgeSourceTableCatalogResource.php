<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BridgeSourceTableCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'table' => $this['table'] ?? null,
            'label' => $this['label'] ?? null,
            'description' => $this['description'] ?? null,
            'row_count' => $this['row_count'] ?? 0,
            'endpoints' => $this['endpoints'] ?? [],
        ];
    }
}
