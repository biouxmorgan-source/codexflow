<?php

namespace App\Models;

use App\Enums\RuleOrigin;
use App\Enums\RuleStatus;
use App\Enums\Zone;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Règle ou aide de jeu saisie librement par le MJ. Rattachée au jeu, elle sert à toutes
 * ses campagnes ; rattachée à une campagne, elle reste locale.
 */
#[Fillable(['title', 'category', 'summary', 'procedure', 'gm_notes', 'source', 'origin', 'status', 'zone'])]
class Rule extends Model
{
    use RecordsActivity;

    protected $attributes = [
        'origin' => 'reference',
        'status' => 'available',
        'zone' => 'public',
    ];

    /** Les notes MJ ne sortent jamais par la sérialisation. */
    protected $hidden = ['gm_notes'];

    protected function casts(): array
    {
        return [
            'origin' => RuleOrigin::class,
            'status' => RuleStatus::class,
            'zone' => Zone::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<GameSystem, $this> */
    public function gameSystem(): BelongsTo
    {
        return $this->belongsTo(GameSystem::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderByRaw('lower(tags.name)');
    }

    /** @return BelongsToMany<Document, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class)->withTimestamps()->orderBy('documents.title');
    }

    /** @return BelongsToMany<Scene, $this> */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class);
    }

    /** @return HasMany<ToPlayItem, $this> */
    public function toPlayItems(): HasMany
    {
        return $this->hasMany(ToPlayItem::class);
    }

    public function isShared(): bool
    {
        return $this->game_system_id !== null;
    }

    /**
     * Règles utilisables dans la campagne : les siennes et celles de son jeu.
     *
     * @param  Builder<Rule>  $query
     */
    public function scopeAvailableIn(Builder $query, Campaign $campaign): void
    {
        $query->where(fn (Builder $q) => $q
            ->where('campaign_id', $campaign->getKey())
            ->orWhere('game_system_id', $campaign->game_system_id));
    }

    public function activityType(): string
    {
        return 'rule';
    }

    public function activityLabel(): string
    {
        return $this->title;
    }

    public function activityScope(): array
    {
        return ['campaign_id' => $this->campaign_id, 'game_system_id' => $this->game_system_id];
    }
}
