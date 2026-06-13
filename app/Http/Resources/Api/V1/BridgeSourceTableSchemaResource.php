<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BridgeSourceTableSchemaResource extends JsonResource
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
            'primary_key' => $this['primary_key'] ?? [],
            'unique_keys' => $this['unique_keys'] ?? [],
            'required_columns' => $this['required_columns'] ?? [],
            'columns' => $this['columns'] ?? [],
            'indexes' => $this['indexes'] ?? [],
        ];
    }
}
