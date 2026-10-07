<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * Envoi push qui ne casse jamais l'action en cours : un service push injoignable
 * est journalisé, la notification reste dans la cloche.
 */
class PushChannel
{
    public function __construct(private WebPushChannel $channel) {}

    public static function enabled(): bool
    {
        return filled(config('webpush.vapid.public_key')) && filled(config('webpush.vapid.private_key'));
    }

    /** @return array<int, mixed> */
    public function send(mixed $notifiable, Notification $notification): array
    {
        return rescue(fn () => $this->channel->send($notifiable, $notification), []);
    }
}
