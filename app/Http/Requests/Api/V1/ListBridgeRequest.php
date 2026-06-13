<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ListBridgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:191'],
            'bridge_number' => ['nullable', 'string', 'max:64'],
            'bridge_kind' => ['nullable', 'string', 'max:255'],
            'province_code' => ['nullable', 'string', 'max:32'],
            'city_code' => ['nullable', 'string', 'max:32'],
            'operational_area_code' => ['nullable', 'string', 'max:32'],
            'lintas_code' => ['nullable', 'string', 'max:32'],
            'station_start_code' => ['nullable', 'string', 'max:32'],
            'station_end_code' => ['nullable', 'string', 'max:32'],
            'sort' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
