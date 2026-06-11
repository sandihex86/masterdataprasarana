<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectExpiredAccessToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*') || $request->bearerToken() === null) {
            return $next($request);
        }

        $token = PersonalAccessToken::findToken($request->bearerToken());

        if ($token !== null && $token->expires_at !== null && $token->expires_at->isPast()) {
            return ApiResponse::error('Token kedaluwarsa.', 'TOKEN_EXPIRED', 401);
        }

        return $next($request);
    }
}
