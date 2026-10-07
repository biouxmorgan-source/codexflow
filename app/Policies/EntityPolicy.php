<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;
use App\Support\CoGameMaster;

/**
 * Le MJ propriétaire et ses co-MJ accèdent aux fiches ; les joueurs les consultent par leurs personnages.
 */
class EntityPolicy
{
    public function view(User $user, Entity $entity): bool
    {
        return CoGameMaster::canPrepare($user, $entity);
    }

    public function update(User $user, Entity $entity): bool
    {
        return CoGameMaster::canPrepare($user, $entity);
    }

    public function delete(User $user, Entity $entity): bool
    {
        return CoGameMaster::canPrepare($user, $entity);
    }
}
