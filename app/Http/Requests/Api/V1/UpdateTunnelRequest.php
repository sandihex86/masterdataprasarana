<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Tunnel;
use App\Services\TunnelDocumentUploadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTunnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nextYear = now()->year + 1;
        $tunnel = Tunnel::query()
            ->where('tunnel_id', (string) $this->route('tunnel_id'))
            ->first();

        return [
            'tunnel_id' => ['prohibited'],
            'kode_aset' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('tunnel.m_tunnels', 'kode_aset')
                    ->ignore($tunnel?->id)
                    ->whereNull('deleted_at'),
            ],
            'nomor_bh' => ['nullable', 'string', 'max:50'],
            'nama_terowongan' => ['sometimes', 'required', 'string', 'max:150'],
            'id_wilayah_kerja' => ['nullable', 'string', 'max:50'],
            'id_lintas' => ['nullable', 'string', 'max:50'],
            'km_hm' => ['nullable', 'string', 'max:30'],
            'panjang_m' => ['nullable', 'numeric', 'min:0'],
            'tahun_bangunan' => ['nullable', 'integer', 'between:1800,'.$nextYear],
            'tahun_operasi' => ['nullable', 'integer', 'between:1800,'.$nextYear],
            'umur_tahun' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'long' => ['nullable', 'numeric', 'between:-180,180'],
            'status_operasi' => ['nullable', 'string', 'max:30'],
            'status_aset' => ['nullable', 'string', 'max:30'],
            'kondisi_terakhir' => ['nullable', 'string', 'max:50'],
            'tgl_inspeksi_terakhir' => ['nullable', 'date'],
            'structure' => ['nullable', 'array'],
            'specs' => ['nullable', 'array'],
            'docs' => ['nullable', 'array'],
            ...TunnelDocumentUploadService::validationRules(),
            ...TunnelDetailRules::structure('structure.', $nextYear),
            ...TunnelDetailRules::specs('specs.'),
            ...TunnelDetailRules::docs('docs.'),
        ];
    }
}
