<?php

namespace App\Livewire\Sessions;

use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\CharacterNote;
use App\Models\PlaySession;
use App\Models\TimelineEvent;
use Livewire\Component;

/**
 * Compte rendu d'une session : son résumé, ses notes regroupées par scène, les événements
 * joués, ce qui a été révélé ou donné, puis les notes des joueurs prises pendant la séance.
 */
class Show extends Component
{
    use SuggestsEntities;

    public Campaign $campaign;

    public PlaySession $playSession;

    public string $summary = '';

    public bool $editingSummary = false;

    public function mount(Campaign $campaign, PlaySession $playSession): void
    {
        $this->authorize('update', $campaign);
        abort_unless($playSession->campaign_id === $campaign->id, 404);

        $this->summary = (string) $playSession->summary;
    }

    public function saveSummary(): void
    {
        $this->authorize('update', $this->campaign);
        $this->validate(['summary' => ['nullable', 'string', 'max:20000']], attributes: ['summary' => __('résumé')]);

        $this->playSession->update(['summary' => trim($this->summary) ?: null]);
        $this->editingSummary = false;
    }

    public function render()
    {
        return view('livewire.sessions.show', [
            'notes' => $this->playSession->notes()->with('scene')->oldest()->oldest('id')->get(),
            'events' => TimelineEvent::query()->where('campaign_id', $this->campaign->id)->where('play_session_id', $this->playSession->id)->orderBy('position')->get(),
            'reveals' => CharacterGrant::query()
                ->where('play_session_id', $this->playSession->id)
                ->with(['character.entity', 'entity', 'document', 'rule', 'scene'])
                ->oldest()
                ->oldest('id')
                ->get(),
            // Notes prises par les joueurs pendant la séance, hormis celles qu'ils gardent pour eux.
            'playerNotes' => CharacterNote::query()
                ->where('play_session_id', $this->playSession->id)
                ->visibleTo(auth()->user(), $this->campaign)
                ->with(['character.entity', 'author'])
                ->oldest()
                ->oldest('id')
                ->get(),
        ])->title($this->playSession->label());
    }
}
