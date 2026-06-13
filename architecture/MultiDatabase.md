# Multi-Database Bounded Context

Dokumen ini menjelaskan target arsitektur enterprise untuk aplikasi prasarana dengan pendekatan **modular monolith + multi-database bounded context**.

## Tujuan

- memisahkan ownership data per domain
- menghindari satu tabel generik menjadi pusat semua transaksi
- memudahkan scale-up per modul tanpa merusak modul lain
- menyiapkan integrasi lintas infrastruktur dengan pola yang konsisten

## Topologi Database

### 1. `prasarana_core`

Isi utama:

- users
- roles / permission
- api_clients
- audit_logs
- api_request_logs
- import_batches
- import_errors
- workflow approval
- konfigurasi dashboard internal

### 2. `prasarana_reference`

Isi utama:

- provinsi
- kabupaten / kota
- wilayah kerja
- wilayah operasi
- lintas
- stasiun
- taxonomy dan kode referensi lintas domain

### 3. `prasarana_bridge`

Isi utama:

- seluruh tabel source `m_jembatan*`
- survey, detail, penilaian, review, perawatan

### 4. `prasarana_tunnel`

Isi utama:

- source operasional Terowongan
- histori inspeksi terowongan
- detail aset terowongan

### 5. `prasarana_track`

Isi utama:

- source operasional Jalur
- histori inspeksi jalur
- detail aset jalur

### 6. `prasarana_operational_facility`

Isi utama:

- source Fasilitas Operasional
- detail aset fasilitas
- histori pemeriksaan / penilaian

### 6. `prasarana_certificate`

Isi utama:

- sumber data Sertifikat
- lifecycle dokumen
- status validitas dan histori approval

### 7. `prasarana_warehouse`

Isi utama:

- stok gudang
- item master gudang
- mutasi dan histori transaksi

### 8. `prasarana_reporting`

Isi utama:

- projection table
- summary dashboard
- agregasi lintas domain

## Bounded Context

### Core

- tidak menyimpan transaksi domain jembatan/jalur/fasilitas/dll
- fokus ke platform capability

### Reference

- hanya data referensi bersama
- tidak menjadi tempat record transaksi domain

### Domain Infrastruktur

Domain yang saat ini disiapkan:

- Jembatan
- Terowongan
- Jalur
- Fasilitas Operasional
- Sertifikat
- Gudang

Aturan:

- satu domain = satu database operasional utama
- write hanya ke database domain sendiri
- read lintas domain sebaiknya lewat service/projection

## Integrasi Antar Domain

Pola yang disarankan:

1. synchronous read melalui service layer jika kebutuhan kecil
2. asynchronous sync via queue/event untuk propagasi perubahan
3. projection ke `reporting` untuk kebutuhan dashboard dan pencarian cepat

Pola yang tidak disarankan:

- join liar antar database langsung dari controller/view
- menyalin semua data domain ke `master_data`
- edit record yang sama dari dua domain berbeda

## Posisi `master_data`

`master_data` sebaiknya diposisikan sebagai salah satu dari dua hal berikut:

- canonical record untuk subset data yang memang perlu dipublikasikan lintas domain, atau
- transitional layer selama migrasi dari source lama ke bounded context baru

`master_data` tidak disarankan menjadi pengganti seluruh schema domain.

## Strategi Implementasi Bertahap

1. Tetapkan naming koneksi eksplisit: `bridge`, `tunnel`, `track`, `operational_facility`, `certificate`, `warehouse`
2. Pertahankan alias `legacy` hanya untuk kompatibilitas sementara
3. Pisahkan `core` dan `reference`
4. Pindahkan setiap modul ke source of truth database domain masing-masing
5. Tambahkan `reporting` bila kebutuhan dashboard lintas domain mulai berat

## Konvensi Kode

Di level Laravel, setiap domain sebaiknya punya:

- model sendiri
- controller sendiri
- request validation sendiri
- service/repository sendiri
- policy sendiri
- migration pada database connection domain

Dengan begitu, codebase tetap satu aplikasi tetapi ownership domain tetap tegas.

Fondasi yang sudah disiapkan di repo ini:

- `app/Modules/Core/*`
- `app/Modules/Reference/*`
- `app/Modules/Bridge/*`
- `app/Modules/Track/*`
- `app/Modules/OperationalFacility/*`
- `app/Modules/Certificate/*`
- `app/Modules/Warehouse/*`
- `app/Modules/Reporting/*`

Base model per domain sudah diarahkan ke connection masing-masing sehingga model baru tidak perlu mengulang deklarasi koneksi.

## Bootstrap Schema

Untuk koneksi yang belum aktif saat migration awal dijalankan, gunakan command:

```bash
php artisan infrastructure:bootstrap
```

Command ini idempotent dan aman dijalankan ulang untuk:

- `reference`
- `track`
- `operational_facility`
- `certificate`
- `warehouse`
- `reporting`

Sementara itu:

- schema `bridge` tetap dikelola oleh migration source bridge
- schema `core` tetap dikelola oleh migration aplikasi inti
