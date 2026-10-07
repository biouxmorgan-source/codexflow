<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Support\CoGameMaster;

/**
 * Le MJ propriétaire et ses co-MJ accèdent aux documents ; les joueurs les consultent par leurs personnages.
 */
class DocumentPolicy
{
    public function view(User $user, Document $model): bool
    {
        return CoGameMaster::canPrepare($user, $model);
    }

    public function update(User $user, Document $model): bool
    {
        return CoGameMaster::canPrepare($user, $model);
    }

    public function delete(User $user, Document $model): bool
    {
        return CoGameMaster::canPrepare($user, $model);
    }
}
