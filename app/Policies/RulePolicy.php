<?php

namespace App\Policies;

use App\Models\Rule;
use App\Models\User;

/**
 * Lot 1 : seul le MJ propriétaire y accède. Les joueurs verront la zone publique au lot 2.
 */
class RulePolicy
{
    public function view(User $user, Rule $model): bool
    {
        return $model->user_id === $user->getKey();
    }

    public function update(User $user, Rule $model): bool
    {
        return $model->user_id === $user->getKey();
    }

    public function delete(User $user, Rule $model): bool
    {
        return $model->user_id === $user->getKey();
    }
}
