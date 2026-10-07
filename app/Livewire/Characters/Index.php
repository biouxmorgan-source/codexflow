<?php

namespace App\Livewire\Characters;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Personnages des joueurs, côté MJ : créer ou désigner la fiche, l'attribuer à un joueur,
 * joindre la feuille PDF, verrouiller les modifications.
 */
class Index extends Component
{
    use WithFileUploads;

    public Campaign $campaign;

    /** « new » pour créer une fiche, sinon l'identifiant d'une fiche de personnage de la campagne. */
    public string $entityChoice = 'new';

    public string $name = '';

    public string $playerId = '';

    /** @var array<int, TemporaryUploadedFile|null> feuilles PDF en cours d'envoi, par personnage */
    public array $sheets = [];

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<int, PlayerCharacter> */
    #[Computed]
    public function characters(): Collection
    {
        return $this->campaign->playerCharacters()
            ->with(['entity', 'player'])
            ->get()
            ->sortBy(fn (PlayerCharacter $character) => [$character->is_active ? 0 : 1, mb_strtolower($character->entity->name)])
            ->values();
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function players(): Collection
    {
        return $this->campaign->members()
            ->wherePivot('role', CampaignRole::Player->value)
            ->orderBy('name')
            ->get();
    }

    /**
     * Fiches de personnage de la campagne, ou de son monde (les prétirés), qui ne sont pas
     * encore des personnages joueurs. Une fiche du monde est copiée dans la campagne au choix.
     *
     * @return Collection<int, Entity>
     */
    #[Computed]
    public function candidates(): Collection
    {
        return $this->campaign->availableEntities()
            ->where('entity_type_id', EntityType::standard('character')->id)
            ->whereNotIn('id', $this->campaign->playerCharacters()->select('entity_id'))
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'entityChoice' => ['required', Rule::in(['new', ...$this->candidates->modelKeys()])],
            'name' => ['required_if:entityChoice,new', 'nullable', 'string', 'max:255'],
            'playerId' => ['nullable', Rule::in($this->players->modelKeys())],
        ], attributes: ['name' => 'nom', 'playerId' => 'joueur', 'entityChoice' => 'fiche']);

        DB::transaction(function () {
            if ($this->entityChoice === 'new') {
                $entity = new Entity(['name' => trim($this->name)]);
                $entity->owner()->associate($this->campaign->user_id);
                $entity->campaign()->associate($this->campaign);
                $entity->type()->associate(EntityType::standard('character'));
                $entity->save();
            } else {
                $entity = $this->candidates->find((int) $this->entityChoice);

                if ($entity->isWorldEntity()) {
                    $entity = $entity->copyToCampaign($this->campaign);
                }
            }

            $character = $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'is_active' => true]);
            $this->assignTo($character, $this->playerId ?: null);
        });

        $this->reset(['entityChoice', 'name', 'playerId']);
        unset($this->characters, $this->candidates);
    }

    public function assign(int $characterId, string $playerId): void
    {
        $this->authorize('update', $this->campaign);
        abort_unless($playerId === '' || $this->players->contains('id', (int) $playerId), 422);

        DB::transaction(fn () => $this->assignTo($this->find($characterId), $playerId === '' ? null : (int) $playerId));
        unset($this->characters);
    }

    public function toggleActive(int $characterId): void
    {
        $this->authorize('update', $this->campaign);

        DB::transaction(function () use ($characterId) {
            $character = $this->find($characterId);
            $character->is_active = ! $character->is_active;

            if ($character->is_active) {
                $this->retireOthers($character);
            }

            $character->save();
        });

        unset($this->characters);
    }

    public function toggleLock(int $characterId): void
    {
        $this->authorize('update', $this->campaign);

        $character = $this->find($characterId);
        $character->update(['locked' => ! $character->locked]);
        unset($this->characters);
    }

    /** Envoi immédiat de la feuille PDF choisie. */
    public function updatedSheets(mixed $file, string $key): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate(["sheets.$key" => ['file', 'mimes:pdf', 'max:30720']], [], ["sheets.$key" => 'feuille']);

        $character = $this->find((int) $key);
        $character->deleteSheet();
        $character->update([
            'sheet_path' => $file->store('character-sheets/'.$this->campaign->id, PlayerCharacter::DISK),
            'sheet_name' => $file->getClientOriginalName(),
            'sheet_size' => $file->getSize(),
        ]);

        unset($this->sheets[$key], $this->characters);
    }

    public function removeSheet(int $characterId): void
    {
        $this->authorize('update', $this->campaign);

        $character = $this->find($characterId);
        $character->deleteSheet();
        $character->update(['sheet_path' => null, 'sheet_name' => null, 'sheet_size' => null]);
        unset($this->characters);
    }

    /** Retire le statut de personnage joueur ; la fiche reste dans la campagne. */
    public function remove(int $characterId): void
    {
        $this->authorize('update', $this->campaign);

        $this->find($characterId)->delete();
        unset($this->characters, $this->candidates);
    }

    private function find(int $characterId): PlayerCharacter
    {
        return $this->campaign->playerCharacters()->findOrFail($characterId);
    }

    private function assignTo(PlayerCharacter $character, ?int $userId): void
    {
        $character->user_id = $userId;

        if ($character->is_active) {
            $this->retireOthers($character);
        }

        $character->save();
    }

    /** Un joueur n'a qu'un personnage actif par campagne : les autres passent au repos. */
    private function retireOthers(PlayerCharacter $character): void
    {
        if ($character->user_id === null) {
            return;
        }

        $this->campaign->playerCharacters()
            ->where('user_id', $character->user_id)
            ->whereKeyNot($character->id)
            ->update(['is_active' => false]);
    }

    public function render()
    {
        return view('livewire.characters.index')->title('Personnages · '.$this->campaign->name);
    }
}
