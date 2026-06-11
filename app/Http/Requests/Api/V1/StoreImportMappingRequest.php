<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'version' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'validation_rules' => ['nullable', 'array'],
            'mapping' => ['required', 'array'],
            'mapping.source_system' => ['required', 'string', 'max:100'],
            'mapping.source_table' => ['required', 'string', 'max:100'],
            'mapping.entity_type' => ['required', 'string', 'max:100'],
            'mapping.identity' => ['required', 'array'],
            'mapping.identity.source_id' => ['nullable', 'string', 'max:100'],
            'mapping.identity.code' => ['required', 'string', 'max:100'],
            'mapping.columns' => ['nullable', 'array'],
            'mapping.data' => ['nullable', 'array'],
            'mapping.transformations' => ['nullable', 'array'],
            'mapping.status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
