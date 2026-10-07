<?php

namespace App\Enums;

enum RuleOrigin: string
{
    case Reference = 'reference';
    case House = 'house';
    case Test = 'test';

    /** Libellé dans la langue courante, ou dans $locale (« fr » pour l'export réimportable). */
    public function label(?string $locale = null): string
    {
        return match ($this) {
            self::Reference => __('Référence', [], $locale),
            self::House => __('Maison', [], $locale),
            self::Test => __('Test', [], $locale),
        };
    }
}
