<?php

namespace App\Livewire\Library;

use App\Models\Campaign;
use App\Models\World;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/** Page d'un monde : sa description, ses campagnes, ses fiches et documents réutilisables. */
class WorldShow extends Component
{
    public World $world;

    public string $name = '';

    public string $description = '';

    public bool $editing = false;

    public function mount(World $world): void
    {
        abort_unless($world->user_id === auth()->id(), 403);

        $this->name = $world->name;
        $this->description = (string) $world->description;
    }

    /** @return Collection<int, Campaign> */
    #[Computed]
    public function campaigns(): Collection
    {
        return $this->world->campaigns()->with('gameSystem')->orderByRaw("status = 'archived'")->latest('updated_at')->get();
    }

    public function save(): void
    {
        abort_unless($this->world->user_id === auth()->id(), 403);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
        ], attributes: ['name' => __('nom')]);

        $this->world->update(['name' => trim($this->name), 'description' => trim($this->description) ?: null]);
        $this->editing = false;
    }

    public function render()
    {
        return view('livewire.library.world-show', [
            // Les fiches du monde s'ouvrent dans l'une de ses campagnes.
            'campaign' => $this->campaigns->first(),
            'entities' => $this->world->entities()->with('type')->orderByRaw('lower(name)')->get()->groupBy(fn ($entity) => $entity->type->name),
            'documents' => $this->world->documents()->orderByRaw('lower(title)')->get(),
        ])->title($this->world->name);
    }
}
