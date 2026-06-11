<?php

namespace App\Policies;

use App\Models\ApiClient;
use App\Models\MasterDataType;
use App\Models\User;

class MasterDataTypePolicy
{
    public function viewAny(User|ApiClient $actor): bool
    {
        return $this->hasAbility($actor, 'master-data:read');
    }

    public function view(User|ApiClient $actor, MasterDataType $masterDataType): bool
    {
        return $this->hasAbility($actor, 'master-data:read');
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
