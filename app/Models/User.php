<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\HasPublicUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPublicUuid, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => UserRole::class,
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            $role = $user->resolveRole();

            $user->attributes['role'] = $role->value;
            $user->is_admin = $role->grantsFullAccess();
        });
    }

    public function createdMasterData(): HasMany
    {
        return $this->hasMany(MasterData::class, 'created_by');
    }

    public function resolveRole(): UserRole
    {
        $role = $this->getAttribute('role');

        if ($role instanceof UserRole) {
            return $role;
        }

        if (is_string($role) && $role !== '') {
            return UserRole::from($role);
        }

        return $this->is_admin ? UserRole::Admin : UserRole::Viewer;
    }

    public function hasRole(UserRole|string ...$roles): bool
    {
        $currentRole = $this->resolveRole()->value;

        foreach ($roles as $role) {
            if ($currentRole === ($role instanceof UserRole ? $role->value : $role)) {
                return true;
            }
        }

        return false;
    }

    public function hasAbility(string $ability): bool
    {
        return $this->resolveRole()->allowsAbility($ability);
    }

    public function isAdministrator(): bool
    {
        return $this->resolveRole()->grantsFullAccess();
    }
}
