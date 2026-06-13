<?php

namespace App\Http\Resources\Api\V1;

use App\Support\MasterData\BridgeModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BridgeMetadataResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'module' => BridgeModuleCatalog::module(),
            'record_count' => $this['record_count'] ?? 0,
            'active_record_count' => $this['active_record_count'] ?? 0,
            'source_system' => BridgeModuleCatalog::sourceSystem(),
            'source_tables' => BridgeModuleCatalog::sourceTables(),
            'searchable_fields' => BridgeModuleCatalog::searchableFields(),
            'visible_fields' => BridgeModuleCatalog::visibleFields(),
            'filters' => BridgeModuleCatalog::filters(),
            'fields' => BridgeModuleCatalog::fields(),
            'endpoints' => BridgeModuleCatalog::endpoints(),
        ];
    }
}
