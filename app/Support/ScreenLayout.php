<?php

namespace App\Support;

use App\Models\User;
use InvalidArgumentException;

/**
 * Disposition des blocs d'un écran, choisie par chaque utilisateur pour lui seul : l'ordre des blocs,
 * leur colonne et ceux qu'il a masqués. Elle vaut pour toutes ses campagnes et se range dans
 * users.preferences['layouts']. Purement visuelle : masquer un bloc ne retire aucun droit et ne
 * cache rien à personne d'autre ; chaque bloc garde ses propres vérifications.
 */
class ScreenLayout
{
    /**
     * Colonnes de chaque écran et leurs blocs, dans l'ordre par défaut.
     *
     * @return array<string, array<string, list<string>>>
     */
    public static function defaults(): array
    {
        return [
            'campaign' => [
                'main' => ['session', 'zones', 'tools', 'universe', 'features', 'duplicate', 'export'],
            ],
            'session' => [
                'main' => ['now', 'cards', 'secrets', 'rules-docs', 'notes'],
                'side' => ['to-play', 'music', 'table', 'give', 'pins'],
            ],
        ];
    }

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
            default => throw new InvalidArgumentException("Écran inconnu : {$screen}"),
        };
    }

    /**
     * Disposition de l'utilisateur, nettoyée : un bloc inconnu disparaît, un bloc nouveau
     * (ajouté depuis) retrouve sa place par défaut, aucun bloc n'apparaît deux fois.
     *
     * @return array{columns: array<string, list<string>>, hidden: list<string>, custom: bool}
     */
    public static function for(?User $user, string $screen): array
    {
        $saved = $user?->preferences['layouts'][$screen] ?? null;

        return is_array($saved)
            ? self::normalize($screen, (array) ($saved['columns'] ?? []), (array) ($saved['hidden'] ?? [])) + ['custom' => true]
            : ['columns' => self::defaultsFor($screen), 'hidden' => [], 'custom' => false];
    }

    /**
     * @param  array<string, mixed>  $columns
     * @param  array<int, mixed>  $hidden
     */
    public static function save(User $user, string $screen, array $columns, array $hidden): void
    {
        $layout = self::normalize($screen, $columns, $hidden);
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
     * @param  array<string, mixed>  $columns
     * @param  array<int, mixed>  $hidden
     * @return array{columns: array<string, list<string>>, hidden: list<string>}
     */
    private static function normalize(string $screen, array $columns, array $hidden): array
    {
        $defaults = self::defaultsFor($screen);
        $known = array_merge(...array_values($defaults));
        $placed = [];
        $result = array_map(fn () => [], $defaults);

        foreach ($defaults as $column => $unused) {
            foreach ((array) ($columns[$column] ?? []) as $block) {
                if (is_string($block) && in_array($block, $known, true) && ! in_array($block, $placed, true)) {
                    $result[$column][] = $block;
                    $placed[] = $block;
                }
            }
        }

        // Un bloc absent de la disposition enregistrée reprend sa colonne par défaut, à sa place relative.
        foreach ($defaults as $column => $blocks) {
            foreach ($blocks as $index => $block) {
                if (in_array($block, $placed, true)) {
                    continue;
                }
                $before = array_slice($blocks, 0, $index);
                $position = 0;
                foreach ($result[$column] as $i => $existing) {
                    if (in_array($existing, $before, true)) {
                        $position = $i + 1;
                    }
                }
                array_splice($result[$column], $position, 0, [$block]);
                $placed[] = $block;
            }
        }

        $hidden = array_values(array_unique(array_filter($hidden, fn ($block) => is_string($block) && in_array($block, $known, true))));

        return ['columns' => $result, 'hidden' => $hidden];
    }

    /** @return array<string, list<string>> */
    private static function defaultsFor(string $screen): array
    {
        return self::defaults()[$screen] ?? throw new InvalidArgumentException("Écran inconnu : {$screen}");
    }
}
