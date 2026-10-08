<?php

namespace App\Models;

use App\Enums\Zone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Relation orientée entre deux fiches, avec un libellé libre et un libellé inverse facultatif.
 */
#[Fillable(['label', 'reverse_label', 'zone'])]
class EntityRelation extends Model
{
    protected function casts(): array
    {
        return ['zone' => Zone::class];
    }

    /** @return BelongsTo<Entity, $this> */
    public function from(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'from_entity_id');
    }

    /** @return BelongsTo<Entity, $this> */
    public function to(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'to_entity_id');
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relations visibles dans une campagne : celles du monde et celles de cette campagne.
     *
     * @param  Builder<EntityRelation>  $query
     */
    public function scopeVisibleIn(Builder $query, Campaign $campaign): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('campaign_id')->orWhere('campaign_id', $campaign->getKey()));
    }

    /**
     * Libellé lu depuis l'autre extrémité : « emploie » si précisé ; sans inverse, « « surveille » par »
     * pour ne pas lire la relation à l'envers (« Auberge surveille Odon »).
     */
    public function labelFrom(Entity $entity): string
    {
        if (! $entity->is($this->to) || $entity->is($this->from)) {
            return $this->label;
        }

        return $this->reverse_label ?: __('« :label » par', ['label' => $this->label]);
    }

    public function otherSide(Entity $entity): Entity
    {
        return $entity->is($this->from) ? $this->to : $this->from;
    }
}
