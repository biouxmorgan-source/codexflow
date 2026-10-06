<?php

namespace App\Policies;

use App\Models\User;
use App\Models\World;

class WorldPolicy
{
    public function view(User $user, World $world): bool
    {
        return $world->user_id === $user->getKey();
    }

    public function update(User $user, World $world): bool
    {
        return $world->user_id === $user->getKey();
    }

    public function delete(User $user, World $world): bool
    {
        return $world->user_id === $user->getKey();
    }
}
