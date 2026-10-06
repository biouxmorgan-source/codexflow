<?php

namespace App\Livewire\Fields;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\GameSystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Champs libres d'un jeu, ouverts depuis une campagne de ce jeu.
 */
class Manage extends Component
{
    public Campaign $campaign;

    public ?int $editingId = null;

    public string $name = '';

    public string $group = '';

    public string $type = 'text';

    public string $options = '';

    public string $zone = 'public';

    public string $entityTypeId = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
        $this->authorize('update', $campaign->gameSystem);
    }

    #[Computed]
    public function gameSystem(): GameSystem
    {
        return $this->campaign->gameSystem;
    }

    /** @return Collection<int, FieldDefinition> */
    #[Computed]
    public function definitions(): Collection
    {
        return $this->gameSystem->fieldDefinitions()->with('entityType')->ordered()->get();
    }

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()->availableTo(auth()->user())->orderBy('id')->get();
    }

    /** @return list<string> */
    #[Computed]
    public function groups(): array
    {
        return $this->definitions->pluck('group')->filter()->unique()->values()->all();
    }

    public function edit(int $id): void
    {
        $definition = $this->find($id);

        $this->resetValidation();
        $this->editingId = $definition->id;
        $this->name = $definition->name;
        $this->group = (string) $definition->group;
        $this->type = $definition->type->value;
        $this->options = implode("\n", $definition->options ?? []);
        $this->zone = $definition->zone->value;
        $this->entityTypeId = (string) $definition->entity_type_id;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'name', 'type', 'options', 'zone', 'entityTypeId');
    }

    public function save(): void
    {
        $this->authorize('update', $this->gameSystem);

        $this->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('field_definitions', 'name')
                    ->where('game_system_id', $this->gameSystem->id)
                    ->where('entity_type_id', $this->entityTypeId ?: null)
                    ->ignore($this->editingId),
            ],
            'group' => ['nullable', 'string', 'max:100'],
            'type' => ['required', Rule::enum(FieldType::class)],
            'options' => [Rule::requiredIf($this->type === FieldType::Select->value), 'nullable', 'string', 'max:5000'],
            'zone' => ['required', Rule::enum(Zone::class)],
            'entityTypeId' => ['nullable', Rule::in($this->types->modelKeys())],
        ], [
            'name.unique' => 'Ce jeu a déjà un champ de ce nom pour ce type de fiche.',
            'options.required' => 'Indiquez au moins un choix, un par ligne.',
        ], [
            'name' => 'nom',
            'group' => 'groupe',
            'entityTypeId' => 'type de fiche',
        ]);

        $definition = $this->editingId ? $this->find($this->editingId) : new FieldDefinition([
            'position' => (int) $this->gameSystem->fieldDefinitions()->max('position') + 1,
        ]);

        $definition->fill([
            'name' => trim($this->name),
            'group' => trim($this->group) ?: null,
            'type' => $this->type,
            'options' => $this->type === FieldType::Select->value ? FieldDefinition::splitOptions($this->options) : null,
            'zone' => $this->zone,
            'entity_type_id' => $this->entityTypeId ?: null,
        ]);

        $this->gameSystem->fieldDefinitions()->save($definition);

        $this->cancel();
        $this->group = (string) $definition->group;
        unset($this->definitions, $this->groups);
    }

    public function delete(int $id): void
    {
        $this->authorize('update', $this->gameSystem);

        $this->find($id)->delete();

        if ($this->editingId === $id) {
            $this->cancel();
        }

        unset($this->definitions, $this->groups);
    }

    public function move(int $id, int $direction): void
    {
        $this->authorize('update', $this->gameSystem);

        $ids = $this->definitions->modelKeys();
        $index = array_search($id, $ids, true);
        $target = $index === false ? false : $index + ($direction < 0 ? -1 : 1);

        if ($target === false || ! isset($ids[$target])) {
            return;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];

        foreach ($ids as $position => $definitionId) {
            FieldDefinition::whereKey($definitionId)->update(['position' => $position]);
        }

        unset($this->definitions);
    }

    private function find(int $id): FieldDefinition
    {
        return $this->gameSystem->fieldDefinitions()->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.fields.manage')->title('Champs du jeu '.$this->gameSystem->name);
    }
}
