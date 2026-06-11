<?php

namespace App\Policies;

use App\Models\ApiClient;
use App\Models\MasterData;
use App\Models\User;

class MasterDataPolicy
{
    public function viewAny(User|ApiClient $actor): bool
    {
        return $this->hasAbility($actor, 'master-data:read');
    }

    public function view(User|ApiClient $actor, MasterData $masterData): bool
    {
        return $this->hasAbility($actor, 'master-data:read');
    }

    public function create(User|ApiClient $actor): bool
    {
        return $this->hasAbility($actor, 'master-data:write');
    }

    public function update(User|ApiClient $actor, MasterData $masterData): bool
    {
        return $this->hasAbility($actor, 'master-data:write');
    }

    public function delete(User|ApiClient $actor, MasterData $masterData): bool
    {
        return $this->hasAbility($actor, 'master-data:delete');
    }

    public function restore(User|ApiClient $actor, MasterData $masterData): bool
    {
        return $this->hasAbility($actor, 'master-data:delete');
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
