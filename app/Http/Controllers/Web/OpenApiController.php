<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use L5Swagger\ConfigFactory;
use L5Swagger\GeneratorFactory;

class OpenApiController extends Controller
{
    public function spec(ConfigFactory $configFactory, GeneratorFactory $generatorFactory): Response
    {
        $documentation = (string) config('l5-swagger.default', 'default');
        $config = $configFactory->documentationConfig($documentation);
        $docsDirectory = $config['paths']['docs'];
        $docsFile = $config['paths']['docs_json'] ?? 'api-docs.json';
        $filePath = $docsDirectory.'/'.$docsFile;
        $filesystem = new Filesystem();

        if (($config['generate_always'] ?? false) || ! $filesystem->exists($filePath)) {
            try {
                $generatorFactory->make($documentation)->generateDocs();
            } catch (Exception $exception) {
                if (! $filesystem->exists($filePath)) {
                    Log::error('OpenAPI generation failed and no cached spec is available.', [
                        'documentation' => $documentation,
                        'file_path' => $filePath,
                        'exception' => $exception,
                    ]);

                    abort(500, 'OpenAPI specification is temporarily unavailable.');
                }

                Log::warning('OpenAPI generation failed, serving cached spec instead.', [
                    'documentation' => $documentation,
                    'file_path' => $filePath,
                    'exception' => $exception,
                ]);
            }
        }

        abort_unless($filesystem->exists($filePath), 404, sprintf('Unable to locate documentation file at: "%s"', $filePath));

        return response($filesystem->get($filePath), 200, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function asset(string $asset): Response
    {
        $documentation = (string) config('l5-swagger.default', 'default');
        $path = swagger_ui_dist_path($documentation, $asset);
        $filesystem = new Filesystem();

        abort_unless($filesystem->exists($path), 404);

        $extension = Str::lower(pathinfo($asset, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'html' => 'text/html',
            default => 'application/octet-stream',
        };

        return (new Response(
            $filesystem->get($path),
            200,
            ['Content-Type' => $contentType]
        ))->setSharedMaxAge(31536000)
            ->setMaxAge(31536000)
            ->setExpires(new \DateTime('+1 year'));
    }
}
