<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditAction;
use App\Models\ApiClient;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ApiClientManagementService
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 100);
        $search = trim((string) ($filters['search'] ?? ''));

        $paginator = ApiClient::query()
            ->withCount('accessTokens')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('owner_name', 'like', '%'.$search.'%')
                        ->orWhere('owner_email', 'like', '%'.$search.'%')
                        ->orWhere('uuid', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (ApiClient $client): array => $this->summary($client)),
        );

        return $paginator;
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(ApiClient $apiClient): array
    {
        $client = $apiClient->load([
            'creator:id,name,email',
            'updater:id,name,email',
            'accessTokens' => fn ($query) => $query->latest('created_at')->limit(8),
        ]);

        return [
            ...$this->summary($client),
            'description' => $client->description,
            'owner_name' => $client->owner_name,
            'owner_email' => $client->owner_email,
            'allowed_ips' => $client->allowed_ips ?? [],
            'allowed_origins' => $client->allowed_origins ?? [],
            'rate_limit_per_minute' => $client->rate_limit_per_minute,
            'rate_limit_per_day' => $client->rate_limit_per_day,
            'expires_at' => $client->expires_at,
            'last_used_at' => $client->last_used_at,
            'created_by_user' => $client->creator?->only(['name', 'email']),
            'updated_by_user' => $client->updater?->only(['name', 'email']),
            'recent_tokens' => $client->accessTokens->map(fn (PersonalAccessToken $token): array => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities ?? [],
                'last_used_at' => $token->last_used_at,
                'expires_at' => $token->expires_at,
                'created_at' => $token->created_at,
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload, User $actor): array
    {
        $client = ApiClient::query()->create([
            ...$this->normalizePayload($payload),
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $this->auditService->record(AuditAction::Create, $client, [], $this->detail($client));

        return $this->detail($client);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(ApiClient $apiClient, array $payload, User $actor): array
    {
        $before = $this->detail($apiClient);

        $apiClient->fill([
            ...$this->normalizePayload($payload, $apiClient),
            'updated_by' => $actor->id,
        ]);
        $apiClient->save();

        $fresh = $apiClient->fresh();
        $after = $this->detail($fresh);
        [$oldValues, $newValues] = $this->auditService->diff($before, $after);
        $this->auditService->record(AuditAction::Update, $fresh, $oldValues, $newValues);

        return $after;
    }

    public function delete(ApiClient $apiClient): void
    {
        $before = $this->detail($apiClient);
        $apiClient->accessTokens()->delete();
        $apiClient->delete();

        $this->auditService->record(AuditAction::Delete, $apiClient, $before, []);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function generateToken(ApiClient $apiClient, array $payload): array
    {
        if (! $apiClient->is_active) {
            throw ValidationException::withMessages([
                'api_client' => ['Client API dalam status nonaktif, token baru tidak bisa dibuat.'],
            ]);
        }

        $abilities = array_values(array_unique(array_map('strval', $payload['abilities'] ?? [])));
        $expiresAt = filled($payload['expires_at'] ?? null) ? Carbon::parse((string) $payload['expires_at']) : null;
        $newToken = $apiClient->createToken((string) $payload['token_name'], $abilities, $expiresAt);
        $token = $newToken->accessToken;
        $token->forceFill([
            'api_client_id' => $apiClient->id,
        ])->save();

        $this->auditService->record(
            AuditAction::TokenCreate,
            $apiClient,
            [],
            [
                'token_name' => $token->name,
                'abilities' => $abilities,
                'expires_at' => $token->expires_at,
            ],
        );

        return [
            'plain_text_token' => $newToken->plainTextToken,
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities ?? [],
                'expires_at' => $token->expires_at,
                'created_at' => $token->created_at,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function abilityOptions(): array
    {
        return [
            ['value' => '*', 'label' => 'Full Access', 'description' => 'Semua endpoint API aktif untuk client ini.'],
            ['value' => 'master-data:read', 'label' => 'Master Data Read', 'description' => 'Boleh membaca endpoint master data.'],
            ['value' => 'master-data:write', 'label' => 'Master Data Write', 'description' => 'Boleh menulis dan memperbarui master data.'],
            ['value' => 'imports:read', 'label' => 'Import Read', 'description' => 'Boleh melihat mapping dan batch import.'],
            ['value' => 'imports:create', 'label' => 'Import Create', 'description' => 'Boleh membuat preview dan menjalankan import.'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(ApiClient $apiClient): array
    {
        return [
            'uuid' => $apiClient->uuid,
            'name' => $apiClient->name,
            'code' => $apiClient->code,
            'owner_name' => $apiClient->owner_name,
            'owner_email' => $apiClient->owner_email,
            'is_active' => $apiClient->is_active,
            'access_tokens_count' => (int) ($apiClient->access_tokens_count ?? $apiClient->accessTokens()->count()),
            'expires_at' => $apiClient->expires_at,
            'last_used_at' => $apiClient->last_used_at,
            'updated_at' => $apiClient->updated_at,
            'created_at' => $apiClient->created_at,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload, ?ApiClient $apiClient = null): array
    {
        return [
            'name' => array_key_exists('name', $payload) ? trim((string) $payload['name']) : $apiClient?->name,
            'code' => array_key_exists('code', $payload) ? trim((string) $payload['code']) : $apiClient?->code,
            'description' => array_key_exists('description', $payload) ? $this->nullableString($payload['description']) : $apiClient?->description,
            'owner_name' => array_key_exists('owner_name', $payload) ? $this->nullableString($payload['owner_name']) : $apiClient?->owner_name,
            'owner_email' => array_key_exists('owner_email', $payload) ? $this->nullableString($payload['owner_email'], true) : $apiClient?->owner_email,
            'allowed_ips' => array_key_exists('allowed_ips', $payload) ? $this->normalizeStringArray($payload['allowed_ips']) : $apiClient?->allowed_ips,
            'allowed_origins' => array_key_exists('allowed_origins', $payload) ? $this->normalizeStringArray($payload['allowed_origins']) : $apiClient?->allowed_origins,
            'rate_limit_per_minute' => array_key_exists('rate_limit_per_minute', $payload) ? ($payload['rate_limit_per_minute'] ?: null) : $apiClient?->rate_limit_per_minute,
            'rate_limit_per_day' => array_key_exists('rate_limit_per_day', $payload) ? ($payload['rate_limit_per_day'] ?: null) : $apiClient?->rate_limit_per_day,
            'expires_at' => array_key_exists('expires_at', $payload) && filled($payload['expires_at']) ? Carbon::parse((string) $payload['expires_at']) : (array_key_exists('expires_at', $payload) ? null : $apiClient?->expires_at),
            'is_active' => array_key_exists('is_active', $payload) ? (bool) $payload['is_active'] : ($apiClient?->is_active ?? true),
        ];
    }

    /**
     * @param  array<int, mixed>|null  $values
     * @return array<int, string>
     */
    private function normalizeStringArray(?array $values): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            $values ?? [],
        ))));
    }

    private function nullableString(mixed $value, bool $lowercase = false): ?string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return $lowercase ? mb_strtolower($normalized) : $normalized;
    }
}
