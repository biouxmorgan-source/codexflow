<?php

namespace App\Livewire\Library;

use App\Livewire\Concerns\EditsLibraryImage;
use App\Models\Campaign;
use App\Models\EntityType;
use App\Models\GameSystem;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
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
        // Le propriétaire, ou un co-MJ d'une de ses campagnes, en lecture seule.
        $this->authorize('view', $gameSystem);

        $this->name = $gameSystem->name;
        $this->description = (string) $gameSystem->description;
    }

    /** @return Collection<int, Campaign> */
    #[Computed]
    public function campaigns(): Collection
    {
        return $this->gameSystem->campaigns()
            ->when($this->gameSystem->user_id !== auth()->id(), fn ($query) => $query->runBy(auth()->user()))->with('world')->orderByRaw("status = 'archived'")->latest('updated_at')->get();
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

    /**
     * L'éditeur de la description demande des fiches à lier quand on tape « [[ » : hors campagne, aucune.
     *
     * @return list<array{id: int, name: string, type: string}>
     */
    #[Renderless]
    public function suggestEntities(string $query): array
    {
        return [];
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
            'canEdit' => $this->gameSystem->user_id === auth()->id(),
            'campaign' => $campaign,
            'rules' => $this->gameSystem->rules()->orderByRaw('lower(title)')->get(),
            'documents' => $this->gameSystem->documents()->orderByRaw('lower(title)')->get(),
            'fieldGroups' => $this->gameSystem->fieldDefinitions()->get()->groupBy(fn ($field) => $field->groupLabel())->map->count(),
            // Les types de fiche sont communs à tous les jeux du MJ.
            'types' => EntityType::availableTo($this->gameSystem->owner)->get()->sortBy(fn (EntityType $type) => [$type->isStandard() ? 0 : 1, mb_strtolower($type->name)]),
        ])->title($this->gameSystem->name);
    }
}
