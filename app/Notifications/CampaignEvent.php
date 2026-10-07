<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Notifications\Notification;

/**
 * Événement d'une campagne pour le centre de notifications : un élément reçu ou repris,
 * un message, une intention de joueur. Le texte est écrit pour son destinataire et ne
 * contient que ce qu'il a le droit de voir.
 */
class CampaignEvent extends Notification
{
    public const KINDS = [
        'grant' => 'Reçu',
        'revoke' => 'Repris',
        'message' => 'Message',
        'intention' => 'À jouer',
    ];

    public function __construct(
        public Campaign $campaign,
        public string $kind,
        public string $text,
        public string $url,
        /** @var array<string, int|string> par exemple character_id, pour marquer lu depuis la bonne page */
        public array $extra = [],
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'campaign_id' => $this->campaign->id,
            'campaign' => $this->campaign->name,
            'kind' => $this->kind,
            'text' => mb_substr($this->text, 0, 300),
            'url' => $this->url,
        ] + $this->extra;
    }
}
