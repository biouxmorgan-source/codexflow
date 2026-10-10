<?php

namespace App\Livewire\Feedback;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\FeedbackRequest;
use App\Models\PlaySession;
use App\Support\FeedbackRequests;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Avis des joueurs, côté MJ : demander une note en étoiles et des retours en fin de séance
 * ou de campagne, puis lire les réponses. Une demande anonyme ne révèle jamais les auteurs.
 */
class Index extends Component
{
    public Campaign $campaign;

    /** Identifiant de la séance visée, ou chaîne vide pour toute la campagne. */
    #[Url(as: 'seance')]
    public string $subject = '';

    public bool $anonymous = true;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);

        if ($this->subject !== '' && ! $campaign->playSessions()->whereKey((int) $this->subject)->exists()) {
            $this->subject = '';
        }
    }

    /** @return Collection<int, PlaySession> */
    #[Computed]
    public function sessions(): Collection
    {
        return $this->campaign->playSessions()->orderByDesc('number')->get();
    }

    /** @return Collection<int, FeedbackRequest> */
    #[Computed]
    public function requests(): Collection
    {
        return $this->campaign->feedbackRequests()
            ->with(['playSession', 'responses' => fn ($q) => $q->oldest()->oldest('id'), 'responses.author'])
            ->latest()
            ->latest('id')
            ->get();
    }

    #[Computed]
    public function playerCount(): int
    {
        return $this->campaign->members()->wherePivot('role', CampaignRole::Player->value)->count();
    }

    public function ask(): void
    {
        $this->authorize('update', $this->campaign);

        $session = $this->subject === '' ? null : $this->campaign->playSessions()->findOrFail((int) $this->subject);

        $alreadyOpen = $this->campaign->feedbackRequests()
            ->whereNull('closed_at')
            ->where('play_session_id', $session?->id)
            ->exists();
        if ($alreadyOpen) {
            $this->addError('subject', __('Une demande est déjà ouverte pour ce sujet.'));

            return;
        }

        FeedbackRequests::ask($this->campaign, $session, $this->anonymous, auth()->user());

        $this->anonymous = true;
        unset($this->requests);
        session()->flash('status', __('Demande envoyée aux joueurs.'));
    }

    public function close(int $id): void
    {
        $this->request($id)->update(['closed_at' => now()]);
        unset($this->requests);
    }

    public function reopen(int $id): void
    {
        $this->request($id)->update(['closed_at' => null]);
        unset($this->requests);
    }

    public function delete(int $id): void
    {
        $this->request($id)->delete();
        unset($this->requests);
    }

    private function request(int $id): FeedbackRequest
    {
        $this->authorize('update', $this->campaign);

        return $this->campaign->feedbackRequests()->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.feedback.index')->title(__('Avis des joueurs · :name', ['name' => $this->campaign->name]));
    }
}
