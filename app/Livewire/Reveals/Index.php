<?php

namespace App\Livewire\Reveals;

use App\Actions\Characters\GiveToCharacters;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Historique des révélations : qui a révélé ou donné quoi, à quel personnage, quand,
 * pendant quelle séance et quelle scène. Le MJ peut annuler une révélation ; l'annulation
 * est notée au journal.
 */
class Index extends Component
{
    use WithPagination;

    public Campaign $campaign;

    #[Url(as: 'personnage', except: '')]
    public string $character = '';

    #[Url(as: 'session', except: '')]
    public string $session = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['character', 'session'], true)) {
            $this->resetPage();
        }
    }

    /** @return LengthAwarePaginator<int, CharacterGrant> */
    #[Computed]
    public function reveals(): LengthAwarePaginator
    {
        return CharacterGrant::query()
            ->whereHas('character', fn ($q) => $q->where('campaign_id', $this->campaign->id))
            // Ce que le joueur a noté lui-même n'est pas une révélation.
            ->where('added_by_player', false)
            ->when(ctype_digit($this->character), fn ($q) => $q->where('player_character_id', (int) $this->character))
            ->when(ctype_digit($this->session), fn ($q) => $q->where('play_session_id', (int) $this->session))
            ->with(['character.entity', 'entity', 'document', 'rule', 'secret', 'giver', 'playSession', 'scene'])
            ->latest('id')
            ->paginate(50);
    }

    public function undo(int $grantId): void
    {
        $this->authorize('update', $this->campaign);

        $grant = CharacterGrant::query()
            ->whereHas('character', fn ($q) => $q->where('campaign_id', $this->campaign->id))
            ->findOrFail($grantId);

        GiveToCharacters::revoke($grant);
        unset($this->reveals);
    }

    public function render()
    {
        return view('livewire.reveals.index', [
            'characters' => $this->campaign->playerCharacters()->with('entity')->get()->sortBy(fn ($c) => mb_strtolower($c->entity->name)),
            'sessions' => $this->campaign->playSessions()->latest('number')->get(),
        ])->title(__('Révélations · :name', ['name' => $this->campaign->name]));
    }
}
