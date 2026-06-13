<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 100);
        $search = trim((string) ($filters['search'] ?? ''));

        $paginator = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('role', 'like', '%'.$search.'%')
                        ->orWhere('uuid', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (User $user): array => $this->summary($user)),
        );

        return $paginator;
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(User $user): array
    {
        $role = $user->resolveRole();

        return [
            ...$this->summary($user),
            'email_verified_at' => $user->email_verified_at,
            'role_label' => $role->label(),
            'role_description' => $role->description(),
            'abilities' => $role->abilities(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $role = $this->resolveRole($payload['role'] ?? null);

        $user = User::query()->create([
            'name' => trim((string) $payload['name']),
            'email' => mb_strtolower(trim((string) $payload['email'])),
            'role' => $role,
            'password' => (string) $payload['password'],
            'email_verified_at' => ($payload['email_verified'] ?? true) ? now() : null,
        ]);

        $this->auditService->record(
            AuditAction::Create,
            $user,
            [],
            Arr::except($this->detail($user), ['abilities']),
        );

        return $this->detail($user);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(User $user, array $payload, User $actor): array
    {
        $before = Arr::except($this->detail($user), ['abilities']);
        $currentRole = $user->resolveRole();
        $nextRole = array_key_exists('role', $payload)
            ? $this->resolveRole($payload['role'])
            : $currentRole;

        $this->guardLastSuperadmin($user, $nextRole);

        $user->fill([
            'name' => array_key_exists('name', $payload) ? trim((string) $payload['name']) : $user->name,
            'email' => array_key_exists('email', $payload) ? mb_strtolower(trim((string) $payload['email'])) : $user->email,
            'role' => $nextRole,
        ]);

        if (array_key_exists('email_verified', $payload)) {
            $user->email_verified_at = $payload['email_verified'] ? ($user->email_verified_at ?? now()) : null;
        }

        if (filled($payload['password'] ?? null)) {
            $user->password = (string) $payload['password'];
        }

        $user->save();

        $after = Arr::except($this->detail($user->fresh()), ['abilities']);
        [$oldValues, $newValues] = $this->auditService->diff($before, $after);
        $this->auditService->record(AuditAction::Update, $user, $oldValues, $newValues);

        return $this->detail($user->fresh());
    }

    public function delete(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'user' => ['Akun yang sedang dipakai tidak bisa dihapus dari dashboard ini.'],
            ]);
        }

        $this->guardLastSuperadmin($user, null);

        $before = Arr::except($this->detail($user), ['abilities']);
        $user->delete();
        $this->auditService->record(AuditAction::Delete, $user, $before, []);
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(User $user): array
    {
        $role = $user->resolveRole();

        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role->value,
            'role_label' => $role->label(),
            'is_admin' => $user->isAdministrator(),
            'email_verified' => $user->email_verified_at !== null,
            'email_verified_at' => $user->email_verified_at,
            'updated_at' => $user->updated_at,
            'created_at' => $user->created_at,
        ];
    }

    private function resolveRole(mixed $role): UserRole
    {
        return $role instanceof UserRole ? $role : UserRole::from((string) $role);
    }

    private function guardLastSuperadmin(User $user, ?UserRole $nextRole): void
    {
        if ($user->resolveRole() !== UserRole::Superadmin) {
            return;
        }

        if ($nextRole === UserRole::Superadmin) {
            return;
        }

        $remainingSuperadmins = User::query()
            ->where('role', UserRole::Superadmin->value)
            ->whereKeyNot($user->getKey())
            ->count();

        if ($remainingSuperadmins === 0) {
            throw ValidationException::withMessages([
                'role' => ['Minimal harus ada satu user dengan role superadmin.'],
            ]);
        }
    }
}
