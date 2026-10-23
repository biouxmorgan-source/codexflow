<?php

namespace App\Support;

use App\Models\User;
use InvalidArgumentException;

/**
 * Disposition des blocs d'un écran, choisie par chaque utilisateur pour lui seul : l'ordre des blocs,
 * leur colonne de départ et leur largeur (1, 2 ou 3 colonnes), et ceux qu'il a masqués. Elle vaut pour
 * toutes ses campagnes et se range dans users.preferences['layouts']. Purement visuelle : masquer un
 * bloc ne retire aucun droit et ne cache rien à personne d'autre ; chaque bloc garde ses propres vérifications.
 */
class ScreenLayout
{
    /** Colonnes d'un écran large ; sur un petit écran, les blocs s'empilent dans l'ordre. */
    public const COLUMNS = 3;

    /**
     * Blocs de chaque écran, dans l'ordre par défaut, avec leur colonne de départ et leur largeur.
     *
     * @return array<string, list<array{id: string, column: int, span: int}>>
     */
    public static function defaults(): array
    {
        $place = fn (int $column, int $span, string ...$ids) => array_map(fn ($id) => ['id' => $id, 'column' => $column, 'span' => $span], $ids);

        return [
            'campaign' => $place(1, 3, 'session', 'zones', 'tools', 'universe', 'features', 'duplicate', 'export'),
            'session' => [
                ...$place(1, 2, 'now', 'cards', 'secrets', 'rules-docs', 'notes'),
                ...$place(3, 1, 'to-play', 'music', 'table', 'give', 'pins'),
            ],
            'map' => [
                ...$place(1, 2, 'map'),
                ...$place(3, 1, 'tokens', 'templates', 'initiative', 'grid', 'name'),
            ],
        ];
    }

    /**
     * Anciennes dispositions, rangées par colonnes nommées : la place que prend chaque colonne.
     *
     * @var array<string, array<string, array{int, int}>>
     */
    private const LEGACY_COLUMNS = [
        'campaign' => ['main' => [1, 3]],
        'session' => ['main' => [1, 2], 'side' => [3, 1]],
    ];

    /** @return array<string, string> nom de chaque bloc, pour les boutons « Masquer » et « Afficher » */
    public static function labels(string $screen): array
    {
        return match ($screen) {
            'campaign' => [
                'session' => __('Mode Session'),
                'zones' => __('Zones de la campagne'),
                'tools' => __('Outils de la campagne'),
                'universe' => __('Univers'),
                'features' => __('Fonctions de la campagne'),
                'duplicate' => __('Dupliquer la campagne'),
                'export' => __('Exporter la campagne'),
            ],
            'session' => [
                'now' => __('Maintenant'),
                'cards' => __('Fiches utiles'),
                'secrets' => __('Indices'),
                'rules-docs' => __('Règles et documents'),
                'notes' => __('Notes de session'),
                'to-play' => __('À jouer'),
                'music' => __('Musique'),
                'table' => __('Écran de table'),
                'give' => __('Révéler ou donner'),
                'pins' => __('Épinglé'),
            ],
            'map' => [
                'map' => __('Carte'),
                'tokens' => __('Jetons'),
                'templates' => __('Gabarits de zone'),
                'initiative' => __('Initiative'),
                'grid' => __('Grille'),
                'name' => __('Nom de la carte'),
            ],
            default => throw new InvalidArgumentException("Écran inconnu : {$screen}"),
        };
    }

    /**
     * Disposition de l'utilisateur, nettoyée : un bloc inconnu disparaît, un bloc nouveau
     * (ajouté depuis) retrouve sa place par défaut, aucun bloc n'apparaît deux fois.
     *
     * @return array{blocks: list<array{id: string, column: int, span: int}>, hidden: list<string>, custom: bool}
     */
    public static function for(?User $user, string $screen): array
    {
        $saved = $user?->preferences['layouts'][$screen] ?? null;

        if (! is_array($saved)) {
            return ['blocks' => self::defaultsFor($screen), 'hidden' => [], 'custom' => false];
        }

        $blocks = isset($saved['blocks']) ? (array) $saved['blocks'] : self::fromLegacy($screen, (array) ($saved['columns'] ?? []));

        return self::normalize($screen, $blocks, (array) ($saved['hidden'] ?? [])) + ['custom' => true];
    }

    /**
     * @param  array<int, mixed>  $blocks  blocs dans l'ordre : ['id' => …, 'column' => 1-3, 'span' => 1-3]
     * @param  array<int, mixed>  $hidden
     */
    public static function save(User $user, string $screen, array $blocks, array $hidden): void
    {
        // Un bloc absent de l'envoi (masqué, donc pas à l'écran) garde sa place d'avant.
        $previous = self::for($user, $screen)['blocks'];
        $layout = self::normalize($screen, $blocks, $hidden, $previous);
        $preferences = $user->preferences ?? [];
        $preferences['layouts'][$screen] = $layout;

        $user->forceFill(['preferences' => $preferences])->save();
    }

    public static function reset(User $user, string $screen): void
    {
        $preferences = $user->preferences ?? [];
        unset($preferences['layouts'][$screen]);
        if (($preferences['layouts'] ?? null) === []) {
            unset($preferences['layouts']);
        }

        $user->forceFill(['preferences' => $preferences])->save();
    }

    /**
     * Colonne de départ et largeur valides : la largeur tient entre 1 et $columns, et le bloc ne déborde pas à droite.
     *
     * @return array{int, int}
     */
    public static function place(mixed $column, mixed $span, int $columns = self::COLUMNS): array
    {
        $span = is_numeric($span) ? max(1, min($columns, (int) $span)) : 1;
        $column = is_numeric($column) ? max(1, min($columns - $span + 1, (int) $column)) : 1;

        return [$column, $span];
    }

    /**
     * @param  array<int, mixed>  $blocks
     * @param  array<int, mixed>  $hidden
     * @param  list<array{id: string, column: int, span: int}>|null  $reference  ordre où replacer les blocs oubliés
     * @return array{blocks: list<array{id: string, column: int, span: int}>, hidden: list<string>}
     */
    private static function normalize(string $screen, array $blocks, array $hidden, ?array $reference = null): array
    {
        $defaults = self::defaultsFor($screen);
        $known = array_column($defaults, 'id');
        $result = [];

        foreach ($blocks as $block) {
            $id = is_array($block) ? ($block['id'] ?? null) : null;
            if (is_string($id) && in_array($id, $known, true) && ! isset($result[$id])) {
                [$column, $span] = self::place($block['column'] ?? null, $block['span'] ?? null);
                $result[$id] = ['id' => $id, 'column' => $column, 'span' => $span];
            }
        }

        // Un bloc absent reprend sa place (d'avant, sinon par défaut), juste après celui qui le précédait.
        foreach ([$reference ?? [], $defaults] as $order) {
            foreach ($order as $index => $block) {
                if (isset($result[$block['id']]) || ! in_array($block['id'], $known, true)) {
                    continue;
                }
                $before = array_column(array_slice($order, 0, $index), 'id');
                $position = 0;
                foreach (array_keys($result) as $i => $existing) {
                    if (in_array($existing, $before, true)) {
                        $position = $i + 1;
                    }
                }
                $result = array_slice($result, 0, $position, true) + [$block['id'] => $block] + array_slice($result, $position, null, true);
            }
        }

        $hidden = array_values(array_unique(array_filter($hidden, fn ($block) => is_string($block) && in_array($block, $known, true))));

        return ['blocks' => array_values($result), 'hidden' => $hidden];
    }

    /**
     * Ancienne disposition par colonnes nommées : chaque colonne devient une colonne de départ et une largeur.
     *
     * @param  array<string, mixed>  $columns
     * @return list<array{id: mixed, column: int, span: int}>
     */
    private static function fromLegacy(string $screen, array $columns): array
    {
        $blocks = [];
        foreach (self::LEGACY_COLUMNS[$screen] ?? [] as $name => [$column, $span]) {
            foreach ((array) ($columns[$name] ?? []) as $id) {
                $blocks[] = ['id' => $id, 'column' => $column, 'span' => $span];
            }
        }

        return $blocks;
    }

    /** @return list<array{id: string, column: int, span: int}> */
    private static function defaultsFor(string $screen): array
    {
        return self::defaults()[$screen] ?? throw new InvalidArgumentException("Écran inconnu : {$screen}");
    }
}
