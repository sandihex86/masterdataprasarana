# Bridge API

## Tujuan

Bridge API menyediakan master data jembatan dari tabel legacy `m_jembatan` agar dapat dipakai aplikasi lain sebagai referensi, integrasi batch, peta/GeoJSON, detail teknis, kondisi, perawatan, dan dropdown referensi.

Semua response menggunakan format standar:

```json
{
  "success": true,
  "message": "Data berhasil diambil.",
  "data": {},
  "meta": {}
}
```

Error dikembalikan dengan `success: false` dan detail pada `error` serta alias `errors`.

## Swagger

Swagger UI tersedia di:

```text
http://domain-aplikasi/api/documentation
```

Endpoint Bridge selain `/api/v1/integration/health` memakai autentikasi Sanctum. Di Swagger UI, klik tombol **Authorize**, pilih skema `sanctumBearer`, lalu masukkan `plain_text_token` hasil generate dari Dashboard Superadmin tanpa prefix `Bearer`. Token ini biasanya berformat `id|token`, misalnya:

```text
12|abcdefxxxxxxxxxxxxxxxxxxxxxxxx
```

Jangan masukkan UUID client API seperti `019ebe91-...`; UUID bukan Bearer token dan akan menghasilkan `401 AUTHENTICATION_REQUIRED`.

Setelah authorize berhasil, request execute akan membawa header:

```text
Authorization: Bearer {token}
```

Generate ulang dokumentasi:

```bash
php artisan l5-swagger:generate
```

Jika L5-Swagger belum tersedia pada instalasi lain:

```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
```

## Endpoint MVP

| Method | Path | Tujuan |
| --- | --- | --- |
| GET | `/api/v1/master/bridges` | Daftar master jembatan dengan pagination dan filter |
| GET | `/api/v1/master/bridges/{kode_jembatan}` | Detail satu jembatan berdasarkan `m_jembatan.uniqid` |
| GET | `/api/v1/master/bridges/batch` | Batch data utama berbasis cursor `id` |
| GET | `/api/v1/master/bridges/full-batch` | Batch lengkap dengan detail relasi sebagai array |
| GET | `/api/v1/master/bridges/changed` | Sinkronisasi perubahan berdasarkan `updated_at` |
| GET | `/api/v1/master/bridges/{kode_jembatan}/profile` | Profil teknis dari `m_jembatan_profil` |
| GET | `/api/v1/master/bridges/{kode_jembatan}/spans` | Daftar bentang dari `m_jembatan_bentang` |
| GET | `/api/v1/master/bridges/geojson` | Lokasi jembatan valid dalam GeoJSON |
| GET | `/api/v1/bridges/{kode_jembatan}/condition` | Nilai kondisi atas, bawah, pelindung, dan total |
| GET | `/api/v1/bridges/{kode_jembatan}/maintenance` | Riwayat perawatan jembatan |
| POST | `/api/v1/bridges/{kode_jembatan}/maintenance` | Membuat data perawatan baru |
| GET | `/api/v1/references/provinces` | Referensi provinsi |
| GET | `/api/v1/references/cities` | Referensi kabupaten/kota |
| GET | `/api/v1/references/operation-areas` | Referensi wilayah operasi |
| GET | `/api/v1/references/work-areas` | Referensi wilayah kerja |
| GET | `/api/v1/references/routes` | Referensi lintas |
| GET | `/api/v1/references/stations` | Referensi stasiun |
| GET | `/api/v1/integration/health` | Health check Bridge API |

## Batch API

Gunakan endpoint batch untuk menarik master jembatan bertahap:

```text
GET /api/v1/master/bridges/batch?limit=500
GET /api/v1/master/bridges/batch?limit=500&cursor=500
```

`meta.next_cursor` diisi dari `id` terakhir batch saat masih ada data. Gunakan nilai itu sebagai `cursor` request berikutnya. Cursor berbasis `id` lebih stabil dibanding OFFSET untuk integrasi data besar.

## Changed API

Untuk sinkronisasi incremental:

```text
GET /api/v1/master/bridges/changed?since=2026-06-01 00:00:00&limit=500
```

Endpoint ini memfilter `m_jembatan.updated_at >= since` dan tetap memakai cursor untuk batch lanjutan.

## Full Batch

`/api/v1/master/bridges/full-batch` memakai limit default 100 karena memuat relasi detail:

- `m_jembatan_profil`
- `m_jembatan_bentang`
- `m_jembatan_baja`
- `m_jembatan_beton`
- `m_jembatan_bawah`
- `m_jembatan_detil_3`
- tabel nilai kondisi
- survey terakhir
- perawatan terakhir

Data utama diambil terlebih dahulu dari `m_jembatan`, lalu detail dimuat menggunakan `whereIn` dan dipetakan sebagai array terpisah. Pendekatan ini menghindari JOIN besar yang bisa menggandakan baris.

## Catatan

Identifier utama API adalah `kode_jembatan`, yaitu nilai `m_jembatan.uniqid`. ID internal hanya dipakai untuk cursor batch dan operasi teknis.

Endpoint read dan POST perawatan sudah tersedia. Endpoint non-MVP seperti survey CRUD lengkap, dashboard ringkas, export CSV/XLSX, media, nearby, dan integrasi full mirror sudah disiapkan sebagai perluasan berikutnya agar tetap konsisten dengan struktur service/controller yang ada.
