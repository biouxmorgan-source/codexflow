<?php

namespace App\Policies;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\User;

/**
 * Propriétaire : tout, dont les membres et la suppression. Co-MJ : préparation et session.
 * Joueur : son personnage, les messages, la recherche. Spectateur : l'écran de table seulement.
 */
class CampaignPolicy
{
    public function view(User $user, Campaign $campaign): bool
    {
        return $campaign->roleOf($user) !== null;
    }

    /** Participer : messages, recherche, discussion. Pas les spectateurs. */
    public function play(User $user, Campaign $campaign): bool
    {
        return in_array($campaign->roleOf($user), [CampaignRole::GameMaster, CampaignRole::Player], true);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $campaign->isGameMaster($user);
    }

    /** Membres, invitations et rôles. */
    public function manage(User $user, Campaign $campaign): bool
    {
        return $campaign->isOwnedBy($user);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $campaign->isOwnedBy($user);
    }
}
