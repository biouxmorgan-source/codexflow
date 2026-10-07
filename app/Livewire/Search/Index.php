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
        $this->authorize('play', $campaign);
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
                ? array_diff_key(GlobalSearch::kinds(), ['knowledge' => true])
                : array_intersect_key(['entities' => __('Fiches connues'), 'knowledge' => __('Informations, objets et règles'), 'documents' => __('Documents'), 'notes' => __('Notes')], array_flip(GlobalSearch::PLAYER_KINDS)),
            'myCharacter' => $isGameMaster ? null : $this->campaign->playerCharacters()->active()->where('user_id', auth()->id())->with('entity')->first(),
            'entityTypes' => $isGameMaster
                ? EntityType::query()->availableTo($this->campaign->owner)->orderBy('name')->get(['id', 'name', 'key', 'user_id'])
                : collect(),
        ])->title($this->q !== ''
            ? __(':query · Recherche · :name', ['query' => $this->q, 'name' => $this->campaign->name])
            : __('Recherche · :name', ['name' => $this->campaign->name]));
    }
}
