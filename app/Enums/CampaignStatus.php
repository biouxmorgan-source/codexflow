<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('En cours'),
            self::Archived => __('Archivée'),
        };
    }
}
