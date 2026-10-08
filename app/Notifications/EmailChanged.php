<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Prévient l'ancienne adresse quand l'adresse d'un compte change : si ce n'est pas
 * son titulaire qui l'a fait, il le sait aussitôt.
 */
class EmailChanged extends Notification
{
    use Queueable;

    public function __construct(private string $newEmail) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Votre adresse e-mail LoreMundi a changé'))
            ->line(__('L’adresse de votre compte LoreMundi est désormais :email.', ['email' => $this->newEmail]))
            ->line(__('Si vous n’êtes pas à l’origine de ce changement, répondez à ce message ou écrivez à l’administrateur du site.'));
    }
}
