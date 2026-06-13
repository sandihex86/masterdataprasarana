<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Prasarana API'
)]
#[OA\Server(
    url: 'https://prasarana.labdata.id',
    description: 'Production'
)]
#[OA\Server(
    url: 'http://localhost',
    description: 'Local development'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctumBearer',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Masukkan bearer token Sanctum.'
)]
#[OA\Tag(name: 'Health', description: 'Status aplikasi dan dependensi inti.')]
#[OA\Tag(name: 'Bridge Source', description: 'API v1 profesional untuk modul jembatan yang langsung membaca dan menulis ke tabel source `m_jembatan` beserta relasi struktur dan lookup terkait.')]
#[OA\Tag(name: 'Bridge Source Tables', description: 'CRUD lengkap dan endpoint schema untuk tabel source/lookup modul jembatan seperti `m_kabkot`, `m_stasiun`, `m_wilayah_kerja`, dan lainnya.')]
#[OA\Tag(name: 'Bridges', description: 'Tag lama endpoint jembatan. Dipertahankan untuk kompatibilitas dokumentasi sebelumnya.')]
#[OA\Tag(name: 'Import Mappings', description: 'Konfigurasi mapping dan preview transformasi import.')]
final class OpenApiSpec
{
}
