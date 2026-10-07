<?php

namespace App\Policies;

use App\Models\GameSystem;
use App\Models\User;
use App\Support\CoGameMaster;

class GameSystemPolicy
{
    public function view(User $user, GameSystem $gameSystem): bool
    {
        return CoGameMaster::canPrepare($user, $gameSystem, edit: false);
    }

    public function update(User $user, GameSystem $gameSystem): bool
    {
        return CoGameMaster::canPrepare($user, $gameSystem);
    }

    public function delete(User $user, GameSystem $gameSystem): bool
    {
        return $gameSystem->user_id === $user->getKey();
    }
}
