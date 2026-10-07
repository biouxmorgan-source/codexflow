<?php

namespace App\Livewire\Search;

use App\Models\Campaign;
use App\Models\EntityType;
use App\Support\Search\GlobalSearch;
use App\Support\Search\SearchResult;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Recherche globale d'une campagne. Les droits sont appliqués par GlobalSearch, côté serveur.
 */
class Index extends Component
{
    public Campaign $campaign;

    #[Url]
    public string $q = '';

    #[Url]
    public string $kind = '';

    #[Url(as: 'type')]
    public string $entityTypeId = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('view', $campaign);
    }

    #[Computed]
    public function search(): GlobalSearch
    {
        return new GlobalSearch($this->campaign, auth()->user(), $this->q);
    }

    /** @return array<string, Collection<int, SearchResult>> */
    #[Computed]
    public function results(): array
    {
        $kinds = array_key_exists($this->kind, GlobalSearch::KINDS) ? [$this->kind] : null;
        $typeId = ctype_digit($this->entityTypeId) ? (int) $this->entityTypeId : null;

        return $this->search->run($kinds, $typeId);
    }

    public function render()
    {
        $isGameMaster = $this->campaign->isGameMaster(auth()->user());

        return view('livewire.search.index', [
            'isGameMaster' => $isGameMaster,
            'kinds' => $isGameMaster
                ? array_diff_key(GlobalSearch::KINDS, ['knowledge' => true])
                : array_intersect_key(['entities' => 'Fiches connues', 'knowledge' => 'Informations, objets et règles', 'documents' => 'Documents', 'notes' => 'Notes'], array_flip(GlobalSearch::PLAYER_KINDS)),
            'myCharacter' => $isGameMaster ? null : $this->campaign->playerCharacters()->active()->where('user_id', auth()->id())->with('entity')->first(),
            'entityTypes' => $isGameMaster
                ? EntityType::query()->availableTo(auth()->user())->orderBy('name')->get(['id', 'name'])
                : collect(),
        ])->title(($this->q !== '' ? $this->q.' · ' : '').'Recherche · '.$this->campaign->name);
    }
}
