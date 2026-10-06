<?php

namespace App\Enums;

enum RuleOrigin: string
{
    case Reference = 'reference';
    case House = 'house';
    case Test = 'test';

    public function label(): string
    {
        return match ($this) {
            self::Reference => 'Référence',
            self::House => 'Maison',
            self::Test => 'Test',
        };
    }
}
