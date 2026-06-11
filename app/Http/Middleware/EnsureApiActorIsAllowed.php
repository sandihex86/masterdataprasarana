<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiActorIsAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();
        $token = $actor?->currentAccessToken();

        if ($token !== null && $token->expires_at !== null && $token->expires_at->isPast()) {
            return ApiResponse::error('Token kedaluwarsa.', 'TOKEN_EXPIRED', 401);
        }

        if ($actor instanceof ApiClient) {
            if (! $actor->is_active || ($actor->expires_at !== null && $actor->expires_at->isPast())) {
                return ApiResponse::error('API client tidak aktif atau sudah kedaluwarsa.', 'ACCESS_DENIED', 403);
            }

            if ($this->isIpDenied($actor, $request->ip())) {
                return ApiResponse::error('IP tidak diizinkan.', 'ACCESS_DENIED', 403);
            }

            $actor->forceFill(['last_used_at' => now()])->save();
        }

        return $next($request);
    }

    private function isIpDenied(ApiClient $client, ?string $requestIp): bool
    {
        $allowedIps = array_filter($client->allowed_ips ?? []);

        if ($allowedIps === [] || $requestIp === null) {
            return false;
        }

        return ! in_array($requestIp, $allowedIps, true);
    }
}
