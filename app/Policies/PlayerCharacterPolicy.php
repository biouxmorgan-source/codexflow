<?php

namespace App\Policies;

use App\Enums\CampaignRole;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Le MJ voit et modifie tous les personnages de sa campagne ; un joueur, seulement le sien,
 * tant qu'il fait partie de la campagne.
 */
class PlayerCharacterPolicy
{
    public function view(User $user, PlayerCharacter $character): bool|Response
    {
        $role = $character->campaign->roleOf($user);

        if ($role === CampaignRole::GameMaster || ($role === CampaignRole::Player && $character->isPlayedBy($user))) {
            return true;
        }

        // Hors de la campagne : la même réponse qu'un personnage inexistant.
        return $role === null ? Response::denyAsNotFound() : Response::deny();
    }

    /** Modifier les champs ouverts aux joueurs et les compteurs. */
    public function play(User $user, PlayerCharacter $character): bool
    {
        if ($character->campaign->isGameMaster($user)) {
            return true;
        }

        // Personnage au repos : le joueur relit sa fiche, sans la modifier.
        return $this->view($user, $character) === true && ! $character->locked && $character->is_active;
    }

    public function manage(User $user, PlayerCharacter $character): bool
    {
        return $character->campaign->isGameMaster($user);
    }
}
