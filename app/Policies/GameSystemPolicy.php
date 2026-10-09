<?php

namespace App\Policies;

use App\Models\Campaign;
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

    /**
     * Champs du jeu : le propriétaire, ou un co-MJ d'une campagne de ce jeu quand le propriétaire
     * le lui a confié (option de la page Membres). Les champs servent à toutes les campagnes du jeu.
     */
    public function manageFields(User $user, GameSystem $gameSystem, Campaign $campaign): bool
    {
        if ($gameSystem->user_id === $user->getKey()) {
            return true;
        }

        return $campaign->co_gm_manage_fields
            && $campaign->game_system_id === $gameSystem->id
            && $campaign->user_id === $gameSystem->user_id
            && $campaign->isGameMaster($user);
    }

    public function delete(User $user, GameSystem $gameSystem): bool
    {
        return $gameSystem->user_id === $user->getKey();
    }
}
