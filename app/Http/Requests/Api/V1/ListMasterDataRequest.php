<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ListMasterDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:191'],
            'parent_code' => ['nullable', 'string', 'max:191'],
            'source_system' => ['nullable', 'string', 'max:100'],
            'source_table' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:191'],
            'sort' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
