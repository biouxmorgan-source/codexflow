<?php

namespace App\Policies;

use App\Models\AudioTrack;
use App\Models\User;

/**
 * La bibliothèque sonore appartient à la préparation : le MJ propriétaire et ses co-MJ seulement.
 * L'écran de table reçoit le morceau joué par sa propre route (TableScreenController::audio).
 */
class AudioTrackPolicy
{
    public function view(User $user, AudioTrack $track): bool
    {
        return $user->can('update', $track->campaign);
    }

    public function update(User $user, AudioTrack $track): bool
    {
        return $user->can('update', $track->campaign);
    }

    public function delete(User $user, AudioTrack $track): bool
    {
        return $user->can('update', $track->campaign);
    }
}
