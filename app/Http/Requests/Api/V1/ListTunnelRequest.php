<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTunnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:191'],
            'id_wilayah_kerja' => ['nullable', 'string', 'max:50'],
            'id_lintas' => ['nullable', 'string', 'max:50'],
            'status_operasi' => ['nullable', 'string', 'max:30'],
            'status_aset' => ['nullable', 'string', 'max:30'],
            'kondisi_terakhir' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort_by' => ['nullable', Rule::in([
                'kode_aset',
                'nomor_bh',
                'nama_terowongan',
                'id_wilayah_kerja',
                'id_lintas',
                'km_hm',
                'panjang_m',
                'tahun_bangunan',
                'tahun_operasi',
                'status_operasi',
                'status_aset',
                'kondisi_terakhir',
                'tgl_inspeksi_terakhir',
                'created_at',
                'updated_at',
            ])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
