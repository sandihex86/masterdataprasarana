<?php

namespace App\Services\Dashboard;

use App\Enums\UserRole;
use App\Models\ApiClient;
use App\Models\ApiRequestLog;
use App\Models\AuditLog;
use App\Models\ImportBatch;
use App\Models\ImportError;
use App\Models\ImportMapping;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $healthChecks = $this->healthChecks();
        $metrics = $this->metrics();

        return [
            'application' => [
                'name' => config('app.name'),
                'environment' => App::environment(),
                'debug' => (bool) config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'php_version' => PHP_VERSION,
                'laravel_version' => App::version(),
                'generated_at' => now(),
            ],
            'health' => [
                'status' => collect($healthChecks)->contains(fn (array $check): bool => ! $check['ok']) ? 'degraded' : 'ok',
                'checks' => $healthChecks,
            ],
            'metrics' => $metrics,
            'modules' => $this->modules($metrics, $healthChecks),
            'entity_types' => $this->entityTypes(),
            'recent_records' => $this->recentMasterData(),
            'recent_mappings' => $this->recentMappings(),
            'recent_imports' => $this->recentImports(),
            'recent_clients' => $this->recentClients(),
            'recent_audits' => $this->recentAudits(),
            'recent_requests' => $this->recentRequests(),
            'user_roles' => $this->userRoles(),
            'api_routes' => $this->apiRoutes(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function healthChecks(): array
    {
        return [
            $this->databaseCheck(
                label: 'Database utama',
                connectionName: config('database.default', 'mysql'),
            ),
            $this->databaseCheck(
                label: 'Database legacy',
                connectionName: 'legacy',
            ),
            [
                'label' => 'Storage aplikasi',
                'ok' => is_writable(storage_path('app')),
                'detail' => storage_path('app'),
            ],
            [
                'label' => 'Cache store',
                'ok' => $this->checkCacheStore(),
                'detail' => (string) config('cache.default'),
            ],
            [
                'label' => 'Queue driver',
                'ok' => filled(config('queue.default')),
                'detail' => (string) config('queue.default'),
            ],
            [
                'label' => 'Session driver',
                'ok' => filled(config('session.driver')),
                'detail' => (string) config('session.driver'),
            ],
            [
                'label' => 'Public storage link',
                'ok' => is_link(public_path('storage')) || is_dir(public_path('storage')),
                'detail' => public_path('storage'),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function metrics(): array
    {
        return [
            'users' => $this->safeCount(User::class),
            'master_data_types' => $this->safeCount(MasterDataType::class),
            'active_master_data_types' => $this->safeCount(MasterDataType::class, fn ($query) => $query->where('is_active', true)),
            'master_data_records' => $this->safeCount(MasterData::class),
            'active_master_data_records' => $this->safeCount(MasterData::class, fn ($query) => $query->where('status', 'active')),
            'import_mappings' => $this->safeCount(ImportMapping::class),
            'active_import_mappings' => $this->safeCount(ImportMapping::class, fn ($query) => $query->where('is_active', true)),
            'import_batches' => $this->safeCount(ImportBatch::class),
            'import_errors' => $this->safeCount(ImportError::class),
            'api_clients' => $this->safeCount(ApiClient::class),
            'active_api_clients' => $this->safeCount(ApiClient::class, fn ($query) => $query->where('is_active', true)),
            'access_tokens' => $this->safeCount(PersonalAccessToken::class),
            'audit_logs' => $this->safeCount(AuditLog::class),
            'request_logs_today' => $this->safeCount(ApiRequestLog::class, fn ($query) => $query->where('requested_at', '>=', now()->startOfDay())),
        ];
    }

    /**
     * @param  array<string, int>  $metrics
     * @param  array<int, array<string, mixed>>  $healthChecks
     * @return array<int, array<string, mixed>>
     */
    private function modules(array $metrics, array $healthChecks): array
    {
        $healthyInfrastructure = ! collect($healthChecks)->contains(fn (array $check): bool => ! $check['ok']);

        return [
            $this->moduleCard(
                label: 'Gateway API',
                checks: [
                    $healthyInfrastructure,
                    $this->apiRoutes()->isNotEmpty(),
                    $metrics['active_api_clients'] > 0,
                ],
                detail: 'Endpoint v1, health check, dan client token sudah terdeteksi.',
            ),
            $this->moduleCard(
                label: 'Master Data',
                checks: [
                    $metrics['master_data_types'] > 0,
                    $metrics['master_data_records'] >= 0,
                    $metrics['active_master_data_types'] > 0,
                ],
                detail: 'Katalog tipe data, record utama, dan struktur validasi tersedia.',
            ),
            $this->moduleCard(
                label: 'Import dan Mapping',
                checks: [
                    $this->tableExists((new ImportBatch)->getTable()),
                    $metrics['import_mappings'] > 0,
                    $this->tableExists((new ImportError)->getTable()),
                ],
                detail: 'Pipeline import, mapping, dan pencatatan error siap digunakan.',
            ),
            $this->moduleCard(
                label: 'Audit dan Monitoring',
                checks: [
                    $this->tableExists((new AuditLog)->getTable()),
                    $this->tableExists((new ApiRequestLog)->getTable()),
                    $metrics['audit_logs'] >= 0,
                ],
                detail: 'Jejak audit, log request API, dan observability backend sudah disiapkan.',
            ),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function entityTypes(): Collection
    {
        if (! $this->tableExists((new MasterDataType)->getTable()) || ! $this->tableExists((new MasterData)->getTable())) {
            return collect();
        }

        try {
            return MasterDataType::query()
                ->withCount('records')
                ->orderByDesc('records_count')
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(fn (MasterDataType $type): array => [
                    'code' => $type->code,
                    'name' => $type->name,
                    'is_active' => $type->is_active,
                    'records_count' => $type->records_count,
                    'description' => $type->description,
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recentMasterData(): Collection
    {
        if (! $this->tableExists((new MasterData)->getTable())) {
            return collect();
        }

        try {
            return MasterData::query()
                ->with('type')
                ->latest('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (MasterData $record): array => [
                    'uuid' => $record->uuid,
                    'code' => $record->code,
                    'name' => $record->name,
                    'entity_type' => $record->entity_type,
                    'type_name' => $record->type?->name,
                    'status' => $record->status?->value ?? (string) $record->status,
                    'updated_at' => $record->updated_at,
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recentMappings(): Collection
    {
        if (! $this->tableExists((new ImportMapping)->getTable())) {
            return collect();
        }

        try {
            return ImportMapping::query()
                ->latest('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (ImportMapping $mapping): array => [
                    'name' => $mapping->name,
                    'source_system' => $mapping->source_system,
                    'source_table' => $mapping->source_table,
                    'entity_type' => $mapping->entity_type,
                    'version' => $mapping->version,
                    'is_active' => $mapping->is_active,
                    'updated_at' => $mapping->updated_at,
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recentImports(): Collection
    {
        if (! $this->tableExists((new ImportBatch)->getTable())) {
            return collect();
        }

        try {
            return ImportBatch::query()
                ->latest('created_at')
                ->limit(6)
                ->get()
                ->map(fn (ImportBatch $batch): array => [
                    'uuid' => $batch->uuid,
                    'source_system' => $batch->source_system,
                    'source_table' => $batch->source_table,
                    'entity_type' => $batch->entity_type,
                    'status' => $batch->status?->value ?? (string) $batch->status,
                    'progress_percentage' => $batch->progress_percentage,
                    'processed_rows' => $batch->processed_rows,
                    'total_rows' => $batch->total_rows,
                    'created_at' => $batch->created_at,
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recentClients(): Collection
    {
        if (! $this->tableExists((new ApiClient)->getTable())) {
            return collect();
        }

        try {
            return ApiClient::query()
                ->latest('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (ApiClient $client): array => [
                    'name' => $client->name,
                    'code' => $client->code,
                    'owner_email' => $client->owner_email,
                    'is_active' => $client->is_active,
                    'last_used_at' => $client->last_used_at,
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recentAudits(): Collection
    {
        if (! $this->tableExists((new AuditLog)->getTable())) {
            return collect();
        }

        try {
            return AuditLog::query()
                ->latest('created_at')
                ->limit(6)
                ->get()
                ->map(fn (AuditLog $log): array => [
                    'action' => $log->action?->value ?? (string) $log->action,
                    'auditable_type' => class_basename($log->auditable_type),
                    'auditable_id' => $log->auditable_id,
                    'request_id' => $log->request_id,
                    'created_at' => $log->created_at,
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recentRequests(): Collection
    {
        if (! $this->tableExists((new ApiRequestLog)->getTable())) {
            return collect();
        }

        try {
            return ApiRequestLog::query()
                ->latest('requested_at')
                ->limit(6)
                ->get()
                ->map(fn (ApiRequestLog $log): array => [
                    'method' => $log->method,
                    'endpoint' => $log->endpoint,
                    'status_code' => $log->status_code,
                    'response_time_ms' => $log->response_time_ms,
                    'requested_at' => $log->requested_at,
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function apiRoutes(): Collection
    {
        return collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'api/v1'))
            ->map(fn (Route $route): array => [
                'uri' => '/'.$route->uri(),
                'methods' => collect($route->methods())
                    ->reject(fn (string $method): bool => in_array($method, ['HEAD'], true))
                    ->values()
                    ->all(),
                'name' => $route->getName(),
            ])
            ->sortBy('uri')
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function userRoles(): Collection
    {
        if (! $this->tableExists((new User)->getTable())) {
            return collect();
        }

        try {
            $counts = User::query()
                ->select('role', DB::raw('count(*) as aggregate'))
                ->groupBy('role')
                ->pluck('aggregate', 'role');

            return collect(UserRole::cases())
                ->map(fn (UserRole $role): array => [
                    'code' => $role->value,
                    'label' => $role->label(),
                    'description' => $role->description(),
                    'count' => (int) ($counts[$role->value] ?? 0),
                ]);
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function safeCount(string $modelClass, ?callable $callback = null): int
    {
        /** @var Model $model */
        $model = new $modelClass;

        if (! $this->tableExists($model->getTable(), $model->getConnectionName())) {
            return 0;
        }

        try {
            $query = $modelClass::query();

            if ($callback !== null) {
                $callback($query);
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function tableExists(string $table, ?string $connection = null): bool
    {
        try {
            return Schema::connection($connection)->hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function databaseCheck(string $label, string $connectionName): array
    {
        try {
            /** @var Connection $connection */
            $connection = DB::connection($connectionName);
            $connection->getPdo();

            return [
                'label' => $label,
                'ok' => true,
                'detail' => sprintf(
                    '%s:%s/%s',
                    $connection->getConfig('host') ?? 'localhost',
                    $connection->getConfig('port') ?? '-',
                    $connection->getDatabaseName()
                ),
            ];
        } catch (Throwable $exception) {
            return [
                'label' => $label,
                'ok' => false,
                'detail' => $exception->getMessage(),
            ];
        }
    }

    private function checkCacheStore(): bool
    {
        try {
            $key = 'dashboard:healthcheck';
            Cache::put($key, true, now()->addMinute());

            return (bool) Cache::get($key);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<int, bool>  $checks
     * @return array<string, mixed>
     */
    private function moduleCard(string $label, array $checks, string $detail): array
    {
        $completed = collect($checks)->filter()->count();
        $total = count($checks);

        return [
            'label' => $label,
            'detail' => $detail,
            'completed' => $completed,
            'total' => $total,
            'percentage' => $total === 0 ? 0 : (int) round(($completed / $total) * 100),
            'status' => $completed === $total ? 'ready' : ($completed > 0 ? 'partial' : 'missing'),
        ];
    }
}
