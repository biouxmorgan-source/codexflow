<?php

namespace App\Livewire\Search;

use App\Models\Campaign;
use App\Support\Search\GlobalSearch;
use App\Support\Search\SearchResult;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Recherche depuis l'accueil, dans toutes les campagnes de la personne (et donc leurs mondes et jeux).
 * Chaque campagne est cherchée avec ses propres droits : MJ ou joueur, comme sur sa page de recherche.
 */
class Everywhere extends Component
{
    private const PER_CAMPAIGN = 6;

    #[Url]
    public string $q = '';

    /**
     * @return Collection<int, array{campaign: Campaign, results: Collection<int, array{kind: string, result: SearchResult}>, total: int}>
     */
    #[Computed]
    public function groups(): Collection
    {
        if (GlobalSearch::words($this->q) === []) {
            return collect();
        }

        $user = auth()->user();
        $kinds = GlobalSearch::kinds();

        return $user->campaigns()
            ->orderByRaw("status = 'archived'")
            ->orderByRaw('lower(name)')
            ->limit(30)
            ->get()
            ->filter(fn (Campaign $campaign) => $user->can('play', $campaign))
            ->map(function (Campaign $campaign) use ($user, $kinds) {
                $found = collect((new GlobalSearch($campaign, $user, $this->q))->run())
                    ->flatMap(fn (Collection $items, string $kind) => $items->map(fn (SearchResult $result) => ['kind' => $kinds[$kind] ?? $kind, 'result' => $result]));

                return ['campaign' => $campaign, 'results' => $found->take(self::PER_CAMPAIGN), 'total' => $found->count()];
            })
            ->filter(fn (array $group) => $group['total'] > 0)
            ->values();
    }

    public function render()
    {
        return view('livewire.search.everywhere')
            ->title($this->q !== '' ? __(':query · Recherche', ['query' => $this->q]) : __('Recherche'));
    }
}
