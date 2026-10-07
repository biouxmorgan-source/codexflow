<?php

namespace App\Livewire\Graph;

use App\Models\Campaign;
use App\Models\PlayerCharacter;
use App\Support\RelationGraph;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Graphe des relations. Le MJ voit tout, ou « comme » un personnage ; un joueur voit
 * le réseau tel que son personnage le connaît.
 */
class Index extends Component
{
    public Campaign $campaign;

    #[Url(as: 'fiche', except: '')]
    public string $focus = '';

    #[Url(as: 'profondeur', except: 2)]
    public int $depth = 2;

    #[Url(as: 'type', except: '')]
    public string $typeId = '';

    /** Personnage à travers lequel le graphe est vu (MJ : « Voir comme… » ; joueur : un de ses personnages). */
    #[Url(as: 'comme', except: '')]
    public string $asCharacterId = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('play', $campaign);
    }

    #[Computed]
    public function isGameMaster(): bool
    {
        return $this->campaign->isGameMaster(auth()->user());
    }

    /** @return Collection<int, PlayerCharacter> */
    #[Computed]
    public function myCharacters(): Collection
    {
        return $this->campaign->playerCharacters()->active()->where('user_id', auth()->id())->with('entity')->orderBy('id')->get();
    }

    /**
     * Personnage à travers lequel on regarde. Vérifié à chaque requête : un joueur ne peut
     * choisir qu'un de ses personnages, le MJ n'importe quel personnage de la campagne.
     */
    #[Computed]
    public function character(): ?PlayerCharacter
    {
        if ($this->isGameMaster) {
            if ($this->asCharacterId === '') {
                return null;
            }
            abort_unless(ctype_digit($this->asCharacterId), 404);

            return $this->campaign->playerCharacters()->with('entity')->findOr((int) $this->asCharacterId, fn () => abort(404));
        }

        return $this->asCharacterId !== '' && ctype_digit($this->asCharacterId)
            ? $this->myCharacters->firstWhere('id', (int) $this->asCharacterId) ?? abort(404)
            : $this->myCharacters->first();
    }

    #[Computed]
    public function graph(): ?RelationGraph
    {
        if (! $this->isGameMaster && $this->character === null) {
            return null;
        }

        return RelationGraph::build(
            $this->campaign,
            $this->character,
            ctype_digit($this->focus) ? (int) $this->focus : null,
            $this->depth,
            ctype_digit($this->typeId) ? (int) $this->typeId : null,
        );
    }

    public function focusOn(?int $id): void
    {
        $this->focus = $id === null ? '' : (string) $id;
    }

    public function render()
    {
        return view('livewire.graph.index', [
            'characters' => $this->isGameMaster ? $this->campaign->playerCharacters()->active()->with('entity')->orderBy('id')->get() : collect(),
        ])->title(__('Graphe des relations · :name', ['name' => $this->campaign->name]));
    }
}
