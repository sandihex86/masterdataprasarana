<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PreviewImportMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mapping' => ['required_without:mapping_uuid', 'array'],
            'mapping_uuid' => ['nullable', 'uuid', 'required_without:mapping'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
