<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(string $message, mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => self::meta($meta),
        ], $status);
    }

    public static function paginated(string $message, array $data, LengthAwarePaginator $paginator, array $meta = []): JsonResponse
    {
        return self::success($message, $data, array_merge($meta, [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]));
    }

    public static function error(string $message, string $code, int $status, array $details = [], array $meta = []): JsonResponse
    {
        $error = [
            'code' => $code,
            'details' => (object) $details,
        ];

        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error,
            'errors' => $error,
            'meta' => self::meta($meta),
        ], $status);
    }

    private static function meta(array $meta = []): array
    {
        $request = request();

        return array_merge([
            'request_id' => $request?->attributes->get('request_id'),
            'timestamp' => now()->toIso8601String(),
        ], $meta);
    }
}
