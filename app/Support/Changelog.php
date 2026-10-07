<?php

namespace App\Support;

/**
 * Nouveautés par version (lang/fr/changelog.php) et ce qu'un utilisateur n'a pas encore vu.
 */
class Changelog
{
    public static function version(): string
    {
        return config('codexflow.version');
    }

    /** @return array<string, array{date: string, title: string, items: list<string>}> de la plus récente à la plus ancienne */
    public static function all(): array
    {
        $entries = trans('changelog');

        // Langue sans lang/{langue}/changelog.php : on montre les nouveautés en français.
        return is_array($entries) ? $entries : trans('changelog', [], Locale::DEFAULT);
    }

    /**
     * Versions publiées depuis la dernière vue par l'utilisateur. Un compte d'avant
     * « Quoi de neuf » (aucune version vue) ne reçoit que la dernière.
     *
     * @return array<string, array{date: string, title: string, items: list<string>}>
     */
    public static function unseenSince(?string $seen): array
    {
        if ($seen === self::version()) {
            return [];
        }

        if ($seen === null) {
            return array_slice(self::all(), 0, 1, true);
        }

        return array_filter(self::all(), fn (string $version) => version_compare($version, $seen, '>') && version_compare($version, self::version(), '<='), ARRAY_FILTER_USE_KEY);
    }
}
