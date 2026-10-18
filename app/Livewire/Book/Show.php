<?php

namespace App\Livewire\Book;

use App\Models\Campaign;
use App\Support\AdventureBook;
use App\Support\Ai\AiProviders;
use App\Support\Ai\Clients\AiRequestFailed;
use App\Support\CampaignFeatures;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Livre des aventures : les scènes jouées et les résumés des séances, assemblés en un document
 * à lire, imprimer ou enregistrer en PDF. Le MJ peut aussi en garder une version rédigée, à la main
 * ou par une IA (texte à coller, ou sa propre clé, à ses frais). Réservé au MJ : il contient ses notes.
 */
class Show extends Component
{
    public Campaign $campaign;

    /** Version affichée : « assemblee » (construite d'après les scènes) ou « redigee ». */
    #[Url(as: 'version')]
    public string $version = 'assemblee';

    public string $narrative = '';

    public bool $editing = false;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);

        $this->narrative = (string) $campaign->adventure_book;

        if (! in_array($this->version, ['assemblee', 'redigee'], true)) {
            $this->version = 'assemblee';
        }
    }

    #[Computed]
    public function book(): AdventureBook
    {
        return new AdventureBook($this->campaign);
    }

    /** L'IA est-elle proposée ici ? (fonction active dans la campagne et comprise dans la formule) */
    #[Computed]
    public function aiUsable(): bool
    {
        return CampaignFeatures::usable($this->campaign, 'ai');
    }

    /** Fournisseur de la clé personnelle du MJ, ou null : seul le texte à coller est proposé. */
    #[Computed]
    public function directProvider(): ?string
    {
        return $this->aiUsable && auth()->user()->hasAiKey() ? AiProviders::name(auth()->user()->ai_provider) : null;
    }

    public function edit(): void
    {
        $this->authorize('update', $this->campaign);

        $this->version = 'redigee';
        $this->editing = true;
    }

    public function saveNarrative(): void
    {
        $this->authorize('update', $this->campaign);
        $this->validate(['narrative' => ['nullable', 'string', 'max:200000']], attributes: ['narrative' => __('livre rédigé')]);

        $this->campaign->forceFill(['adventure_book' => trim($this->narrative) ?: null])->save();
        $this->narrative = (string) $this->campaign->adventure_book;
        $this->editing = false;
        $this->version = 'redigee';
    }

    /** Fait rédiger le livre par l'IA du MJ, avec sa clé et à ses frais ; le résultat reste modifiable. */
    public function writeWithAi(): void
    {
        $this->authorize('update', $this->campaign);
        CampaignFeatures::ensure($this->campaign, 'ai');
        $this->resetValidation();

        $client = AiProviders::clientFor(auth()->user());

        if ($client === null) {
            $this->addError('direct', __('Enregistrez d’abord votre clé d’API dans vos préférences.'));

            return;
        }

        if ($this->book->isEmpty()) {
            $this->addError('direct', __('Rien à raconter pour l’instant : aucune scène jouée, aucun résumé de séance.'));

            return;
        }

        // Chaque appel coûte au MJ : un double clic ne doit pas le facturer deux fois.
        $key = 'ai-book:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('direct', __('Trop de demandes à la suite : patientez une minute.'));

            return;
        }

        RateLimiter::hit($key, 60);
        @set_time_limit(300);

        try {
            $this->narrative = trim($client->complete($this->book->prompt()));
        } catch (AiRequestFailed $e) {
            $this->addError('direct', $e->getMessage());

            return;
        }

        $this->saveNarrative();
    }

    public function render()
    {
        return view('livewire.book.show', [
            'chapters' => $this->book->chapters(),
            'sessions' => $this->book->sessions(),
        ])->title(__('Livre des aventures'));
    }
}
