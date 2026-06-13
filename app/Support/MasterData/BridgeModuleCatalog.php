<?php

namespace App\Support\MasterData;

final class BridgeModuleCatalog
{
    public static function module(): array
    {
        return [
            'code' => 'bridge',
            'label' => 'Jembatan',
            'namespace' => '/api/v1/master/bridges',
            'description' => 'Endpoint khusus Master Data Jembatan untuk konsumsi aplikasi utama dan integrasi lintas aplikasi.',
            'distinction' => 'Modul Jembatan adalah namespace V1 spesialis pertama. Modul lain seperti Fasilitas Operasional, Gudang, Sertifikat, dan Jalur akan dikembangkan dengan endpoint spesifik masing-masing, bukan dicampur ke namespace bridges.',
        ];
    }

    public static function sourceSystem(): string
    {
        return 'legacy_jembatan';
    }

    /**
     * @return array<int, string>
     */
    public static function sourceTables(): array
    {
        return [
            'm_jembatan',
            'm_jembatan_profil',
            'm_jembatan_bentang',
            'm_jembatan_bawah',
            'm_jembatan_detil_3',
            'm_jembatan_nilai_total',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function searchableFields(): array
    {
        return BridgeModuleDefinition::typeAttributes()['searchable_fields'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public static function visibleFields(): array
    {
        return BridgeModuleDefinition::typeAttributes()['visible_fields'] ?? [];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function filters(): array
    {
        return [
            ['name' => 'search', 'type' => 'string', 'path' => 'code,name,description,data.bridge_number,data.km_hm,data.station_start_name,data.station_end_name', 'description' => 'Pencarian bebas untuk identitas dan lokasi jembatan.'],
            ['name' => 'status', 'type' => 'string', 'path' => 'master_data.status', 'description' => 'Status record hasil normalisasi.'],
            ['name' => 'code', 'type' => 'string', 'path' => 'master_data.code', 'description' => 'Kode record bridge hasil normalisasi.'],
            ['name' => 'bridge_number', 'type' => 'string', 'path' => 'data.bridge_number', 'description' => 'Nomor jembatan dari sumber legacy.'],
            ['name' => 'bridge_kind', 'type' => 'string', 'path' => 'data.bridge_kind', 'description' => 'Jenis atau klasifikasi jembatan.'],
            ['name' => 'province_code', 'type' => 'string', 'path' => 'data.province_code', 'description' => 'Filter kode provinsi.'],
            ['name' => 'city_code', 'type' => 'string', 'path' => 'data.city_code', 'description' => 'Filter kode kabupaten/kota.'],
            ['name' => 'operational_area_code', 'type' => 'string', 'path' => 'data.operational_area_code', 'description' => 'Filter wilayah operasi.'],
            ['name' => 'lintas_code', 'type' => 'string', 'path' => 'data.lintas_code', 'description' => 'Filter lintas jalur.'],
            ['name' => 'station_start_code', 'type' => 'string', 'path' => 'data.station_start_code', 'description' => 'Filter stasiun awal.'],
            ['name' => 'station_end_code', 'type' => 'string', 'path' => 'data.station_end_code', 'description' => 'Filter stasiun akhir.'],
            ['name' => 'sort', 'type' => 'string', 'path' => 'code,name,status,updated_at,created_at', 'description' => 'Urutkan dengan awalan `-` untuk descending.'],
            ['name' => 'per_page', 'type' => 'integer', 'path' => 'pagination', 'description' => 'Jumlah data per halaman, maksimum 100.'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function fields(): array
    {
        return [
            ['key' => 'kode_jembatan', 'label' => 'Kode Jembatan', 'type' => 'string', 'api_path' => 'kode_jembatan', 'source' => 'm_jembatan.uniqid', 'description' => 'Identifier utama jembatan yang dipakai sebagai kunci path dan cursor integrasi.'],
            ['key' => 'nama', 'label' => 'Nama Jembatan', 'type' => 'string', 'api_path' => 'nama', 'source' => 'm_jembatan.nama', 'description' => 'Nama tampilan jembatan pada aplikasi dan hasil pencarian.'],
            ['key' => 'no_bh', 'label' => 'Nomor BH', 'type' => 'string|null', 'api_path' => 'no_bh', 'source' => 'm_jembatan.no_bh', 'description' => 'Nomor bangunan hikmat/jembatan dari data sumber.'],
            ['key' => 'jenis', 'label' => 'Jenis', 'type' => 'string|null', 'api_path' => 'jenis', 'source' => 'm_jembatan.jenis', 'description' => 'Kode atau klasifikasi jenis jembatan sesuai sumber.'],
            ['key' => 'km_hm', 'label' => 'KM/HM', 'type' => 'string|null', 'api_path' => 'km_hm', 'source' => 'm_jembatan.km_hm', 'description' => 'Posisi kilometer/hektometer jembatan pada lintas.'],
            ['key' => 'lintas', 'label' => 'Lintas', 'type' => 'string|null', 'api_path' => 'lintas', 'source' => 'm_jembatan.lintas / lookup lintas', 'description' => 'Nama atau kode lintas jalur tempat jembatan berada.'],
            ['key' => 'stasiun1', 'label' => 'Stasiun Awal', 'type' => 'string|null', 'api_path' => 'stasiun1', 'source' => 'm_jembatan.stasiun1 / m_stasiun', 'description' => 'Stasiun awal segmen jembatan.'],
            ['key' => 'stasiun2', 'label' => 'Stasiun Akhir', 'type' => 'string|null', 'api_path' => 'stasiun2', 'source' => 'm_jembatan.stasiun2 / m_stasiun', 'description' => 'Stasiun akhir segmen jembatan.'],
            ['key' => 'wilayah_operasi', 'label' => 'Wilayah Operasi', 'type' => 'string|null', 'api_path' => 'wilayah_operasi', 'source' => 'm_jembatan.wil_op / lookup wilayah operasi', 'description' => 'Wilayah operasi/Daop yang mengelola area jembatan.'],
            ['key' => 'wilayah_kerja', 'label' => 'Wilayah Kerja', 'type' => 'string|null', 'api_path' => 'wilayah_kerja', 'source' => 'm_jembatan.wil_ker / m_wilayah_kerja', 'description' => 'Wilayah kerja/BTP terkait jembatan.'],
            ['key' => 'id_prov', 'label' => 'ID Provinsi', 'type' => 'string|null', 'api_path' => 'id_prov', 'source' => 'm_jembatan.id_prov / m_provinsi', 'description' => 'Kode provinsi lokasi jembatan.'],
            ['key' => 'id_kabkot', 'label' => 'ID Kab/Kota', 'type' => 'string|null', 'api_path' => 'id_kabkot', 'source' => 'm_jembatan.id_kabkot / m_kabkot', 'description' => 'Kode kabupaten/kota lokasi jembatan.'],
            ['key' => 'lat', 'label' => 'Latitude', 'type' => 'number|null', 'api_path' => 'lat', 'source' => 'm_jembatan.lat', 'description' => 'Koordinat latitude untuk peta dan endpoint GeoJSON.'],
            ['key' => 'lon', 'label' => 'Longitude', 'type' => 'number|null', 'api_path' => 'lon', 'source' => 'm_jembatan.lon', 'description' => 'Koordinat longitude untuk peta dan endpoint GeoJSON.'],
            ['key' => 'active', 'label' => 'Active', 'type' => 'integer|null', 'api_path' => 'active', 'source' => 'm_jembatan.active', 'description' => 'Flag aktif dari data sumber.'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'integer|null', 'api_path' => 'status', 'source' => 'm_jembatan.status', 'description' => 'Status operasional/administratif dari sumber jembatan.'],
            ['key' => 'statusdata', 'label' => 'Status Data', 'type' => 'integer|null', 'api_path' => 'statusdata', 'source' => 'm_jembatan.statusdata', 'description' => 'Status kelengkapan atau validitas data sumber.'],
            ['key' => 'created_at', 'label' => 'Created At', 'type' => 'date-time|null', 'api_path' => 'created_at', 'source' => 'm_jembatan.created_at', 'description' => 'Waktu pembuatan record pada sumber.'],
            ['key' => 'updated_at', 'label' => 'Updated At', 'type' => 'date-time|null', 'api_path' => 'updated_at', 'source' => 'm_jembatan.updated_at', 'description' => 'Waktu perubahan terakhir, dipakai oleh filter sinkronisasi `updated_since`.'],
            ['key' => 'profil', 'label' => 'Profil', 'type' => 'object|null', 'api_path' => 'profil', 'source' => 'm_jembatan_profil', 'description' => 'Profil teknis utama seperti perpotongan, jumlah lintasan, jumlah bentang, panjang total, dan tahun selesai.'],
            ['key' => 'nilai_kondisi_terakhir', 'label' => 'Nilai Kondisi Terakhir', 'type' => 'object|null', 'api_path' => 'nilai_kondisi_terakhir', 'source' => 'm_jembatan_nilai_total', 'description' => 'Data penilaian kondisi terbaru yang ditemukan untuk jembatan.'],
            ['key' => 'perawatan_terakhir', 'label' => 'Perawatan Terakhir', 'type' => 'object|null', 'api_path' => 'perawatan_terakhir', 'source' => 'm_jembatan_perawatan', 'description' => 'Riwayat perawatan terbaru yang tersedia untuk jembatan.'],
            ['key' => 'survey_terakhir', 'label' => 'Survey Terakhir', 'type' => 'object|null', 'api_path' => 'survey_terakhir', 'source' => 'm_jembatan_survey', 'description' => 'Riwayat survey terbaru yang tersedia untuk jembatan.'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function endpoints(): array
    {
        return [
            ['method' => 'GET', 'path' => '/api/v1/master/bridges', 'purpose' => 'Daftar master jembatan terfilter dan terpagiasi.'],
            ['method' => 'GET', 'path' => '/api/v1/master/bridges/batch', 'purpose' => 'Batch master jembatan berbasis cursor id untuk integrasi besar.'],
            ['method' => 'GET', 'path' => '/api/v1/master/bridges/{kode_jembatan}', 'purpose' => 'Detail satu jembatan lengkap dengan profil dan ringkasan riwayat terbaru.'],
        ];
    }
}
