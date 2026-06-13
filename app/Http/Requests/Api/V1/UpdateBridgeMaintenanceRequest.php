<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Api\V1\StoreBridgeMaintenanceRequest;

class UpdateBridgeMaintenanceRequest extends StoreBridgeMaintenanceRequest
{
    public function rules(): array
    {
        return [
            'tanggal' => ['sometimes', 'required', 'date'],
            'pemeriksa' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lon' => ['nullable', 'numeric'],
            'catatan' => ['nullable', 'string'],
            'dokumen' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
