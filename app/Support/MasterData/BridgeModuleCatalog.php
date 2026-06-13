<?php

namespace App\Support\MasterData;

final class BridgeModuleCatalog
{
    public static function module(): array
    {
        return [
            'code' => 'bridge',
            'label' => 'Jembatan',
            'namespace' => '/api/v1/bridges',
            'description' => 'Endpoint khusus modul master data Jembatan untuk konsumsi aplikasi utama.',
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
            ['key' => 'uuid', 'label' => 'UUID Publik', 'type' => 'uuid', 'api_path' => 'uuid', 'source' => 'master_data.uuid', 'description' => 'Identifier publik stabil untuk integrasi aplikasi utama.'],
            ['key' => 'code', 'label' => 'Kode Record', 'type' => 'string', 'api_path' => 'code', 'source' => 'master_data.code / m_jembatan.uniqid', 'description' => 'Kunci record hasil normalisasi bridge.'],
            ['key' => 'name', 'label' => 'Nama Jembatan', 'type' => 'string', 'api_path' => 'name', 'source' => 'master_data.name / m_jembatan.nama', 'description' => 'Nama tampilan jembatan pada aplikasi.'],
            ['key' => 'status', 'label' => 'Status Record', 'type' => 'string', 'api_path' => 'status', 'source' => 'master_data.status', 'description' => 'Status record API setelah normalisasi.'],
            ['key' => 'bridge_number', 'label' => 'Nomor Jembatan', 'type' => 'string', 'api_path' => 'bridge_number', 'source' => 'data.bridge_number / m_jembatan.no_bh', 'description' => 'Nomor identifikasi bridge di sumber legacy.'],
            ['key' => 'bridge_kind', 'label' => 'Jenis Jembatan', 'type' => 'string', 'api_path' => 'bridge_kind', 'source' => 'data.bridge_kind / m_jembatan.jenis', 'description' => 'Jenis konstruksi atau klasifikasi bridge.'],
            ['key' => 'km_hm', 'label' => 'KM/HM', 'type' => 'string', 'api_path' => 'km_hm', 'source' => 'data.km_hm / m_jembatan.km_hm', 'description' => 'Posisi kilometer/hektometer.'],
            ['key' => 'lintas_code', 'label' => 'Kode Lintas', 'type' => 'string', 'api_path' => 'lintas_code', 'source' => 'data.lintas_code / m_jembatan.lintas', 'description' => 'Relasi jalur/lintas tempat jembatan berada.'],
            ['key' => 'station_start_code', 'label' => 'Stasiun Awal', 'type' => 'string', 'api_path' => 'station_start_code', 'source' => 'data.station_start_code / m_jembatan.stasiun1', 'description' => 'Kode stasiun awal segmen bridge.'],
            ['key' => 'station_end_code', 'label' => 'Stasiun Akhir', 'type' => 'string', 'api_path' => 'station_end_code', 'source' => 'data.station_end_code / m_jembatan.stasiun2', 'description' => 'Kode stasiun akhir segmen bridge.'],
            ['key' => 'operational_area_code', 'label' => 'Wilayah Operasi', 'type' => 'string', 'api_path' => 'operational_area_code', 'source' => 'data.operational_area_code / m_jembatan.wil_op', 'description' => 'Wilayah operasi pengelolaan jembatan.'],
            ['key' => 'coordinates', 'label' => 'Koordinat', 'type' => 'object', 'api_path' => 'coordinates.latitude, coordinates.longitude', 'source' => 'data.latitude / data.longitude / m_jembatan.lat/lon', 'description' => 'Koordinat hasil normalisasi yang siap dipakai aplikasi peta.'],
            ['key' => 'profile', 'label' => 'Profil Struktur', 'type' => 'object', 'api_path' => 'structures.profile', 'source' => 'm_jembatan_profil', 'description' => 'Ringkasan profil dan dimensi utama bridge.'],
            ['key' => 'spans', 'label' => 'Bentang', 'type' => 'array', 'api_path' => 'structures.spans', 'source' => 'm_jembatan_bentang', 'description' => 'Daftar bentang bridge yang telah dinormalisasi.'],
            ['key' => 'substructures', 'label' => 'Struktur Bawah', 'type' => 'array', 'api_path' => 'structures.substructures', 'source' => 'm_jembatan_bawah', 'description' => 'Komponen struktur bawah bridge.'],
            ['key' => 'assessment', 'label' => 'Asesmen', 'type' => 'object', 'api_path' => 'assessment', 'source' => 'm_jembatan_nilai_total + m_jembatan_detil_3', 'description' => 'Ringkasan hasil penilaian kondisi dan pelindung.'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function endpoints(): array
    {
        return [
            ['method' => 'GET', 'path' => '/api/v1/bridges/metadata', 'purpose' => 'Metadata modul bridge, daftar field, filter, sumber legacy, dan pembeda terhadap modul prasarana lain.'],
            ['method' => 'GET', 'path' => '/api/v1/bridges', 'purpose' => 'Daftar jembatan terfilter dan terpagiasi untuk pemrograman aplikasi utama.'],
            ['method' => 'GET', 'path' => '/api/v1/bridges/{uuid}', 'purpose' => 'Detail satu jembatan lengkap dengan struktur, media, lokasi, dan asesmen.'],
        ];
    }
}
