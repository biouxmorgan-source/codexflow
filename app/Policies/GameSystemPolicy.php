<?php

namespace App\Policies;

use App\Models\GameSystem;
use App\Models\User;

class GameSystemPolicy
{
    public function view(User $user, GameSystem $gameSystem): bool
    {
        return $gameSystem->user_id === $user->getKey();
    }

    public function update(User $user, GameSystem $gameSystem): bool
    {
        return $gameSystem->user_id === $user->getKey();
    }

    public function delete(User $user, GameSystem $gameSystem): bool
    {
        return $gameSystem->user_id === $user->getKey();
    }
}
