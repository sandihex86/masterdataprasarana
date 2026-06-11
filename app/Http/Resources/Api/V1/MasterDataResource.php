<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterDataResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actor = $request->user();
        $canViewMetadata = $actor instanceof User && $actor->resolveRole()->canViewSensitiveMetadata();

        return [
            'uuid' => $this->uuid,
            'source_system' => $this->source_system,
            'source_table' => $this->source_table,
            'source_id' => $this->source_id,
            'entity_type' => $this->entity_type,
            'code' => $this->code,
            'name' => $this->name,
            'parent_code' => $this->parent_code,
            'description' => $this->description,
            'data' => $this->data ?? (object) [],
            'metadata' => $canViewMetadata ? ($this->metadata ?? (object) []) : null,
            'checksum' => $canViewMetadata ? $this->checksum : null,
            'version' => $this->version,
            'status' => $this->status?->value ?? $this->status,
            'synced_at' => $this->synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'type' => $this->whenLoaded('type', fn () => [
                'code' => $this->type?->code,
                'name' => $this->type?->name,
            ]),
        ];
    }
}
