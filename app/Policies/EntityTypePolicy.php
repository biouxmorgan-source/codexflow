<?php

namespace App\Policies;

use App\Models\EntityType;
use App\Models\User;

/**
 * Les types standards sont partagés et non modifiables ; un type personnalisé appartient à son créateur.
 */
class EntityTypePolicy
{
    public function update(User $user, EntityType $entityType): bool
    {
        return $entityType->user_id === $user->getKey();
    }

    public function delete(User $user, EntityType $entityType): bool
    {
        return $entityType->user_id === $user->getKey();
    }
}
