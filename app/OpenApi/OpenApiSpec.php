<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Master Data API',
    description: 'Dokumentasi API untuk Master Data Jembatan, Profil Teknis, Survey, Kondisi, Perawatan, Referensi, dan Integrasi aplikasi lain. Endpoint yang memakai ikon gembok membutuhkan Bearer token Sanctum berupa plain_text_token hasil generate dari Dashboard Superadmin, biasanya berformat id|token seperti 12|xxxxxxxx. UUID client API bukan Bearer token dan akan menghasilkan 401.'
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
    description: 'Klik Authorize di Swagger UI, lalu masukkan plain_text_token Sanctum hasil generate dari Dashboard Superadmin tanpa prefix Bearer. Format token biasanya id|token, contoh: 12|abcdef.... Jangan isi UUID client API seperti 019e... karena itu bukan Bearer token.'
)]
#[OA\Tag(name: 'Health', description: 'Status aplikasi dan dependensi inti.')]
#[OA\Tag(name: 'Bridges', description: 'API untuk Master Data Jembatan, detail teknis, batch data, lokasi, survey, kondisi, perawatan, dan integrasi aplikasi lain.')]
#[OA\Tag(name: 'Import Mappings', description: 'Konfigurasi mapping dan preview transformasi import.')]
final class OpenApiSpec
{
}
