<?php

namespace App\Livewire\Support;

use App\Models\BugReport;
use App\Support\Changelog;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * « Signaler un problème » : le message arrive aux administrateurs, avec la page concernée.
 * Ouvert aussi sans compte (on ne peut pas se connecter, on ne peut pas s'inscrire) : il faut
 * alors une adresse pour répondre, et les envois sont limités par adresse IP.
 */
class ReportBug extends Component
{
    public string $message = '';

    /** Page où le problème est apparu (chemin de ce site). */
    #[Url(as: 'page', except: '')]
    public string $page = '';

    public bool $sent = false;

    /** Sans compte : l'adresse à laquelle répondre. */
    public string $contact = '';

    /** Piège à robots : un champ caché que personne ne remplit. */
    public string $website = '';

    public function send(): void
    {
        $guest = ! auth()->check();

        $this->validate([
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'contact' => $guest ? ['required', 'email', 'max:255'] : ['nullable'],
        ], [
            'message.required' => __('Décrivez ce qui s’est passé.'),
            'message.min' => __('Décrivez le problème en au moins :min caractères.'),
            'message.max' => __('Votre description ne doit pas dépasser :max caractères.'),
        ], ['contact' => __('adresse e-mail')]);

        // Un robot a rempli le champ caché : on fait comme si de rien n'était.
        if ($this->website !== '') {
            $this->reset('message', 'contact', 'website');
            $this->sent = true;

            return;
        }

        $key = $guest ? 'bug-report-guest:'.request()->ip() : 'bug-report:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, $guest ? 3 : 10)) {
            $this->addError('message', __('Vous avez envoyé beaucoup de signalements : réessayez dans une heure.'));

            return;
        }

        RateLimiter::hit($key, 3600);

        BugReport::create([
            'user_id' => auth()->id(),
            'message' => trim($this->message),
            'url' => preg_match('#^/(?![/\\\\])\S*$#', $this->page) ? mb_substr($this->page, 0, 2048) : null,
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 512),
            'locale' => app()->getLocale(),
            'version' => Changelog::version(),
            'contact_email' => $guest ? mb_strtolower(trim($this->contact)) : null,
        ]);

        $this->reset('message', 'contact');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.support.report-bug')
            ->layout(auth()->check() ? 'components.layouts.app' : 'components.layouts.public')
            ->title(__('Signaler un problème'));
    }
}
