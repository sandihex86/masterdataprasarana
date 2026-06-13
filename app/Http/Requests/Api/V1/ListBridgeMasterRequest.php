<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ListBridgeMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'keyword' => ['nullable', 'string', 'max:255'],
            'wil_op' => ['nullable', 'string', 'max:255'],
            'wil_ker' => ['nullable', 'string', 'max:255'],
            'lintas' => ['nullable', 'string', 'max:255'],
            'id_prov' => ['nullable', 'string', 'max:32'],
            'id_kabkot' => ['nullable', 'string', 'max:32'],
            'jenis' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'integer'],
            'statusdata' => ['nullable', 'integer'],
        ];
    }
}
