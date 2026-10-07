<?php

namespace App\Policies;

use App\Models\Rule;
use App\Models\User;
use App\Support\CoGameMaster;

/**
 * Le MJ propriétaire et ses co-MJ accèdent aux règles ; les joueurs les consultent par leurs personnages.
 */
class RulePolicy
{
    public function view(User $user, Rule $model): bool
    {
        return CoGameMaster::canPrepare($user, $model);
    }

    public function update(User $user, Rule $model): bool
    {
        return CoGameMaster::canPrepare($user, $model);
    }

    public function delete(User $user, Rule $model): bool
    {
        return CoGameMaster::canPrepare($user, $model);
    }
}
