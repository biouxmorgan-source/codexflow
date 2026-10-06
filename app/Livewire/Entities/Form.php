<?php

namespace App\Livewire\Entities;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Création et modification d'une fiche, toujours depuis le contexte d'une campagne.
 */
class Form extends Component
{
    public Campaign $campaign;

    public ?Entity $entity = null;

    public string $entityTypeId = '';

    public string $name = '';

    public string $summary = '';

    public string $description = '';

    public string $gmNotes = '';

    /** « world » : réutilisable dans toutes les campagnes du monde ; « campaign » : propre à cette campagne. */
    public string $scope = 'campaign';

    public function mount(Campaign $campaign, ?Entity $entity = null): void
    {
        $this->authorize('update', $campaign);

        if ($entity?->exists) {
            abort_unless($campaign->availableEntities()->whereKey($entity->getKey())->exists(), 404);
            $this->authorize('update', $entity);

            $this->entity = $entity;
            $this->entityTypeId = (string) $entity->entity_type_id;
            $this->name = $entity->name;
            $this->summary = (string) $entity->summary;
            $this->description = (string) $entity->description;
            $this->gmNotes = (string) $entity->gm_notes;
            $this->scope = $entity->isWorldEntity() ? 'world' : 'campaign';

            return;
        }

        $this->entity = null;
        $this->entityTypeId = (string) $this->types->first()?->getKey();
        $this->scope = $campaign->world_id ? 'world' : 'campaign';
    }

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()->availableTo(auth()->user())->orderBy('id')->get();
    }

    public function save(): void
    {
        $this->validate([
            'entityTypeId' => ['required', Rule::in($this->types->modelKeys())],
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'gmNotes' => ['nullable', 'string', 'max:20000'],
            'scope' => ['required', Rule::in($this->campaign->world_id ? ['world', 'campaign'] : ['campaign'])],
        ], attributes: [
            'entityTypeId' => 'type',
            'summary' => 'résumé',
            'gmNotes' => 'notes MJ',
            'scope' => 'portée',
        ]);

        $entity = $this->entity ?? new Entity;
        $entity->fill([
            'entity_type_id' => (int) $this->entityTypeId,
            'name' => $this->name,
            'summary' => $this->summary ?: null,
            'description' => $this->description ?: null,
            'gm_notes' => $this->gmNotes ?: null,
        ]);

        if (! $entity->exists) {
            $entity->owner()->associate(auth()->user());

            if ($this->scope === 'world') {
                $entity->world()->associate($this->campaign->world);
            } else {
                $entity->campaign()->associate($this->campaign);
            }
        }

        $entity->save();

        $this->redirectRoute('entities.show', [$this->campaign, $entity], navigate: true);
    }

    public function render()
    {
        return view('livewire.entities.form')
            ->title($this->entity ? 'Modifier '.$this->entity->name : 'Nouvelle entité');
    }
}
