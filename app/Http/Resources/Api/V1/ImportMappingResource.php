<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportMappingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'source_system' => $this->source_system,
            'source_table' => $this->source_table,
            'entity_type' => $this->entity_type,
            'version' => $this->version,
            'mapping' => $this->mapping ?? (object) [],
            'transformations' => $this->transformations ?? (object) [],
            'validation_rules' => $this->validation_rules ?? (object) [],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
