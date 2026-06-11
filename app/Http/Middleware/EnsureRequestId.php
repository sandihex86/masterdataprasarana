<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerValue = $request->header('X-Request-ID');
        $requestId = $this->isValidRequestId($headerValue) ? $headerValue : (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    private function isValidRequestId(?string $requestId): bool
    {
        return is_string($requestId)
            && ($requestId !== '')
            && (Str::isUuid($requestId) || Str::isUlid($requestId));
    }
}
