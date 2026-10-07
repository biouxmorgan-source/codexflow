<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Rule;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Moteur de contexte, déterministe et sans IA : choisit les fiches à montrer au MJ
 * à partir des associations explicites de la scène en cours.
 */
final class SessionContext
{
    /**
     * Fiches de la scène (avec leur précision), puis fiches citées dans sa préparation.
     *
     * @return Collection<int, array{entity: Entity, note: ?string, source: string}>
     */
    public static function cards(Campaign $campaign, ?Scene $scene): Collection
    {
        if ($scene === null) {
            return collect();
        }

        $linked = $scene->entities()->get();
        $cited = EntityLinks::referenced($scene->description, $campaign)->whereNotIn('id', $linked->modelKeys());

        $cards = $linked->map(fn (Entity $entity) => ['entity' => $entity, 'note' => $entity->pivot->note, 'source' => __('Dans la scène')])
            ->concat($cited->map(fn (Entity $entity) => ['entity' => $entity, 'note' => null, 'source' => __('Cité dans la préparation')]))
            ->values();

        self::load($campaign, $cards->pluck('entity'));

        return $cards;
    }

    /**
     * Règles liées à la scène, dans l'ordre choisi.
     *
     * @return EloquentCollection<int, Rule>
     */
    public static function rules(Campaign $campaign, ?Scene $scene): EloquentCollection
    {
        return $scene === null ? new EloquentCollection : $scene->rules()->availableIn($campaign)->get();
    }

    /**
     * Documents liés à la scène, puis à ses règles.
     *
     * @return EloquentCollection<int, Document>
     */
    public static function documents(Campaign $campaign, ?Scene $scene): EloquentCollection
    {
        if ($scene === null) {
            return new EloquentCollection;
        }

        $direct = $scene->documents()->availableIn($campaign)->get();
        $viaRules = Document::query()
            ->availableIn($campaign)
            ->whereHas('rules', fn ($q) => $q->whereIn('rules.id', $scene->rules()->select('rules.id')))
            ->whereKeyNot($direct->modelKeys())
            ->orderBy('title')
            ->get();

        return $direct->concat($viaRules)->values();
    }

    /**
     * Charge en une fois ce que les cartes affichent.
     *
     * @param  Collection<int, Entity>  $entities
     */
    public static function load(Campaign $campaign, Collection $entities): void
    {
        (new EloquentCollection($entities->all()))->load([
            'type',
            'campaignStates' => fn ($q) => $q->where('campaign_id', $campaign->id),
        ]);
    }
}
