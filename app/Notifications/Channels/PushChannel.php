<?php

namespace App\Notifications\Channels;

use App\Support\SystemHealth;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use Throwable;

/**
 * Envoi push qui ne casse jamais l'action en cours : un service push injoignable
 * est journalisé, la notification reste dans la cloche.
 */
class PushChannel
{
    public static function enabled(): bool
    {
        return filled(config('webpush.vapid.public_key')) && filled(config('webpush.vapid.private_key'));
    }

    /** @return array<int, mixed> */
    public function send(mixed $notifiable, Notification $notification): array
    {
        // Résolu ici, sous rescue : sans GMP ni BCMath, la bibliothèque web-push
        // lève une erreur dès sa construction.
        // L'échec est aussi retenu pour la console d'administration.
        return rescue(fn () => app(WebPushChannel::class)->send($notifiable, $notification), function (Throwable $error) {
            SystemHealth::pushFailed($error);

            return [];
        });
    }
}
