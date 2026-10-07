<?php

namespace App\Support;

use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\PlayerCharacter;
use Illuminate\Support\Collection;

/**
 * Graphe des relations d'une campagne. Le MJ voit toutes les fiches et toutes les relations ;
 * à travers un personnage, seulement les fiches qu'il connaît et les relations publiques entre elles.
 * Le filtrage est fait ici, côté serveur : rien d'autre n'est envoyé au navigateur.
 */
final class RelationGraph
{
    public const MAX_DEPTH = 3;

    /**
     * @param  Collection<int, array{id: int, name: string, type: string, type_id: int, url: ?string}>  $nodes
     * @param  Collection<int, array{id: int, from: int, to: int, label: string, reverse: ?string, gm: bool}>  $edges
     * @param  Collection<int, string>  $types  types des fiches reliées, pour le filtre
     */
    private function __construct(
        public readonly Collection $nodes,
        public readonly Collection $edges,
        public readonly bool $truncated,
        public readonly Collection $types,
    ) {}

    /**
     * @param  ?int  $focusId  fiche au centre : seules les fiches à moins de $depth relations sont gardées
     * @param  ?int  $typeId  ne garder que les fiches de ce type (la fiche au centre reste)
     */
    public static function build(Campaign $campaign, ?PlayerCharacter $character, ?int $focusId = null, int $depth = 2, ?int $typeId = null, int $limit = 120): self
    {
        $entities = $campaign->availableEntities()
            ->when($character, fn ($q) => $q->whereIn('id', self::knownIds($character)))
            ->with('type')
            ->get(['id', 'name', 'entity_type_id', 'campaign_id', 'world_id'])
            ->keyBy('id');

        $relations = EntityRelation::query()
            ->visibleIn($campaign)
            ->whereIn('from_entity_id', $entities->keys())
            ->whereIn('to_entity_id', $entities->keys())
            ->when($character, fn ($q) => $q->where('zone', Zone::Public))
            ->orderBy('id')
            ->get(['id', 'from_entity_id', 'to_entity_id', 'label', 'reverse_label', 'zone']);

        // Types proposés au filtre : ceux des fiches reliées, avant filtrage.
        $linked = $relations->flatMap(fn (EntityRelation $r) => [$r->from_entity_id, $r->to_entity_id])->unique();
        $types = $entities->only($linked->all())->pluck('type')->unique('id')->sortBy('name')->mapWithKeys(fn ($type) => [$type->id => $type->name]);

        if ($typeId !== null) {
            $entities = $entities->filter(fn (Entity $entity) => $entity->entity_type_id === $typeId || $entity->id === $focusId);
            $relations = $relations->filter(fn (EntityRelation $r) => $entities->has($r->from_entity_id) && $entities->has($r->to_entity_id));
        }

        if ($focusId !== null && $entities->has($focusId)) {
            $kept = self::around($focusId, $relations, max(1, min(self::MAX_DEPTH, $depth)));
        } else {
            // Vue d'ensemble : seulement les fiches reliées à quelque chose.
            $kept = $relations->flatMap(fn (EntityRelation $r) => [$r->from_entity_id, $r->to_entity_id])->unique()->values();
        }

        // Au-delà de la limite, on garde les fiches les plus reliées (et toujours celle du centre).
        $truncated = $kept->count() > $limit;
        if ($truncated) {
            $degree = $relations->flatMap(fn (EntityRelation $r) => [$r->from_entity_id, $r->to_entity_id])->countBy();
            $kept = $kept->sortByDesc(fn (int $id) => $id === $focusId ? PHP_INT_MAX : ($degree[$id] ?? 0))->take($limit)->values();
        }

        $kept = $kept->flip();

        $nodes = $entities->only($kept->keys()->all())
            ->sortBy(fn (Entity $entity) => mb_strtolower($entity->name))
            ->map(fn (Entity $entity) => [
                'id' => $entity->id,
                'name' => $entity->name,
                'type' => $entity->type->name,
                'type_id' => $entity->entity_type_id,
                'url' => self::url($campaign, $character, $entity),
            ])
            ->values();

        $edges = $relations
            ->filter(fn (EntityRelation $r) => isset($kept[$r->from_entity_id], $kept[$r->to_entity_id]))
            ->map(fn (EntityRelation $r) => [
                'id' => $r->id,
                'from' => $r->from_entity_id,
                'to' => $r->to_entity_id,
                'label' => $r->label,
                'reverse' => $r->reverse_label,
                'gm' => $r->zone === Zone::GameMaster,
            ])
            ->values();

        return new self($nodes, $edges, $truncated, $types);
    }

    /** @return Collection<int, int> */
    public static function knownIds(PlayerCharacter $character): Collection
    {
        return $character->grants()->where('kind', 'entity')->pluck('entity_id')->push($character->entity_id)->unique()->values();
    }

    /**
     * Fiches à moins de $depth relations de la fiche au centre.
     *
     * @param  Collection<int, EntityRelation>  $relations
     * @return Collection<int, int>
     */
    private static function around(int $focusId, Collection $relations, int $depth): Collection
    {
        $seen = [$focusId => true];
        $frontier = [$focusId];

        for ($step = 0; $step < $depth && $frontier !== []; $step++) {
            $next = [];
            foreach ($relations as $r) {
                foreach ([[$r->from_entity_id, $r->to_entity_id], [$r->to_entity_id, $r->from_entity_id]] as [$a, $b]) {
                    if (in_array($a, $frontier, true) && ! isset($seen[$b])) {
                        $seen[$b] = true;
                        $next[] = $b;
                    }
                }
            }
            $frontier = $next;
        }

        return collect(array_keys($seen));
    }

    private static function url(Campaign $campaign, ?PlayerCharacter $character, Entity $entity): ?string
    {
        if ($character === null) {
            return route('entities.show', [$campaign, $entity]);
        }

        return $entity->id === $character->entity_id
            ? route('characters.show', [$campaign, $character])
            : route('characters.entity', [$campaign, $character, $entity]);
    }
}
