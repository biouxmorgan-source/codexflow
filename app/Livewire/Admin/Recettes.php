<?php

namespace App\Livewire\Admin;

use App\Models\Recette;
use App\Models\RecetteItem;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Cahiers de recette conservés version après version, pour garder la trace des tests. */
class Recettes extends Component
{
    #[Url(as: 'cahier')]
    public ?int $recetteId = null;

    #[Url(except: '')]
    public string $search = '';

    /** Seulement les lignes sous 100 : ce qui reste à améliorer. */
    #[Url(except: false)]
    public bool $belowTarget = false;

    public function mount(): void
    {
        $this->authorize('admin');

        $this->recetteId ??= Recette::latest('tested_on')->latest('id')->value('id');
    }

    /** @return Collection<int, Recette> */
    #[Computed]
    public function recettes(): Collection
    {
        return Recette::withCount('items')->latest('tested_on')->latest('id')->get();
    }

    #[Computed]
    public function recette(): ?Recette
    {
        return $this->recetteId ? Recette::find($this->recetteId) : null;
    }

    /** @return \Illuminate\Support\Collection<string, Collection<int, RecetteItem>> lignes par zone */
    #[Computed]
    public function sections(): \Illuminate\Support\Collection
    {
        if (! $this->recette) {
            return collect();
        }

        $term = '%'.addcslashes($this->search, '%_\\').'%';

        return $this->recette->items()
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('feature', 'ilike', $term)->orWhere('gaps', 'ilike', $term)->orWhere('tests', 'ilike', $term)))
            ->when($this->belowTarget, fn ($query) => $query->where('score', '<', 100))
            ->get()
            ->groupBy('section');
    }

    public function render()
    {
        return view('livewire.admin.recettes')->title(__('Administration · Recettes'));
    }
}
