<?php

namespace App\Livewire\Sessions;

use App\Models\Campaign;
use App\Models\PlaySession;
use Livewire\Component;

/**
 * Compte rendu d'une session : ses notes, dans l'ordre, regroupées par scène.
 */
class Show extends Component
{
    public Campaign $campaign;

    public PlaySession $playSession;

    public function mount(Campaign $campaign, PlaySession $playSession): void
    {
        $this->authorize('update', $campaign);
        abort_unless($playSession->campaign_id === $campaign->id, 404);
    }

    public function render()
    {
        return view('livewire.sessions.show', [
            'notes' => $this->playSession->notes()->with('scene')->oldest()->oldest('id')->get(),
        ])->title($this->playSession->label());
    }
}
