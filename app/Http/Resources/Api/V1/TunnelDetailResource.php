<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class TunnelDetailResource extends TunnelResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'structure' => TunnelStructureResource::make($this->whenLoaded('structure'))->resolve(),
            'specs' => TunnelSpecResource::make($this->whenLoaded('specs'))->resolve(),
            'docs' => TunnelDocResource::make($this->whenLoaded('docs'))->resolve(),
        ];
    }
}
