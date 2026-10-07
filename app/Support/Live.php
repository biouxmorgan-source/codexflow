<?php

namespace App\Support;

use App\Events\CharacterChanged;
use App\Events\UserActivity;

/**
 * Mises à jour en direct (Laravel Reverb). Si le serveur Reverb n'est pas lancé,
 * l'appli continue normalement : les pages se mettent à jour au rechargement.
 */
class Live
{
    public static function user(int $userId, string $kind, ?int $campaignId = null): void
    {
        // La diffusion part à la fin de l'instruction : elle doit rester dans rescue().
        rescue(function () use ($userId, $kind, $campaignId) {
            broadcast(new UserActivity($userId, $kind, $campaignId));
        }, report: false);
    }

    public static function character(int $characterId): void
    {
        rescue(function () use ($characterId) {
            broadcast(new CharacterChanged($characterId));
        }, report: false);
    }
}
