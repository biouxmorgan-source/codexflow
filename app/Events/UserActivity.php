<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Signal envoyé sur le canal privé d'une personne : « quelque chose est arrivé pour vous »
 * (notification, message). Il ne porte aucun contenu : la page se recharge depuis le serveur,
 * qui applique les droits de lecture.
 */
class UserActivity implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(public int $userId, public string $kind, public ?int $campaignId = null) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.'.$this->userId);
    }

    public function broadcastAs(): string
    {
        return 'activity';
    }

    /** @return array{kind: string, campaign_id: ?int} */
    public function broadcastWith(): array
    {
        return ['kind' => $this->kind, 'campaign_id' => $this->campaignId];
    }
}
