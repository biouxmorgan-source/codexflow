<?php

namespace App\Livewire\Library;

use App\Livewire\Concerns\EditsLibraryImage;
use App\Models\Campaign;
use App\Models\GameSystem;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/** Page d'un jeu : sa description, ses campagnes, ses règles, documents et champs communs. */
class GameShow extends Component
{
    use EditsLibraryImage;

    public GameSystem $gameSystem;

    public string $name = '';

    public string $description = '';

    public bool $editing = false;

    public function mount(GameSystem $gameSystem): void
    {
        abort_unless($gameSystem->user_id === auth()->id(), 403);

        $this->name = $gameSystem->name;
        $this->description = (string) $gameSystem->description;
    }

    /** @return Collection<int, Campaign> */
    #[Computed]
    public function campaigns(): Collection
    {
        return $this->gameSystem->campaigns()->with('world')->orderByRaw("status = 'archived'")->latest('updated_at')->get();
    }

    public function save(): void
    {
        abort_unless($this->gameSystem->user_id === auth()->id(), 403);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
        ], attributes: ['name' => __('nom')]);

        $this->gameSystem->update(['name' => trim($this->name), 'description' => trim($this->description) ?: null]);
        $this->editing = false;
    }

    protected function libraryItem(): GameSystem
    {
        return $this->gameSystem;
    }

    public function render()
    {
        // Les pages de règles et de documents s'ouvrent dans une campagne du jeu.
        $campaign = $this->campaigns->first();

        return view('livewire.library.game-show', [
            'campaign' => $campaign,
            'rules' => $this->gameSystem->rules()->orderByRaw('lower(title)')->get(),
            'documents' => $this->gameSystem->documents()->orderByRaw('lower(title)')->get(),
            'fieldGroups' => $this->gameSystem->fieldDefinitions()->get()->groupBy(fn ($field) => $field->groupLabel())->map->count(),
        ])->title($this->gameSystem->name);
    }
}
