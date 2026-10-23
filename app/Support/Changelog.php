<?php

namespace App\Support;

use App\Models\User;

/**
 * Nouveautés par version (lang/fr/changelog.php) et ce qu'un utilisateur n'a pas encore vu.
 *
 * L'administrateur lit le détail de chaque version. Les autres comptes ne voient que les
 * versions notables, celles qui ont une phrase « short », et seulement cette phrase.
 */
class Changelog
{
    public static function version(): string
    {
        return config('codexflow.version');
    }

    /** Détail complet pour l'administrateur, version courte pour les autres. */
    public static function detailedFor(?User $user): bool
    {
        return (bool) $user?->is_admin;
    }

    /** @return array<string, array{date: string, title: string, short?: string, items: list<string>}> de la plus récente à la plus ancienne */
    public static function all(bool $detailed = true): array
    {
        $entries = trans('changelog');

        // Langue sans lang/{langue}/changelog.php : on montre les nouveautés en français.
        $entries = is_array($entries) ? $entries : trans('changelog', [], Locale::DEFAULT);

        if ($detailed) {
            return $entries;
        }

        $notable = array_filter($entries, fn (array $entry) => filled($entry['short'] ?? null));

        return array_map(fn (array $entry) => ['items' => [$entry['short']]] + $entry, $notable);
    }

    /**
     * Versions publiées depuis la dernière vue par l'utilisateur. Un compte d'avant
     * « Quoi de neuf » (aucune version vue) ne reçoit que la dernière.
     *
     * @return array<string, array{date: string, title: string, short?: string, items: list<string>}>
     */
    public static function unseenSince(?string $seen, bool $detailed = true): array
    {
        if ($seen === self::version()) {
            return [];
        }

        if ($seen === null) {
            return array_slice(self::all($detailed), 0, 1, true);
        }

        return array_filter(self::all($detailed), fn (string $version) => version_compare($version, $seen, '>') && version_compare($version, self::version(), '<='), ARRAY_FILTER_USE_KEY);
    }
}
