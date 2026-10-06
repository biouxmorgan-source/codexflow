<?php

namespace App\Livewire\Campaigns;

use App\Actions\Campaigns\CreateCampaign;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mes campagnes')]
class Index extends Component
{
    public bool $creating = false;

    public string $name = '';

    public string $description = '';

    /** Identifiant d'un jeu existant, ou « new » pour en créer un. */
    public string $gameChoice = 'new';

    public string $newGameName = '';

    /** Identifiant d'un monde existant, « new » pour en créer un, vide pour aucun. */
    public string $worldChoice = '';

    public string $newWorldName = '';

    public function mount(): void
    {
        $firstGame = $this->gameSystems->first();

        if ($firstGame !== null) {
            $this->gameChoice = (string) $firstGame->getKey();
        }
    }

    /** @return Collection<int, Campaign> */
    #[Computed]
    public function campaigns(): Collection
    {
        return Campaign::query()
            ->visibleTo(auth()->user())
            ->with(['gameSystem', 'world', 'members' => fn ($q) => $q->whereKey(auth()->id())])
            ->orderByRaw("status = 'archived'")
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function gameSystems(): Collection
    {
        return auth()->user()->gameSystems()->orderBy('name')->get();
    }

    #[Computed]
    public function worlds(): Collection
    {
        return auth()->user()->worlds()->orderBy('name')->get();
    }

    public function create(CreateCampaign $createCampaign): void
    {
        $user = auth()->user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'gameChoice' => ['required', Rule::in(['new', ...$this->gameSystems->modelKeys()])],
            'newGameName' => ['required_if:gameChoice,new', 'nullable', 'string', 'max:255'],
            'worldChoice' => ['nullable', Rule::in(['', 'new', ...$this->worlds->modelKeys()])],
            'newWorldName' => ['required_if:worldChoice,new', 'nullable', 'string', 'max:255'],
        ], attributes: [
            'gameChoice' => 'jeu',
            'worldChoice' => 'monde',
            'newGameName' => 'nom du nouveau jeu',
            'newWorldName' => 'nom du nouveau monde',
        ]);

        $createCampaign->handle($user, [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'game_system_id' => $this->gameChoice === 'new' ? null : (int) $this->gameChoice,
            'new_game_name' => $this->gameChoice === 'new' ? $this->newGameName : null,
            'world_id' => in_array($this->worldChoice, ['', 'new'], true) ? null : (int) $this->worldChoice,
            'new_world_name' => $this->worldChoice === 'new' ? $this->newWorldName : null,
        ]);

        $this->reset(['creating', 'name', 'description', 'newGameName', 'worldChoice', 'newWorldName']);
        unset($this->campaigns, $this->gameSystems, $this->worlds);
        $this->mount();
    }

    public function render()
    {
        return view('livewire.campaigns.index');
    }
}
