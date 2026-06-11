<?php

namespace App\Policies;

use App\Models\ApiClient;
use App\Models\ImportMapping;
use App\Models\User;

class ImportMappingPolicy
{
    public function viewAny(User|ApiClient $actor): bool
    {
        return $this->hasAbility($actor, 'imports:read');
    }

    public function view(User|ApiClient $actor, ImportMapping $importMapping): bool
    {
        return $this->hasAbility($actor, 'imports:read');
    }

    public function create(User|ApiClient $actor): bool
    {
        return $this->hasAbility($actor, 'imports:create');
    }

    public function update(User|ApiClient $actor, ImportMapping $importMapping): bool
    {
        return $this->hasAbility($actor, 'imports:create');
    }

    private function hasAbility(User|ApiClient $actor, string $ability): bool
    {
        if ($actor instanceof User) {
            return $actor->hasAbility($ability);
        }

        $token = $actor->currentAccessToken();

        return $token !== null && $token->can($ability);
    }
}
