<?php

namespace App\Policies;

use App\Enums\CampaignRole;
use App\Models\PlayerCharacter;
use App\Models\User;

/**
 * Le MJ voit et modifie tous les personnages de sa campagne ; un joueur, seulement le sien,
 * tant qu'il fait partie de la campagne.
 */
class PlayerCharacterPolicy
{
    public function view(User $user, PlayerCharacter $character): bool
    {
        $role = $character->campaign->roleOf($user);

        return $role === CampaignRole::GameMaster
            || ($role === CampaignRole::Player && $character->isPlayedBy($user));
    }

    /** Modifier les champs ouverts aux joueurs et les compteurs. */
    public function play(User $user, PlayerCharacter $character): bool
    {
        if ($character->campaign->isGameMaster($user)) {
            return true;
        }

        return $this->view($user, $character) && ! $character->locked;
    }

    public function manage(User $user, PlayerCharacter $character): bool
    {
        return $character->campaign->isGameMaster($user);
    }
}
