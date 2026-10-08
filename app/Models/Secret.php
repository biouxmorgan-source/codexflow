<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Secret : une information du MJ, indépendante d'une fiche (« Morel travaille pour le Culte d'Ambre »),
 * reliée à plusieurs fiches, documents ou scènes. Chaque personnage le connaît ou non : le révéler
 * crée un élément « secret » dans ses connaissances.
 */
#[Fillable(['title', 'body', 'kind'])]
class Secret extends Model
{
    use RecordsActivity;

    public const KINDS = ['rumour', 'clue', 'truth'];

    /** @return array<string, string> */
    public static function kinds(): array
    {
        return ['rumour' => __('Rumeur'), 'clue' => __('Indice'), 'truth' => __('Vérité')];
    }

    /** @return array<string, string> */
    public static function states(): array
    {
        return ['hidden' => __('Caché'), 'partial' => __('Partiel'), 'revealed' => __('Révélé')];
    }

    public function kindLabel(): string
    {
        return self::kinds()[$this->kind] ?? self::kinds()['truth'];
    }

    /**
     * État du secret à la table, déduit de qui le connaît : caché (aucun personnage actif),
     * partiel (certains) ou révélé (tous).
     *
     * @param  list<int>  $activeCharacterIds
     */
    public function state(array $activeCharacterIds): string
    {
        $known = count(array_intersect($activeCharacterIds, $this->grants->pluck('player_character_id')->all()));

        return match (true) {
            $known === 0 => 'hidden',
            $known < count($activeCharacterIds) => 'partial',
            default => 'revealed',
        };
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

    /** @return BelongsToMany<Entity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'entity_secret')->orderBy('name');
    }

    /** @return BelongsToMany<Document, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_secret')->orderBy('title');
    }

    /** @return BelongsToMany<Scene, $this> */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class, 'scene_secret')->orderBy('name');
    }

    /** @return HasMany<CharacterGrant, $this> les personnages qui le connaissent */
    public function grants(): HasMany
    {
        return $this->hasMany(CharacterGrant::class);
    }

    /**
     * Secrets reliés à une fiche, un document ou une scène.
     *
     * @param  Builder<Secret>  $query
     */
    public function scopeLinkedTo(Builder $query, Entity|Document|Scene $item): void
    {
        $relation = match (true) {
            $item instanceof Entity => 'entities',
            $item instanceof Document => 'documents',
            default => 'scenes',
        };

        $query->whereHas($relation, fn (Builder $q) => $q->whereKey($item->getKey()));
    }

    public function activityType(): string
    {
        return 'secret';
    }

    public function activityLabel(): string
    {
        return $this->title;
    }

    public function activityScope(): array
    {
        return ['campaign_id' => $this->campaign_id];
    }
}
