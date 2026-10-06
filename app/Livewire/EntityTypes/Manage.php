<?php

namespace App\Livewire\EntityTypes;

use App\Models\EntityType;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Types de fiche : les six standards, plus ceux que le MJ ajoute (Faction, Divinité, Vaisseau…).
 */
class Manage extends Component
{
    public string $name = '';

    public ?int $editingId = null;

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()
            ->availableTo(auth()->user())
            ->withCount(['entities', 'fieldDefinitions'])
            ->orderByRaw('user_id is not null')
            ->orderBy('name')
            ->get();
    }

    public function edit(int $id): void
    {
        $type = $this->own($id);
        $this->authorize('update', $type);

        $this->resetValidation();
        $this->editingId = $type->id;
        $this->name = $type->name;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('name', 'editingId');
    }

    public function save(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:60']], attributes: ['name' => 'nom']);

        $name = trim($this->name);
        $taken = $this->types->contains(fn (EntityType $type) => $type->id !== $this->editingId && mb_strtolower($type->name) === mb_strtolower($name));

        if ($taken) {
            $this->addError('name', 'Ce type existe déjà.');

            return;
        }

        $type = $this->editingId ? $this->own($this->editingId) : new EntityType;

        if ($type->exists) {
            $this->authorize('update', $type);
        } else {
            $type->user_id = auth()->id();
        }

        $type->name = $name;
        $type->save();

        $this->cancel();
        unset($this->types);
    }

    public function delete(int $id): void
    {
        $type = $this->own($id);
        $this->authorize('delete', $type);

        if ($type->entities()->exists()) {
            $this->addError('delete', 'Le type « '.$type->name.' » est encore utilisé par des fiches : changez-les de type avant de le supprimer.');

            return;
        }

        $type->delete();

        unset($this->types);
    }

    private function own(int $id): EntityType
    {
        return EntityType::query()->where('user_id', auth()->id())->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.entity-types.manage')->title('Types de fiche');
    }
}
