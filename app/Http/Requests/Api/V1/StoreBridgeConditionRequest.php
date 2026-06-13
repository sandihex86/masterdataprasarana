<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBridgeConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'total' => ['nullable', 'numeric'],
            'kesimpulan' => ['nullable', 'integer'],
        ];
    }
}
