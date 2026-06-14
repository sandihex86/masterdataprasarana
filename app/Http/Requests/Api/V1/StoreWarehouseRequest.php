<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
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
            'id_gudang' => ['prohibited'],
            'kode_gudang' => ['prohibited'],
            'nama_gudang' => ['required', 'string', 'max:191'],
            'tipe_gudang' => ['nullable', 'string', 'max:100'],
            'id_wilker' => ['nullable', 'string', 'max:50'],
            'id_prov' => ['nullable', 'string', 'max:50'],
            'id_kabkot' => ['nullable', 'string', 'max:50'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'long' => ['nullable', 'numeric', 'between:-180,180'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
