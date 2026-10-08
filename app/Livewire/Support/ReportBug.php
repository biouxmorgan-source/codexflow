<?php

namespace App\Livewire\Support;

use App\Models\BugReport;
use App\Support\Changelog;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * « Signaler un problème » : le message arrive aux administrateurs, avec la page concernée.
 */
class ReportBug extends Component
{
    public string $message = '';

    /** Page où le problème est apparu (chemin de ce site). */
    #[Url(as: 'page', except: '')]
    public string $page = '';

    public bool $sent = false;

    public function send(): void
    {
        $this->validate(['message' => ['required', 'string', 'min:10', 'max:5000']], [
            'message.required' => __('Décrivez ce qui s’est passé.'),
            'message.min' => __('Décrivez le problème en au moins :min caractères.'),
            'message.max' => __('Votre description ne doit pas dépasser :max caractères.'),
        ]);

        $key = 'bug-report:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 10)) {
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
        ]);

        $this->reset('message');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.support.report-bug')->title(__('Signaler un problème'));
    }
}
