<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envoyé à la nouvelle adresse d'un compte : elle ne remplace l'ancienne qu'une fois ce lien ouvert,
 * ce qui prouve que le titulaire la reçoit bien.
 */
class ConfirmNewEmail extends Notification
{
    use Queueable;

    public function __construct(private string $url) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Confirmez votre nouvelle adresse e-mail LoreMundi'))
            ->line(__('Vous avez demandé à utiliser cette adresse pour votre compte LoreMundi.'))
            ->action(__('Confirmer cette adresse'), $this->url)
            ->line(__('Ce lien est valable :minutes minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message : rien ne change.', ['minutes' => 60]));
    }
}
