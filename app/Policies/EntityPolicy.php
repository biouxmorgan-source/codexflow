<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;

/**
 * Lot 1 : seul le MJ propriétaire accède aux fiches. La lecture de la zone publique
 * par les joueurs arrivera avec les révélations (lot 2).
 */
class EntityPolicy
{
    public function view(User $user, Entity $entity): bool
    {
        return $entity->user_id === $user->getKey();
    }

    public function update(User $user, Entity $entity): bool
    {
        return $entity->user_id === $user->getKey();
    }

    public function delete(User $user, Entity $entity): bool
    {
        return $entity->user_id === $user->getKey();
    }
}
