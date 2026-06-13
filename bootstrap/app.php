<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnsureApiActorIsAllowed;
use App\Http\Middleware\EnsureRequestId;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RejectExpiredAccessToken;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            EnsureRequestId::class,
            RejectExpiredAccessToken::class,
            AddSecurityHeaders::class,
        ]);

        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'api.actor' => EnsureApiActorIsAllowed::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson() || $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            return ApiResponse::error(
                message: 'Data tidak valid.',
                code: 'VALIDATION_ERROR',
                status: 422,
                details: $exception->errors(),
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            return ApiResponse::error(
                message: 'Autentikasi diperlukan.',
                code: 'AUTHENTICATION_REQUIRED',
                status: 401,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            return ApiResponse::error(
                message: 'Akses ditolak.',
                code: 'ACCESS_DENIED',
                status: 403,
            );
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            return ApiResponse::error(
                message: 'Data tidak ditemukan.',
                code: 'RESOURCE_NOT_FOUND',
                status: 404,
            );
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            $status = $exception->getStatusCode();

            return ApiResponse::error(
                message: $status === 403 ? 'Akses ditolak.' : ($exception->getMessage() !== '' ? $exception->getMessage() : 'Terjadi kesalahan HTTP.'),
                code: match ($status) {
                    401 => 'AUTHENTICATION_REQUIRED',
                    403 => 'ACCESS_DENIED',
                    404 => 'RESOURCE_NOT_FOUND',
                    default => 'HTTP_ERROR',
                },
                status: $status,
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            return ApiResponse::error(
                message: 'Terjadi kesalahan internal.',
                code: 'INTERNAL_ERROR',
                status: 500,
            );
        });
    })->create();
