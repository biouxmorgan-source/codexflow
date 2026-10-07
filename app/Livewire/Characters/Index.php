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
            // Objets ajoutés par les joueurs, que le MJ n'a pas encore validés.
            ->withCount(['grants as pending_count' => fn ($q) => $q->where('kind', 'possession')->where('added_by_player', true)->whereNull('validated_at')])
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
            // Un prétiré déjà copié pour un personnage n'est plus proposé.
            ->whereNotIn('id', $this->campaign->playerCharacters()->whereNotNull('source_entity_id')->select('source_entity_id'))
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
        ], attributes: ['name' => __('nom'), 'playerId' => __('joueur'), 'entityChoice' => __('fiche')]);

        // Une fiche de ce nom attend déjà dans la campagne : la reprendre plutôt que la dupliquer.
        $same = $this->entityChoice === 'new'
            ? $this->candidates->first(fn (Entity $entity) => mb_strtolower($entity->name) === mb_strtolower(trim($this->name)))
            : null;

        if ($same !== null) {
            $this->entityChoice = (string) $same->id;
            $this->addError('entityChoice', __('Une fiche « :name » existe déjà : elle est sélectionnée ci-dessus. Créez le personnage avec elle, ou supprimez-la plus bas.', ['name' => $same->name]));

            return;
        }

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
                    $source = $entity;
                    $entity = $entity->copyToCampaign($this->campaign);
                }
            }

            $character = $this->campaign->playerCharacters()->create([
                'entity_id' => $entity->id,
                'source_entity_id' => isset($source) ? $source->id : null,
                'is_active' => true,
            ]);
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

        $this->validate(["sheets.$key" => ['file', 'mimes:pdf', 'max:30720']], [], ["sheets.$key" => __('feuille')]);

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

    /** Supprime le personnage et sa fiche de campagne (connaissances, notes et jetons liés compris). */
    public function destroy(int $characterId): void
    {
        $this->authorize('update', $this->campaign);

        $entity = $this->find($characterId)->entity;
        abort_unless($entity->campaign_id === $this->campaign->id, 404);
        $this->authorize('delete', $entity);

        // Le personnage part avec sa fiche (clé étrangère en cascade).
        $entity->delete();
        unset($this->characters, $this->candidates);
    }

    /**
     * Fiches de personnage de la campagne qui ne sont plus des personnages joueurs : d'anciens
     * personnages retirés, souvent en double après plusieurs essais.
     *
     * @return Collection<int, Entity>
     */
    #[Computed]
    public function unused(): Collection
    {
        return $this->candidates->filter(fn (Entity $entity) => $entity->campaign_id === $this->campaign->id)->values();
    }

    public function deleteUnused(int $entityId): void
    {
        $this->authorize('update', $this->campaign);

        $entity = $this->unused->find($entityId) ?? abort(404);
        $this->authorize('delete', $entity);

        $entity->delete();
        unset($this->candidates, $this->unused);
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
        return view('livewire.characters.index')->title(__('Personnages · :name', ['name' => $this->campaign->name]));
    }
}
