<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Surcharge d'une entité de monde dans une campagne, sans toucher à la fiche mondiale.
 */
#[Fillable(['campaign_id', 'status', 'gm_notes', 'overrides'])]
#[Hidden(['gm_notes'])]
class CampaignEntityState extends Model
{
    protected $attributes = [
        'overrides' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'overrides' => 'array',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
