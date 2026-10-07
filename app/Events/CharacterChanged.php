<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * La fiche d'un personnage a changé (compteur, champ, verrouillage) : le MJ et le joueur
 * qui l'ont ouverte la rechargent. Aucun contenu n'est envoyé.
 */
class CharacterChanged implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(public int $characterId) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('characters.'.$this->characterId);
    }

    public function broadcastAs(): string
    {
        return 'changed';
    }

    /** @return array<string, never> */
    public function broadcastWith(): array
    {
        return [];
    }
}
