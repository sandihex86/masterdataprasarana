<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BridgeBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'cursor' => ['nullable', 'integer', 'min:0'],
            'last_id' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'integer'],
            'updated_since' => ['nullable', 'date'],
        ];
    }
}
