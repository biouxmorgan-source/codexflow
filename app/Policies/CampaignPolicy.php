<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function view(User $user, Campaign $campaign): bool
    {
        return $campaign->roleOf($user) !== null;
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $campaign->isGameMaster($user);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $campaign->user_id === $user->getKey();
    }
}
