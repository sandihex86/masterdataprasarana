<?php

namespace App\Http\Requests\Api\V1;

class UpdateBridgeSurveyRequest extends StoreBridgeSurveyRequest
{
    public function rules(): array
    {
        return [
            'tanggal' => ['sometimes', 'required', 'date'],
            'pemeriksa' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lon' => ['nullable', 'numeric'],
            'catatan' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:255'],
            'dokumen' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'string', 'max:255'],
        ];
    }
}
