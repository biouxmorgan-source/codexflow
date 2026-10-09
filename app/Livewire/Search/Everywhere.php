<?php

namespace App\Livewire\Search;

use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\GameSystem;
use App\Models\Rule;
use App\Models\World;
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

    /**
     * Mondes et jeux de la personne rattachés à aucune campagne : leur nom, leur description,
     * les fiches du monde, les règles du jeu et leurs documents. Chaque résultat mène à leur page.
     *
     * @return Collection<int, array{kind: string, title: string, subtitle: string, url: string}>
     */
    #[Computed]
    public function library(): Collection
    {
        $words = GlobalSearch::words($this->q);

        if ($words === []) {
            return collect();
        }

        $user = auth()->user();
        $worlds = $user->worlds()->whereDoesntHave('campaigns')->get(['id', 'name', 'description']);
        $games = $user->gameSystems()->whereDoesntHave('campaigns')->get(['id', 'name', 'description']);
        $match = fn ($query, string $haystack) => GlobalSearch::whereWords($query, $haystack, $words);
        $worldUrl = fn (int $id) => route('worlds.show', $id);
        $gameUrl = fn (int $id) => route('games.show', $id);
        $worldName = fn (int $id) => __('Monde : :name', ['name' => $worlds->firstWhere('id', $id)?->name]);
        $gameName = fn (int $id) => $games->firstWhere('id', $id)?->name ?? '';

        $results = collect();
        if ($worlds->isNotEmpty()) {
            $results = $results
                ->concat(World::whereKey($worlds->modelKeys())->tap(fn ($q) => $match($q, "concat_ws(' ', worlds.name, worlds.description)"))->get()
                    ->map(fn (World $world) => ['kind' => __('Monde'), 'title' => $world->name, 'subtitle' => __('Sans campagne'), 'url' => $worldUrl($world->id)]))
                ->concat(Entity::whereIn('world_id', $worlds->modelKeys())->tap(fn ($q) => $match($q, "concat_ws(' ', entities.name, entities.summary, entities.description, entities.gm_notes)"))->orderBy('name')->limit(20)->get()
                    ->map(fn (Entity $entity) => ['kind' => __('Fiche'), 'title' => $entity->name, 'subtitle' => $worldName($entity->world_id), 'url' => $worldUrl($entity->world_id)]))
                ->concat(Document::whereIn('world_id', $worlds->modelKeys())->tap(fn ($q) => $match($q, "concat_ws(' ', documents.title, documents.description)"))->limit(20)->get()
                    ->map(fn (Document $document) => ['kind' => __('Document'), 'title' => $document->title, 'subtitle' => $worldName($document->world_id), 'url' => $worldUrl($document->world_id)]));
        }
        if ($games->isNotEmpty()) {
            $results = $results
                ->concat(GameSystem::whereKey($games->modelKeys())->tap(fn ($q) => $match($q, "concat_ws(' ', game_systems.name, game_systems.description)"))->get()
                    ->map(fn (GameSystem $game) => ['kind' => __('Jeu'), 'title' => $game->name, 'subtitle' => __('Sans campagne'), 'url' => $gameUrl($game->id)]))
                ->concat(Rule::whereIn('game_system_id', $games->modelKeys())->tap(fn ($q) => $match($q, "concat_ws(' ', rules.title, rules.summary, rules.procedure, rules.gm_notes)"))->limit(20)->get()
                    ->map(fn (Rule $rule) => ['kind' => __('Règle'), 'title' => $rule->title, 'subtitle' => $gameName($rule->game_system_id), 'url' => $gameUrl($rule->game_system_id)]))
                ->concat(Document::whereIn('game_system_id', $games->modelKeys())->tap(fn ($q) => $match($q, "concat_ws(' ', documents.title, documents.description)"))->limit(20)->get()
                    ->map(fn (Document $document) => ['kind' => __('Document'), 'title' => $document->title, 'subtitle' => $gameName($document->game_system_id), 'url' => $gameUrl($document->game_system_id)]));
        }

        return $results->values();
    }

    public function render()
    {
        return view('livewire.search.everywhere')
            ->title($this->q !== '' ? __(':query · Recherche', ['query' => $this->q]) : __('Recherche'));
    }
}
