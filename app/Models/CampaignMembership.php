<?php

namespace App\Models;

use App\Enums\CampaignRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CampaignMembership extends Pivot
{
    protected $table = 'campaign_memberships';

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'role' => CampaignRole::class,
        ];
    }
}
