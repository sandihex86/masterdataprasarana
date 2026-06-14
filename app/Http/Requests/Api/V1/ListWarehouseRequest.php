<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:191'],
            'tipe_gudang' => ['nullable', 'string', 'max:100'],
            'id_wilker' => ['nullable', 'string', 'max:50'],
            'id_prov' => ['nullable', 'string', 'max:50'],
            'id_kabkot' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort_by' => ['nullable', Rule::in([
                'id_gudang',
                'kode_gudang',
                'nama_gudang',
                'tipe_gudang',
                'id_wilker',
                'id_prov',
                'id_kabkot',
                'active',
                'created_at',
                'updated_at',
            ])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
