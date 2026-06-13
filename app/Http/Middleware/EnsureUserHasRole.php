<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Access\AuthorizationException;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ($roles !== [] && ! $user->hasRole(...$roles))) {
            throw new AuthorizationException('Akses ditolak.');
        }

        return $next($request);
    }
}
