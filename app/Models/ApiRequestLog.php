<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'api_client_id',
        'user_id',
        'method',
        'endpoint',
        'route_name',
        'query_parameters',
        'request_size',
        'status_code',
        'response_time_ms',
        'response_size',
        'ip_address',
        'user_agent',
        'error_code',
        'requested_at',
    ];

    protected function casts(): array
    {
        return [
            'query_parameters' => 'array',
            'requested_at' => 'datetime',
        ];
    }

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
