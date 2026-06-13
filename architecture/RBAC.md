# RBAC Aplikasi

Dokumen ini merangkum model RBAC yang benar-benar dipakai aplikasi saat ini.

## Prinsip Dasar

Aplikasi memakai kombinasi:

- role-based access control untuk user web/internal
- ability-based access control untuk API
- policy Laravel untuk enforcement domain object

Dengan kata lain, role menentukan ability untuk `User`, sedangkan `ApiClient` memakai ability token Sanctum secara langsung.

## Aktor yang Didukung

Ada dua jenis aktor pada sistem:

### 1. User

Model: `App\Models\User`

Dipakai untuk:

- login web
- akses dashboard
- akses Swagger UI
- akses API jika user memiliki token Sanctum

### 2. ApiClient

Model: `App\Models\ApiClient`

Dipakai untuk:

- integrasi machine-to-machine via Sanctum bearer token
- pembatasan akses berbasis status aktif, expiry, IP allowlist, dan token ability

## Role User

Sumber definisi: `App\Enums\UserRole`

Role yang tersedia:

- `superadmin`
- `admin`
- `operator`
- `verifikator`
- `viewer` = `client`

## Ability User per Role

### Superadmin

- Label: `Superadmin`
- Full access: ya
- Abilities:
  - `*`

### Admin

- Label: `Admin`
- Full access: ya
- Abilities:
  - `*`

### Operator

- Label: `Operator`
- Abilities:
  - `master-data:read`
  - `master-data:write`
  - `imports:read`
  - `imports:create`

### Verifikator

- Label: `Verifikator`
- Abilities:
  - `master-data:read`
  - `imports:read`

### Viewer

- Label: `Viewer`
- Abilities:
  - `master-data:read`

## Implikasi Praktis

Karena `superadmin` dan `admin` memakai wildcard `*`, keduanya saat ini memiliki hak efektif yang sama pada level ability.

Perbedaan yang sudah benar-benar diterapkan di aplikasi saat ini:

- `superadmin` dapat melihat seluruh dashboard, dokumentasi API, JSON sistem, audit log, dan endpoint API
- `admin` dapat melihat dokumentasi API, group menu `Master Data`, `Import dan Mapping`, serta `Monitoring`
- `viewer` difokuskan ke dokumentasi API
- `operator` dapat melihat dashboard operasional terbatas dan group menu `Master Data`
- role non-admin lain saat ini mengikuti tampilan dokumentasi API pada dashboard web
- route web tertentu dibatasi oleh middleware role

Untuk menu `Master Data`, `admin`, `superadmin`, dan `operator` saat ini mengakses submenu:

- `Jembatan`
- `Terowongan`
- `Jalur`
- `Fasilitas Operasional`
- `Sertifikat`
- `Gudang`

Masing-masing submenu memakai halaman route terpisah dan grid interaktif untuk baca, tambah, lihat detail, dan edit data.

Jika nanti dibutuhkan perbedaan hak nyata antara `superadmin` dan `admin`, implementasi ability perlu dipisah.

## Enforcement Layer

RBAC di aplikasi ini tidak bergantung pada satu lapisan saja. Ia ditegakkan di beberapa titik.

### 1. Pada Model User

`App\Models\User` menyediakan:

- `resolveRole()`
- `hasRole()`
- `hasAbility()`
- `isAdministrator()`

Saat model disimpan:

- `role` dinormalisasi ke enum value
- `is_admin` diisi otomatis dari `grantsFullAccess()`

Artinya `is_admin` adalah turunan dari role, bukan sumber kebenaran utama.

### 2. Pada Enum UserRole

`App\Enums\UserRole` memegang:

- label role
- deskripsi role
- daftar ability per role
- evaluasi wildcard/full access

Ini adalah pusat definisi role untuk user.

### 3. Pada Middleware Web

`EnsureUserHasRole` dipakai oleh alias middleware `role`.

Contoh implementasi saat ini:

- `/dashboard/system` hanya bisa diakses `superadmin`

Ini adalah kontrol akses berbasis role murni, bukan ability.

### 4. Pada Middleware API Ability

Route API memakai middleware Sanctum:

- `abilities:master-data:read`
- `abilities:master-data:write`
- `abilities:master-data:delete`
- `abilities:imports:read`
- `abilities:imports:create`

Lapisan ini menjadi gerbang awal sebelum controller dieksekusi.

### 5. Pada Middleware `api.actor`

`EnsureApiActorIsAllowed` menambahkan kontrol non-RBAC yang tetap berpengaruh pada akses:

- token tidak boleh expired
- `ApiClient` harus aktif
- `ApiClient` tidak boleh expired
- IP request harus sesuai allowlist jika diatur

Jadi memiliki ability saja belum cukup; aktor juga harus lolos validasi keamanan operasional.

### 6. Pada Policy

Policy utama:

- `MasterDataPolicy`
- `MasterDataTypePolicy`
- `ImportMappingPolicy`

Semua policy mendukung aktor `User|ApiClient`.

Perilaku:

- jika aktor `User`, keputusan memakai `User::hasAbility()`
- jika aktor `ApiClient`, keputusan memakai `currentAccessToken()->can(...)`

Ini penting karena otorisasi domain pada API tidak eksklusif untuk user internal.

## Matrix Akses Endpoint API

### Health

Endpoint:

- `GET /api/v1/health`
- `GET /api/v1/health/live`
- `GET /api/v1/health/ready`

Akses:

- publik

### Master Data

Endpoint:

- `GET /api/v1/master-data`
- `GET /api/v1/master-data/{uuid}`
- `GET /api/v1/master-data-types`
- `GET /api/v1/master-data-types/{code}`
- `GET /api/v1/master-data-types/{code}/records`
- `GET /api/v1/master-data-types/{code}/records/{recordCode}`

Ability minimum:

- `master-data:read`

Endpoint tulis:

- `POST /api/v1/master-data`
- `PUT/PATCH /api/v1/master-data/{uuid}`

Ability minimum:

- `master-data:write`

Endpoint hapus/pulih:

- `DELETE /api/v1/master-data/{uuid}`
- `POST /api/v1/master-data/{uuid}/restore`

Ability minimum:

- `master-data:delete`

Catatan:

- Saat ini tidak ada role user bawaan selain admin/superadmin yang memiliki `master-data:delete`.

### Import Mapping

Endpoint baca:

- `GET /api/v1/import-mappings`
- `GET /api/v1/import-mappings/{uuid}`

Ability minimum:

- `imports:read`

Endpoint tulis:

- `POST /api/v1/import-mappings`
- `PUT /api/v1/import-mappings/{uuid}`
- `POST /api/v1/import-mappings/preview`

Ability minimum:

- `imports:create`

## Akses Web Internal

### Login

- guest only

### Dashboard

- `viewer`: dokumentasi API dan OpenAPI spec
- `operator`: dashboard operasional terbatas dan seluruh submenu `Master Data`
- `admin`: dashboard operasional, dokumentasi API, monitoring, import dan mapping, serta seluruh submenu `Master Data`
- `superadmin`: seluruh akses `admin` ditambah JSON sistem, audit log, dan endpoint internal khusus

### Dashboard utama

- butuh autentikasi web

### Swagger docs

- butuh autentikasi web

### Dashboard system JSON

- butuh autentikasi web
- role harus `superadmin` atau `admin`

## Relasi Role dan Token Sanctum

Ada dua pola penggunaan token:

### User token

Jika token milik `User`, ability efektif harus selaras dengan role user. Policy tetap membaca `User::hasAbility()`.

### ApiClient token

Jika token milik `ApiClient`, policy membaca ability token secara langsung.

Konsekuensinya:

- user internal dikontrol terutama oleh role enum
- integrasi eksternal dikontrol oleh token ability + status client

## Catatan Desain

Model saat ini lebih tepat disebut hybrid RBAC + ABAC ringan:

- RBAC untuk user internal
- token ability untuk API
- constraint tambahan pada `ApiClient` seperti IP allowlist dan expiry

## Risiko dan Hal yang Perlu Diingat

- `admin` dan `superadmin` saat ini setara secara ability.
- `master-data:delete` belum diberikan ke role non-admin di enum.
- Jika menambah endpoint API baru, route middleware dan policy harus diperbarui bersama.
- Jika menambah role baru, `UserRole` adalah titik utama yang harus diubah.
- Jika ingin permission granular dari database, desain sekarang perlu dievolusikan karena masih hard-coded di enum.
