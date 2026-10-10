<?php

namespace App\Livewire\Feedback;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\FeedbackRequest;
use App\Models\PlayerCharacter;
use App\Support\FeedbackRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Avis des joueurs, côté joueur : une note de 1 à 5 étoiles, ce qui a plu, ce qui pourrait
 * être mieux. Une seule réponse par demande.
 */
class Answer extends Component
{
    public Campaign $campaign;

    public FeedbackRequest $feedbackRequest;

    public ?int $rating = null;

    public string $liked = '';

    public string $improve = '';

    public function mount(Campaign $campaign, FeedbackRequest $feedbackRequest): void
    {
        abort_unless($feedbackRequest->campaign_id === $campaign->id, 404);
        abort_unless($campaign->roleOf(auth()->user()) === CampaignRole::Player, 403);
    }

    #[Computed]
    public function answered(): bool
    {
        return $this->feedbackRequest->responses()->where('user_id', auth()->id())->exists();
    }

    #[Computed]
    public function character(): ?PlayerCharacter
    {
        return $this->campaign->playerCharacters()->active()->where('user_id', auth()->id())->first();
    }

    public function save(): void
    {
        abort_unless($this->campaign->roleOf(auth()->user()) === CampaignRole::Player, 403);

        $data = $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'liked' => ['nullable', 'string', 'max:2000'],
            'improve' => ['nullable', 'string', 'max:2000'],
        ], ['rating.required' => __('Choisissez une note de 1 à 5 étoiles.')], ['rating' => __('note'), 'liked' => __('ce qui vous a plu'), 'improve' => __('ce qui pourrait être mieux')]);

        if (! $this->feedbackRequest->fresh()->canAnswer(auth()->user())) {
            $this->addError('rating', __('Cette demande est close ou vous y avez déjà répondu.'));

            return;
        }

        FeedbackRequests::answer($this->feedbackRequest, auth()->user(), [
            'rating' => (int) $data['rating'],
            'liked' => trim((string) $data['liked']) ?: null,
            'improve' => trim((string) $data['improve']) ?: null,
        ]);

        unset($this->answered);
    }

    public function render()
    {
        return view('livewire.feedback.answer')->title(__('Votre avis · :name', ['name' => $this->campaign->name]));
    }
}
