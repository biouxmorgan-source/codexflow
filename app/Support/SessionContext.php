<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Scene;
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

        $cards = $linked->map(fn (Entity $entity) => ['entity' => $entity, 'note' => $entity->pivot->note, 'source' => 'Dans la scène'])
            ->concat($cited->map(fn (Entity $entity) => ['entity' => $entity, 'note' => null, 'source' => 'Cité dans la préparation']))
            ->values();

        self::load($campaign, $cards->pluck('entity'));

        return $cards;
    }

    /**
     * Charge en une fois ce que les cartes affichent.
     *
     * @param  Collection<int, Entity>  $entities
     */
    public static function load(Campaign $campaign, Collection $entities): void
    {
        (new \Illuminate\Database\Eloquent\Collection($entities->all()))->load([
            'type',
            'campaignStates' => fn ($q) => $q->where('campaign_id', $campaign->id),
        ]);
    }
}
