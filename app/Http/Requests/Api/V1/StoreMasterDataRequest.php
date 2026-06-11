<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MasterDataStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMasterDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_system' => ['nullable', 'string', 'max:100'],
            'source_table' => ['nullable', 'string', 'max:100'],
            'source_id' => ['nullable', 'string', 'max:191'],
            'entity_type' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:191'],
            'name' => ['nullable', 'string', 'max:191'],
            'parent_code' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'status' => ['required', Rule::in(MasterDataStatus::values())],
            'synced_at' => ['nullable', 'date'],
        ];
    }
}
