<?php

namespace App\Policies;

use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * La bibliothèque sonore appartient à la préparation : le MJ propriétaire et ses co-MJ seulement.
 * Un son de bibliothèque personnelle s'écoute dans les campagnes où il est ajouté, mais seul son
 * MJ le modifie ou le supprime. L'écran de table reçoit le morceau joué par sa propre route
 * (TableScreenController::audio).
 */
class AudioTrackPolicy
{
    public function view(User $user, AudioTrack $track): bool|Response
    {
        if ($track->inLibrary()) {
            return $track->user_id === $user->id
                || $track->linkedCampaigns()->get()->contains(fn (Campaign $campaign) => $user->can('update', $campaign)
                    && $campaign->availableTracks()->whereKey($track->id)->exists())
                ?: Response::denyAsNotFound();
        }

        return $user->can('update', $track->campaign) ?: Response::denyAsNotFound();
    }

    public function update(User $user, AudioTrack $track): bool
    {
        return $track->inLibrary() ? $track->user_id === $user->id : $user->can('update', $track->campaign);
    }

    public function delete(User $user, AudioTrack $track): bool
    {
        return $this->update($user, $track);
    }
}
