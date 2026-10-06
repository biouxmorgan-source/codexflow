<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'En cours',
            self::Archived => 'Archivée',
        };
    }
}
