<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreBridgeSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uniqid' => ['nullable', 'string', 'max:64'],
            'tanggal' => ['nullable', 'date'],
            'wil_ker' => ['nullable', 'string', 'max:255'],
            'id_prov' => ['nullable', 'string', 'max:32'],
            'id_kabkot' => ['nullable', 'string', 'max:32'],
            'wil_op' => ['nullable', 'string', 'max:32'],
            'lat' => ['nullable', 'string', 'max:32'],
            'lon' => ['nullable', 'string', 'max:32'],
            'nama' => ['nullable', 'string', 'max:255'],
            'lintas' => ['nullable', 'string', 'max:64'],
            'stasiun1' => ['nullable', 'string', 'max:64'],
            'stasiun2' => ['nullable', 'string', 'max:64'],
            'no_bh' => ['nullable', 'string', 'max:32'],
            'arah_bh' => ['nullable', 'string', 'max:255'],
            'jenis' => ['nullable', 'string', 'max:255'],
            'km_hm' => ['nullable', 'string', 'max:16'],
            'foto1' => ['nullable', 'string', 'max:255'],
            'foto2' => ['nullable', 'string', 'max:255'],
            'foto3' => ['nullable', 'string', 'max:255'],
            'foto4' => ['nullable', 'string', 'max:255'],
            'caption1' => ['nullable', 'string', 'max:255'],
            'caption2' => ['nullable', 'string', 'max:255'],
            'caption3' => ['nullable', 'string', 'max:255'],
            'caption4' => ['nullable', 'string', 'max:255'],
            'dokumen' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'active' => ['required', 'integer', 'between:0,1'],
            'status' => ['required', 'integer'],
            'statusdata' => ['required', 'integer'],
            'profile' => ['nullable', 'array'],
            'profile.uniqid' => ['nullable', 'string', 'max:64'],
            'profile.perpotongan' => ['nullable', 'string', 'max:255'],
            'profile.jml_lintasan' => ['nullable', 'integer'],
            'profile.jml_bentang' => ['nullable', 'integer'],
            'profile.pjg_bentang1' => ['nullable', 'string', 'max:16'],
            'profile.pjg_bentang2' => ['nullable', 'string', 'max:16'],
            'profile.pjg_bentang3' => ['nullable', 'string', 'max:16'],
            'profile.pjg_total' => ['nullable', 'string', 'max:32'],
            'profile.thn_selesai' => ['nullable', 'string', 'max:4'],
            'profile.rm_bgn_atas' => ['nullable', 'string', 'max:16'],
            'profile.rm_bgn_bawah' => ['nullable', 'string', 'max:16'],
            'profile.active' => ['nullable', 'integer', 'between:0,1'],
            'spans' => ['nullable', 'array'],
            'spans.*.uniqid' => ['nullable', 'string', 'max:64'],
            'spans.*.pjg_bentang' => ['nullable', 'string', 'max:16'],
            'spans.*.urut' => ['nullable', 'integer'],
            'spans.*.active' => ['nullable', 'integer', 'between:0,1'],
            'substructures' => ['nullable', 'array'],
            'substructures.*.uniqid' => ['nullable', 'string', 'max:64'],
            'substructures.*.nomor' => ['nullable', 'string', 'max:255'],
            'substructures.*.material' => ['nullable', 'string', 'max:255'],
            'substructures.*.tipe' => ['nullable', 'string', 'max:255'],
            'substructures.*.manteling' => ['nullable', 'string', 'max:255'],
            'substructures.*.jenis' => ['nullable', 'string', 'max:255'],
            'substructures.*.urut' => ['nullable', 'integer'],
            'protection' => ['nullable', 'array'],
            'protection.uniqid' => ['nullable', 'string', 'max:64'],
            'protection.pelindung_arus_material' => ['nullable', 'string', 'max:255'],
            'protection.pelindung_arus_tipe' => ['nullable', 'string', 'max:255'],
            'protection.pengarah_arus_material' => ['nullable', 'string', 'max:255'],
            'protection.pengarah_arus_tipe' => ['nullable', 'string', 'max:255'],
            'protection.pelindung_longsoran_material' => ['nullable', 'string', 'max:255'],
            'protection.pelindung_longsoran_tipe' => ['nullable', 'string', 'max:255'],
            'assessment' => ['nullable', 'array'],
            'assessment.uniqid' => ['nullable', 'string', 'max:64'],
            'assessment.total' => ['nullable', 'numeric'],
            'assessment.kesimpulan' => ['nullable', 'integer'],
        ];
    }
}
