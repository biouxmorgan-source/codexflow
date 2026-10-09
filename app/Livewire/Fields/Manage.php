<?php

namespace App\Livewire\Fields;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\GameSystem;
use App\Support\Archive\ArchiveException;
use App\Support\Archive\CampaignImport;
use App\Support\Plans\Plans;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Champs libres d'un jeu, ouverts depuis une campagne de ce jeu.
 */
class Manage extends Component
{
    use WithFileUploads;

    public Campaign $campaign;

    public ?int $editingId = null;

    public string $name = '';

    public string $group = '';

    public string $type = 'text';

    public string $options = '';

    public string $zone = 'public';

    /** @var list<string> types de fiche concernés ; aucun coché : tous les types */
    public array $entityTypeIds = [];

    /** Le joueur peut modifier ce champ sur la fiche de son personnage. */
    public bool $playerEditable = false;

    /** Modèle de jeu (.json) exporté depuis LoreMundi. */
    public ?TemporaryUploadedFile $template = null;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
        $this->authorize('manageFields', [$campaign->gameSystem, $campaign]);
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
        return EntityType::query()->availableTo($this->campaign->owner)->orderBy('id')->get();
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
        $this->entityTypeIds = array_map('strval', $definition->typeIds());
        $this->playerEditable = $definition->player_editable;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'name', 'type', 'options', 'zone', 'entityTypeIds', 'playerEditable');
    }

    public function save(): void
    {
        $this->authorize('manageFields', [$this->gameSystem, $this->campaign]);

        $this->validate([
            'name' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, \Closure $fail) {
                if ($this->nameTaken(trim((string) $value))) {
                    $fail(__('Ce jeu a déjà un champ de ce nom pour ce type de fiche.'));
                }
            }],
            'group' => ['nullable', 'string', 'max:100'],
            'type' => ['required', Rule::enum(FieldType::class)],
            'options' => [Rule::requiredIf($this->type === FieldType::Select->value), 'nullable', 'string', 'max:5000'],
            'zone' => ['required', Rule::enum(Zone::class)],
            'entityTypeIds' => ['array'],
            'entityTypeIds.*' => [Rule::in($this->types->modelKeys())],
        ], [
            'options.required' => __('Indiquez au moins un choix, un par ligne.'),
        ], [
            'name' => __('nom'),
            'group' => __('groupe'),
            'entityTypeIds' => __('type de fiche'),
            'entityTypeIds.*' => __('type de fiche'),
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
            // La zone MJ reste hors de portée des joueurs.
            'player_editable' => $this->playerEditable && $this->zone === Zone::Public->value,
        ]);

        $definition->assignTypes($this->entityTypeIds);

        $this->gameSystem->fieldDefinitions()->save($definition);

        $this->cancel();
        $this->group = (string) $definition->group;
        unset($this->definitions, $this->groups);
    }

    public function delete(int $id): void
    {
        $this->authorize('manageFields', [$this->gameSystem, $this->campaign]);

        $this->find($id)->delete();

        if ($this->editingId === $id) {
            $this->cancel();
        }

        unset($this->definitions, $this->groups);
    }

    public function move(int $id, int $direction): void
    {
        $this->authorize('manageFields', [$this->gameSystem, $this->campaign]);

        $definitions = $this->definitions->values();
        $ids = $definitions->modelKeys();
        $index = array_search($id, $ids, true);

        if ($index === false) {
            return;
        }

        // Les champs s'affichent par groupe : on échange avec le voisin du même groupe.
        $group = $definitions[$index]->groupLabel();
        $target = $index;
        do {
            $target += $direction < 0 ? -1 : 1;
        } while (isset($ids[$target]) && $definitions[$target]->groupLabel() !== $group);

        if (! isset($ids[$target])) {
            return;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];

        foreach ($ids as $position => $definitionId) {
            FieldDefinition::whereKey($definitionId)->update(['position' => $position]);
        }

        unset($this->definitions);
    }

    /** Un autre champ du même nom concerne déjà l'un des types choisis (ou tous les types). */
    private function nameTaken(string $name): bool
    {
        $chosen = (new FieldDefinition)->assignTypes($this->entityTypeIds);

        return $this->gameSystem->fieldDefinitions()
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
            ->get()
            ->contains(fn (FieldDefinition $other) => $other->typeIds() === [] || $chosen->typeIds() === []
                || array_intersect($other->typeIds(), $chosen->typeIds()) !== []);
    }

    private function find(int $id): FieldDefinition
    {
        return $this->gameSystem->fieldDefinitions()->findOrFail($id);
    }

    public function importTemplate(): void
    {
        $this->authorize('manageFields', [$this->gameSystem, $this->campaign]);
        $this->validate(['template' => ['required', 'file', 'extensions:json', 'max:5120']], attributes: ['template' => __('modèle')]);
        Plans::ensure($this->campaign->owner, 'archive');

        try {
            $added = (new CampaignImport($this->campaign->owner))->template((string) file_get_contents($this->template->getRealPath()), $this->campaign);
        } catch (ArchiveException $e) {
            $this->addError('template', $e->getMessage());

            return;
        }

        $this->template->delete();
        $this->reset('template');
        unset($this->definitions, $this->types, $this->groups);
        session()->now('status', __('Modèle importé : :fields, :rules.', [
            'fields' => trans_choice(':count champ ajouté|:count champs ajoutés', $added['fields']),
            'rules' => trans_choice(':count règle ajoutée|:count règles ajoutées', $added['rules']),
        ]));
    }

    public function render()
    {
        return view('livewire.fields.manage')->title(__('Champs du jeu :name', ['name' => $this->gameSystem->name]));
    }
}
