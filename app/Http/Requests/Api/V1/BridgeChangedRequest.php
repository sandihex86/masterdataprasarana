<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BridgeChangedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'since' => ['required', 'date_format:Y-m-d H:i:s'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'cursor' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
