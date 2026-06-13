<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBridgeSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
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
