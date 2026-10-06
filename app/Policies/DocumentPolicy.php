<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Lot 1 : seul le MJ propriétaire y accède. Les joueurs verront la zone publique au lot 2.
 */
class DocumentPolicy
{
    public function view(User $user, Document $model): bool
    {
        return $model->user_id === $user->getKey();
    }

    public function update(User $user, Document $model): bool
    {
        return $model->user_id === $user->getKey();
    }

    public function delete(User $user, Document $model): bool
    {
        return $model->user_id === $user->getKey();
    }
}
