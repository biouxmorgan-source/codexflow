<?php

namespace App\Policies;

use App\Models\User;
use App\Models\World;
use App\Support\CoGameMaster;

class WorldPolicy
{
    public function view(User $user, World $world): bool
    {
        return CoGameMaster::canPrepare($user, $world, edit: false);
    }

    public function update(User $user, World $world): bool
    {
        return CoGameMaster::canPrepare($user, $world);
    }

    public function delete(User $user, World $world): bool
    {
        return $world->user_id === $user->getKey();
    }
}
