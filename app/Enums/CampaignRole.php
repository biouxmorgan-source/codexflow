<?php

namespace App\Enums;

enum CampaignRole: string
{
    case GameMaster = 'gm';
    case Player = 'player';

    public function label(): string
    {
        return match ($this) {
            self::GameMaster => __('Maître de jeu'),
            self::Player => __('Joueur'),
        };
    }
}
