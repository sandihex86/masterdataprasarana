# Arsitektur Aplikasi

Dokumen ini merangkum arsitektur aktual aplikasi berdasarkan implementasi pada repo ini.

## Ringkasan

Aplikasi ini adalah backend Laravel untuk pengelolaan master data prasarana, preview dan penyimpanan import mapping, dashboard operasional internal, serta dokumentasi OpenAPI/Swagger yang diproteksi login web.

Karakter utama:

- Framework: Laravel 12 style bootstrap (`bootstrap/app.php`)
- Interface: web internal berbasis Blade untuk login, dashboard, dan docs
- API: REST `api/v1/*`
- Auth web: session auth
- Auth API: Sanctum bearer token
- Sumber data: database utama aplikasi dan koneksi database legacy
- Dokumentasi API: OpenAPI attributes + Swagger UI kustom

## Lapisan Sistem

### 1. Presentation Layer

Komponen utama:

- Web routes di `routes/web.php`
- API routes di `routes/api.php`
- Blade views di `resources/views/*`
- Swagger UI kustom di `App\Http\Controllers\Web\ApiDocsController`

Flow web saat ini:

1. Guest diarahkan ke `/login`
2. Login web diproses oleh `AuthenticatedSessionController`
3. User terautentikasi masuk ke `/dashboard`
4. Swagger docs juga berada di belakang auth web

Flow API saat ini:

1. Request masuk ke `api/v1/*`
2. Middleware global menambahkan `X-Request-ID`, security headers, dan cek token expired
3. Endpoint terlindungi memakai `auth:sanctum`
4. Middleware `api.actor` memvalidasi aktor API
5. Middleware ability memastikan token/aktor punya izin per endpoint
6. Controller memanggil policy dan service domain

### 2. Application Layer

Controller bertugas tipis:

- `AuthenticatedSessionController`: login/logout web + verifikasi reCAPTCHA
- `DashboardController`: menyajikan dashboard dan snapshot sistem
- `MasterDataController`: CRUD master data
- `MasterDataTypeController`: baca tipe master data dan record per tipe
- `ImportMappingController`: CRUD mapping dan preview transformasi
- `HealthController`: live, ready, summary

Controller API umumnya:

- validasi request via Form Request
- authorize via policy
- delegasi proses bisnis ke service
- serialize output via resource dan `ApiResponse`

### 3. Domain / Service Layer

Service utama yang membentuk inti aplikasi:

- `DashboardService`
  Menghasilkan overview dashboard, metrics, health checks, daftar route API, role matrix, dan ringkasan modul.

- `MasterDataQueryService`
  Menangani filter, search, sorting, dan pagination record master data.

- `MasterDataWriteService`
  Menangani create, update, soft delete, restore, checksum, version bump, dan audit log.

- `MasterDataValidationService`
  Menjalankan validasi payload master data, termasuk rule bawaan dan rule dari tipe data.

- `MasterDataChecksumService`
  Membuat checksum payload untuk mendeteksi perubahan data yang bermakna.

- `MappingService`
  Memvalidasi konfigurasi mapping, membaca kolom legacy, memetakan baris source ke payload master data, preview hasil transformasi, dan persist mapping.

- `TransformationRegistry`
  Registry transformasi yang diizinkan pada import mapping.

- `LegacyDatabaseService`
  Adapter untuk membaca metadata dan sample row dari database legacy.

- `AuditService`
  Mencatat jejak perubahan create/update/delete/restore.

## Data Layer

Model utama:

- `User`
- `ApiClient`
- `PersonalAccessToken`
- `MasterDataType`
- `MasterData`
- `ImportMapping`
- `ImportBatch`
- `ImportError`
- `AuditLog`
- `ApiRequestLog`

### Karakter data penting

- Banyak model memakai `uuid` publik sebagai identifier eksternal.
- `MasterData` mendukung soft delete.
- `MasterDataType` menyimpan aturan validasi, field yang dapat dicari, field yang terlihat, dan konfigurasi mapping.
- `ImportMapping` menyimpan definisi transformasi source legacy menjadi payload master data.
- `ApiClient` berfungsi sebagai aktor API non-user dengan token Sanctum.

## Autentikasi dan Otorisasi

### Web

- Session-based auth
- Login form memakai email + password
- Login kini mendukung Google reCAPTCHA v2 checkbox
- Route dashboard sistem dibatasi role `superadmin` dan `admin`

### API

- Sanctum bearer token
- Aktor API bisa berupa `User` atau `ApiClient`
- Authorization dijalankan berlapis:
  - middleware route ability
  - policy Laravel
  - helper `UserRole::allowsAbility()`

Dokumen detail ada di `architecture/RBAC.md`.

## Middleware dan Cross-Cutting Concerns

Middleware global di `bootstrap/app.php`:

- `EnsureRequestId`
  Membuat atau meneruskan `X-Request-ID`.

- `RejectExpiredAccessToken`
  Menolak request API yang memakai bearer token kadaluwarsa.

- `AddSecurityHeaders`
  Menambahkan header keamanan dasar.

Alias middleware:

- `abilities`
- `ability`
- `api.actor`
- `role`

`EnsureApiActorIsAllowed` melakukan:

- cek expiry token
- cek status aktif `ApiClient`
- cek expiry `ApiClient`
- cek allowlist IP
- update `last_used_at`

## Error Handling

`bootstrap/app.php` mengubah exception API menjadi format JSON seragam melalui `ApiResponse`.

Mapping utama:

- `ValidationException` -> `422 VALIDATION_ERROR`
- `AuthenticationException` -> `401 AUTHENTICATION_REQUIRED`
- `AuthorizationException` -> `403 ACCESS_DENIED`
- `ModelNotFoundException` / `NotFoundHttpException` -> `404 RESOURCE_NOT_FOUND`
- Exception lain -> `500 INTERNAL_ERROR`

Respons API juga membawa:

- `meta.request_id`
- `meta.timestamp`

## Integrasi Legacy

Database legacy dipakai untuk:

- inspeksi struktur tabel
- validasi source table/column
- sampling row untuk preview mapping

Layer integrasi ini dipusatkan di `LegacyDatabaseService`, sehingga controller dan service domain tidak berinteraksi langsung dengan query mentah legacy.

## Dokumentasi API

OpenAPI dibangun dari attribute PHP di controller dan schema class:

- `app/Http/Controllers/Api/V1/*`
- `app/OpenApi/*`

Swagger UI kustom:

- route: `/docs/swagger`
- JSON spec: route `l5-swagger.default.docs`

Swagger saat ini berada di belakang autentikasi web.

## Dashboard Internal

Dashboard internal bukan sekadar tampilan statis. `DashboardService` menyusun data dari:

- health check database utama
- health check database legacy
- storage/cache/queue/session/public storage
- count model inti
- daftar role dan jumlah user
- daftar route API aktif
- ringkasan recent data/mapping/import/client/audit/request

Dengan begitu dashboard berfungsi sebagai ringkasan operasional dan observability ringan.

## Konfigurasi Penting

Konfigurasi aplikasi yang paling berpengaruh:

- `config/master-data.php`
  pagination, import preview limit, cache, retention, swagger, validasi, transformasi mapping

- `config/services.php`
  third-party services termasuk Google reCAPTCHA

- `config/auth.php`, `config/sanctum.php`, `config/session.php`
  auth/session/token behavior

- `config/database.php`
  database utama dan legacy connection

## Catatan Implementasi

- Login reCAPTCHA saat ini menggunakan widget resmi Google reCAPTCHA v2 checkbox.
- Verifikasi token dilakukan server-side via endpoint `siteverify`.
- Implementasi reCAPTCHA dilakukan native memakai HTTP client Laravel, bukan package eksternal.
- Seeder tidak lagi membuat akun dummy login.

## Batasan Saat Ini

- UI web internal masih berfokus pada observability dan akses operasional, belum menjadi CRUD admin panel penuh untuk semua entitas.
- Import preview sudah ada, tetapi pipeline eksekusi import batch penuh belum tampak lengkap dari route web internal.
- RBAC user berbasis enum role dan ability sederhana; belum ada permission matrix dinamis dari database.
