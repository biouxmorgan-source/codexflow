<?php

namespace App\Enums;

enum Zone: string
{
    case Public = 'public';
    case GameMaster = 'gm';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Zone publique',
            self::GameMaster => 'Zone MJ',
        };
    }
}
