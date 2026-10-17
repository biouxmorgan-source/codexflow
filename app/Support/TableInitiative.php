<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\MapToken;

/**
 * Ordre d'initiative de l'écran de table : le MJ prépare la liste (PJ, ennemis, avec ou sans
 * valeur), puis la fait apparaître par-dessus ce qui est affiché. Les participants sans initiative
 * (un PNJ présent, un objet, autre chose) s'affichent à part, sans ordre. Une ligne non montrée
 * reste côté MJ. Aucun jet automatique : la valeur est celle annoncée à la table.
 */
final class TableInitiative
{
    public const MAX_ENTRIES = 60;

    /**
     * @return array{shown: bool, round: int, turn: int|null, next: int, entries: list<array{id: int, label: string, entity_id: int|null, color: string, value: float|null, ordered: bool, visible: bool}>}
     */
    public static function state(Campaign $campaign): array
    {
        $state = $campaign->table_initiative ?? [];

        return [
            'shown' => (bool) ($state['shown'] ?? false),
            'round' => max(1, (int) ($state['round'] ?? 1)),
            'turn' => isset($state['turn']) ? (int) $state['turn'] : null,
            'next' => max(1, (int) ($state['next'] ?? 1)),
            'entries' => array_values($state['entries'] ?? []),
        ];
    }

    /**
     * Ce que voient les joueurs : rien tant que le MJ ne la montre pas, jamais une ligne non montrée.
     *
     * @return array{round: int, turn: int|null, ordered: list<array<string, mixed>>, others: list<array<string, mixed>>}|null
     */
    public static function forTable(Campaign $campaign): ?array
    {
        $state = self::state($campaign);

        if (! $state['shown'] || ! CampaignFeatures::usable($campaign, 'table')) {
            return null;
        }

        $visible = collect($state['entries'])->where('visible', true);
        $ordered = $visible->where('ordered', true)->values();

        return [
            'round' => $state['round'],
            'turn' => $ordered->contains('id', $state['turn']) ? $state['turn'] : null,
            'ordered' => $ordered->all(),
            'others' => $visible->where('ordered', false)->values()->all(),
        ];
    }

    public static function add(Campaign $campaign, string $label, ?Entity $entity = null, ?string $color = null, bool $ordered = true): void
    {
        $state = self::state($campaign);

        if (count($state['entries']) >= self::MAX_ENTRIES) {
            return;
        }

        $state['entries'][] = [
            'id' => $state['next'],
            'label' => mb_substr(trim($label), 0, 100),
            'entity_id' => $entity?->id,
            'color' => $color ?? MapToken::COLORS[count($state['entries']) % count(MapToken::COLORS)],
            'value' => null,
            'ordered' => $ordered,
            // Un PJ apparaît d'emblée ; un ennemi ou un PNJ attend que le MJ le montre.
            'visible' => $entity !== null && $campaign->playerCharacters()->where('entity_id', $entity->id)->exists(),
        ];
        $state['next']++;

        self::save($campaign, $state);
    }

    /** Les personnages des joueurs, chacun une fois. */
    public static function addCharacters(Campaign $campaign): void
    {
        $present = collect(self::state($campaign)['entries'])->pluck('entity_id')->filter()->all();

        $campaign->playerCharacters()->where('is_active', true)->with('entity')->orderBy('id')->get()
            ->reject(fn ($character) => $character->entity === null || in_array($character->entity_id, $present, true))
            ->each(fn ($character) => self::add($campaign, $character->entity->name, $character->entity));
    }

    /**
     * Les jetons d'une carte qui n'y sont pas encore : même nom, même couleur. Un jeton masqué
     * arrive non montré.
     */
    public static function addTokens(Campaign $campaign, iterable $tokens): void
    {
        foreach ($tokens as $token) {
            $entries = self::state($campaign)['entries'];
            $already = collect($entries)->contains(fn (array $entry) => $token->entity_id !== null
                ? $entry['entity_id'] === $token->entity_id
                : $entry['entity_id'] === null && $entry['label'] === $token->label);

            if (! $already) {
                self::add($campaign, $token->label, $token->entity, $token->color);

                if ($token->hidden) {
                    $state = self::state($campaign);
                    $state['entries'][count($state['entries']) - 1]['visible'] = false;
                    self::save($campaign, $state);
                }
            }
        }
    }

    /** @param array{value?: float|null, ordered?: bool, visible?: bool, label?: string} $changes */
    public static function update(Campaign $campaign, int $id, array $changes): void
    {
        $state = self::state($campaign);

        foreach ($state['entries'] as &$entry) {
            if ($entry['id'] === $id) {
                $entry = array_merge($entry, array_intersect_key($changes, array_flip(['value', 'ordered', 'visible', 'label'])));
            }
        }
        unset($entry);

        self::save($campaign, $state);
    }

    public static function remove(Campaign $campaign, int $id): void
    {
        $state = self::state($campaign);
        $state['entries'] = array_values(array_filter($state['entries'], fn (array $entry) => $entry['id'] !== $id));

        if ($state['turn'] === $id) {
            $state['turn'] = null;
        }

        self::save($campaign, $state);
    }

    /** Monte ou descend une ligne d'un cran (égalités, ordre choisi par le MJ). */
    public static function shift(Campaign $campaign, int $id, int $delta): void
    {
        $state = self::state($campaign);
        $index = collect($state['entries'])->search(fn (array $entry) => $entry['id'] === $id);
        $target = $index === false ? null : $index + max(-1, min(1, $delta));

        if ($target !== null && isset($state['entries'][$target])) {
            [$state['entries'][$index], $state['entries'][$target]] = [$state['entries'][$target], $state['entries'][$index]];
            self::save($campaign, $state);
        }
    }

    /** Range par valeur décroissante ; sans valeur, à la fin. L'ordre à égalité est gardé. */
    public static function sort(Campaign $campaign): void
    {
        $state = self::state($campaign);
        $state['entries'] = collect($state['entries'])
            ->values()
            ->sortBy([fn (array $a, array $b) => [$a['value'] === null, -($a['value'] ?? 0)] <=> [$b['value'] === null, -($b['value'] ?? 0)]])
            ->values()->all();

        self::save($campaign, $state);
    }

    /**
     * Tour suivant (ou précédent) parmi les lignes rangées et montrées ; après la dernière,
     * on revient à la première et le round avance.
     */
    public static function step(Campaign $campaign, int $delta = 1): void
    {
        $state = self::state($campaign);
        $ids = collect($state['entries'])->where('ordered', true)->where('visible', true)->pluck('id')->values();

        if ($ids->isEmpty()) {
            return;
        }

        $index = $ids->search($state['turn']);

        if ($index === false) {
            $state['turn'] = $ids->first();
        } elseif ($delta >= 0) {
            $state['round'] += $index === $ids->count() - 1 ? 1 : 0;
            $state['turn'] = $ids[($index + 1) % $ids->count()];
        } elseif ($index > 0 || $state['round'] > 1) {
            $state['round'] -= $index === 0 ? 1 : 0;
            $state['turn'] = $ids[($index - 1 + $ids->count()) % $ids->count()];
        }

        self::save($campaign, $state);
    }

    public static function show(Campaign $campaign, bool $shown): void
    {
        $state = self::state($campaign);

        if ($shown) {
            CampaignFeatures::ensure($campaign, 'table');
        }

        $state['shown'] = $shown;
        self::save($campaign, $state);
    }

    /** Nouveau combat : la liste est vidée, retour au round 1. */
    public static function reset(Campaign $campaign): void
    {
        self::save($campaign, ['shown' => false, 'round' => 1, 'turn' => null, 'next' => self::state($campaign)['next'], 'entries' => []]);
    }

    /** @param array<string, mixed> $state */
    private static function save(Campaign $campaign, array $state): void
    {
        $campaign->forceFill(['table_initiative' => $state])->save();
        TableDisplay::broadcast($campaign);
    }
}
