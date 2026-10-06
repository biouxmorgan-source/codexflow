<?php

namespace App\Models;

use Database\Factories\EntityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fiche d'entité. Zone publique : name, summary, description. Zone MJ : gm_notes.
 */
#[Fillable(['entity_type_id', 'name', 'summary', 'description', 'gm_notes', 'image_path'])]
#[Hidden(['gm_notes'])]
class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<EntityType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(EntityType::class, 'entity_type_id');
    }

    /** @return BelongsTo<World, $this> */
    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return HasMany<CampaignEntityState, $this> */
    public function campaignStates(): HasMany
    {
        return $this->hasMany(CampaignEntityState::class);
    }

    public function isWorldEntity(): bool
    {
        return $this->world_id !== null;
    }

    /** @param Builder<Entity> $query */
    public function scopeAvailableIn(Builder $query, Campaign $campaign): void
    {
        $query->where(function (Builder $q) use ($campaign) {
            $q->where('campaign_id', $campaign->getKey());

            if ($campaign->world_id !== null) {
                $q->orWhere('world_id', $campaign->world_id);
            }
        });
    }

    /**
     * État de l'entité dans une campagne, créé à la demande.
     */
    public function stateIn(Campaign $campaign): CampaignEntityState
    {
        return $this->campaignStates()->firstOrNew(['campaign_id' => $campaign->getKey()]);
    }
}
