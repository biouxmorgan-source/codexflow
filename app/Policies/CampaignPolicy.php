<?php

namespace App\Policies;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Propriétaire : tout, dont les membres et la suppression. Co-MJ : préparation et session.
 * Joueur : son personnage, les messages, la recherche. Spectateur : l'écran de table seulement.
 *
 * Un compte étranger à la campagne reçoit la même réponse qu'une campagne inexistante (404) :
 * essayer des numéros dans la barre d'adresse n'apprend même pas qu'une campagne existe.
 */
class CampaignPolicy
{
    public function view(User $user, Campaign $campaign): bool|Response
    {
        return $campaign->roleOf($user) !== null ?: Response::denyAsNotFound();
    }

    /** Participer : messages, recherche, discussion. Pas les spectateurs. */
    public function play(User $user, Campaign $campaign): bool|Response
    {
        $role = $campaign->roleOf($user);

        return in_array($role, [CampaignRole::GameMaster, CampaignRole::Player], true) ?: self::refuse($role);
    }

    public function update(User $user, Campaign $campaign): bool|Response
    {
        $role = $campaign->roleOf($user);

        return $role === CampaignRole::GameMaster ?: self::refuse($role);
    }

    /** Membres, invitations et rôles. */
    public function manage(User $user, Campaign $campaign): bool|Response
    {
        return $campaign->isOwnedBy($user) ?: self::refuse($campaign->roleOf($user));
    }

    /** Seul le propriétaire duplique sa campagne : la copie lui appartient. */
    public function duplicate(User $user, Campaign $campaign): bool|Response
    {
        return $campaign->isOwnedBy($user) ?: self::refuse($campaign->roleOf($user));
    }

    public function delete(User $user, Campaign $campaign): bool|Response
    {
        return $campaign->isOwnedBy($user) ?: self::refuse($campaign->roleOf($user));
    }

    /** Un membre sait que la campagne existe : 403. Les autres : 404, comme une campagne inexistante. */
    private static function refuse(?CampaignRole $role): Response
    {
        return $role === null ? Response::denyAsNotFound() : Response::deny();
    }
}
