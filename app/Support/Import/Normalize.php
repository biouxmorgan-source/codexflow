<?php

namespace App\Support\Import;

use App\Enums\Zone;
use Illuminate\Support\Str;

/**
 * Comparaisons tolérantes pour les en-têtes et valeurs saisies à la main.
 */
final class Normalize
{
    public static function key(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]/', '')->toString();
    }

    public static function zone(string $value): ?Zone
    {
        return match (self::key($value)) {
            '', 'publique', 'public', 'joueurs', 'zonepublique' => Zone::Public,
            'mj', 'gm', 'secret', 'secrete', 'privee', 'prive', 'zonemj' => Zone::GameMaster,
            default => null,
        };
    }
}
