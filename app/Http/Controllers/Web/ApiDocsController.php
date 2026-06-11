<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ApiDocsController extends Controller
{
    public function swagger(): Response
    {
        $title = 'Master Data Prasarana DJKA API Docs';
        $subtitle = 'Dokumentasi API internal';
        $docsUrl = route('l5-swagger.default.docs');
        $cssUrl = 'https://unpkg.com/swagger-ui-dist@5/swagger-ui.css';
        $bundleUrl = 'https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js';
        $presetUrl = 'https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js';
        $favicon32 = 'https://unpkg.com/swagger-ui-dist@5/favicon-32x32.png';
        $favicon16 = 'https://unpkg.com/swagger-ui-dist@5/favicon-16x16.png';
        $csrfToken = csrf_token();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title}</title>
    <link rel="stylesheet" href="{$cssUrl}">
    <link rel="icon" type="image/png" href="{$favicon32}" sizes="32x32">
    <link rel="icon" type="image/png" href="{$favicon16}" sizes="16x16">
    <style>
        html { box-sizing: border-box; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body {
            margin: 0;
            background: linear-gradient(180deg, #faf7f1 0%, #f0eadf 100%);
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: #172033;
        }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 22px;
            background: rgba(10, 12, 16, 0.96);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(18px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .brand-mark img {
            width: 28px;
            height: 28px;
            display: block;
        }
        .brand-mark span {
            color: #7ee787;
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .brand-copy {
            min-width: 0;
        }
        .brand-copy strong {
            display: block;
            font-size: 1.02rem;
            color: #f8fafc;
        }
        .brand-copy span {
            color: rgba(226, 232, 240, 0.82);
            font-size: 0.9rem;
        }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.06);
            color: #f8fafc;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 600;
        }
        .button.primary {
            background: linear-gradient(135deg, #7ee787, #2ea043);
            border-color: transparent;
            color: #08120b;
        }
        #swagger-ui { max-width: 1280px; margin: 0 auto; padding: 18px 12px 36px; }
        .swagger-ui .topbar { display: none; }
        .swagger-ui .scheme-container { display: none; }
        @media (max-width: 720px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">
            <div class="brand-mark">
                <img src="{$favicon32}" alt="Swagger logo">
                <span>Swagger</span>
            </div>
            <div class="brand-copy">
                <strong>{$title}</strong>
                <span>{$subtitle}</span>
            </div>
        </div>
        <div class="actions">
            <a class="button" href="/dashboard">Dashboard</a>
            <a class="button primary" href="{$docsUrl}" target="_blank" rel="noreferrer">OpenAPI Spec</a>
        </div>
    </div>
    <div id="swagger-ui"></div>
    <script src="{$bundleUrl}"></script>
    <script src="{$presetUrl}"></script>
    <script>
        window.onload = function () {
            window.ui = SwaggerUIBundle({
                url: "{$docsUrl}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: 'BaseLayout',
                docExpansion: 'none',
                filter: true,
                persistAuthorization: true,
                requestInterceptor: function(request) {
                    request.headers['X-CSRF-TOKEN'] = '{$csrfToken}';
                    return request;
                }
            });
        };
    </script>
</body>
</html>
HTML;

        return response($html);
    }
}
