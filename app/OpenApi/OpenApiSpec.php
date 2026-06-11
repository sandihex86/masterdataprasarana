<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Master Data Prasarana DJKA API'
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
#[OA\Tag(name: 'Master Data', description: 'Operasi CRUD dan query master data.')]
#[OA\Tag(name: 'Master Data Types', description: 'Katalog tipe entitas dan record by type.')]
#[OA\Tag(name: 'Import Mappings', description: 'Konfigurasi mapping dan preview transformasi import.')]
final class OpenApiSpec
{
}
