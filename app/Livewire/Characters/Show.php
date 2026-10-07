<?php

namespace App\Livewire\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Entity;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Fiche d'un personnage joueur telle que le joueur la voit : zone publique, feuille PDF,
 * compteurs et champs qu'il a le droit de modifier. La zone MJ n'est jamais chargée ici.
 */
class Show extends Component
{
    public Campaign $campaign;

    public PlayerCharacter $character;

    /** @var array<int, string|bool> saisies des champs modifiables, par définition */
    public array $values = [];

    /** Champ modifiable en cours d'édition (null = lecture). */
    public bool $editing = false;

    public function mount(Campaign $campaign, PlayerCharacter $character): void
    {
        abort_unless($character->campaign_id === $campaign->id, 404);
        $this->authorize('view', $character);

        $this->fillValues();
    }

    #[Computed]
    public function entity(): Entity
    {
        return $this->character->entity;
    }

    /** @return Collection<int, FieldDefinition> champs de la zone publique pour ce type de fiche */
    #[Computed]
    public function fields(): Collection
    {
        return $this->campaign->gameSystem->fieldDefinitions()
            ->forType($this->entity->entity_type_id)
            ->where('zone', Zone::Public)
            ->ordered()
            ->get();
    }

    /** @return Collection<int, CharacterGrant> ce que le personnage a reçu, du plus récent au plus ancien */
    #[Computed]
    public function grants(): Collection
    {
        return $this->character->grants()->with(['entity.type', 'document'])->get();
    }

    #[On('character-grants-changed')]
    public function refreshGrants(): void
    {
        unset($this->grants);
    }

    /** Le MJ reprend un objet ou cache à nouveau une fiche. */
    public function revoke(int $grantId): void
    {
        abort_unless($this->isGameMaster, 403);

        GiveToCharacters::revoke($this->character->grants()->findOrFail($grantId));
        unset($this->grants);
    }

    #[Computed]
    public function canPlay(): bool
    {
        return auth()->user()->can('play', $this->character);
    }

    #[Computed]
    public function isGameMaster(): bool
    {
        return $this->campaign->isGameMaster(auth()->user());
    }

    /** Ajuste un compteur modifiable : −1, +1… */
    public function adjust(int $definitionId, int $delta): void
    {
        $definition = $this->editable($definitionId);
        abort_unless($definition->type === FieldType::Counter && abs($delta) <= 1000, 422);

        DB::transaction(function () use ($definition, $delta) {
            // Verrou : le MJ et le joueur peuvent cliquer en même temps.
            $entity = Entity::query()->lockForUpdate()->findOrFail($this->entity->id);
            $entity->setFieldValues([$definition->id => FieldType::adjustCounter($entity->fieldValue($definition), $delta)]);
            $entity->save();
        });

        $this->refreshEntity();
    }

    public function edit(): void
    {
        abort_unless($this->canPlay, 403);
        $this->fillValues();
        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->editing = false;
    }

    public function save(): void
    {
        abort_unless($this->canPlay, 403);

        $parsed = [];
        $errors = [];

        foreach ($this->fields->where('player_editable', true) as $definition) {
            [$value, $error] = $definition->parse($this->values[$definition->id] ?? null);

            if ($error !== null) {
                $errors['values.'.$definition->id] = $definition->name.' : '.$error.'.';
            } else {
                $parsed[$definition->id] = $value;
            }
        }

        if ($errors !== []) {
            $this->setErrorBag($errors);

            return;
        }

        DB::transaction(function () use ($parsed) {
            $entity = Entity::query()->lockForUpdate()->findOrFail($this->entity->id);
            $entity->setFieldValues($parsed);
            $entity->save();
        });

        $this->editing = false;
        $this->refreshEntity();
    }

    /**
     * Adresse d'une fiche citée dans un texte, si le personnage la connaît ; sinon texte simple.
     */
    public function knownLink(Entity $linked): ?string
    {
        if ($linked->id === $this->entity->id) {
            return null;
        }

        return $this->grants->contains(fn (CharacterGrant $grant) => $grant->entity_id === $linked->id)
            ? route('characters.entity', [$this->campaign, $this->character, $linked])
            : null;
    }

    private function editable(int $definitionId): FieldDefinition
    {
        abort_unless($this->canPlay, 403);

        $definition = $this->fields->firstWhere('id', $definitionId);
        abort_unless($definition?->player_editable, 403);

        return $definition;
    }

    private function refreshEntity(): void
    {
        $this->character->load('entity');
        unset($this->entity);
        $this->fillValues();
    }

    private function fillValues(): void
    {
        $this->values = [];

        foreach ($this->fields->where('player_editable', true) as $definition) {
            $this->values[$definition->id] = $definition->type->input($this->entity->fieldValue($definition));
        }
    }

    public function render()
    {
        return view('livewire.characters.show')->title($this->entity->name.' · '.$this->campaign->name);
    }
}
