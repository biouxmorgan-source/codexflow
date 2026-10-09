<?php

namespace App\Livewire\Characters;

use App\Actions\Characters\TransferGrants;
use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\Plans\Plans;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
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

    /** Personnage qui reçoit ce que lui transmet un ancien personnage du même joueur. */
    public ?int $transferTo = null;

    public string $transferFrom = '';

    /** @var list<int|string> éléments cochés de l'ancien personnage */
    public array $transferIds = [];

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<int, PlayerCharacter> */
    #[Computed]
    public function characters(): Collection
    {
        return $this->campaign->playerCharacters()
            ->with(['entity', 'player', 'previousPlayer', 'assignments'])
            // Objets ajoutés par les joueurs, que le MJ n'a pas encore validés.
            ->withCount(['grants as pending_count' => fn ($q) => $q->where('kind', 'possession')->where('added_by_player', true)->whereNull('validated_at')])
            // Échanges proposés par le joueur, en attente du MJ.
            ->withCount('exchangeRequests')
            ->get()
            ->sortBy(fn (PlayerCharacter $character) => [$character->is_active ? 0 : 1, mb_strtolower($character->entity->name)])
            ->values();
    }

    /** Le MJ valide chaque échange entre joueurs, ou les autorise d'office. */
    public function toggleExchangeApproval(): void
    {
        $this->authorize('update', $this->campaign);

        $this->campaign->update(['exchanges_need_approval' => ! $this->campaign->exchanges_need_approval]);
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
     * Personnages sans joueur dont un ancien joueur est de nouveau dans la campagne
     * et n'y joue rien d'autre : le MJ peut les lui rendre d'un clic. Le dernier joueur, s'il est
     * encore là ; sinon un joueur plus ancien revenu depuis (le personnage a pu passer entre d'autres mains).
     *
     * @return Collection<int, PlayerCharacter> chacun avec sa relation « returnee »
     */
    #[Computed]
    public function returning(): Collection
    {
        $playing = $this->characters->whereNotNull('user_id')->where('is_active', true)->pluck('user_id');

        return $this->characters
            ->filter(fn (PlayerCharacter $character) => $character->user_id === null)
            ->map(fn (PlayerCharacter $character) => $character->setRelation('returnee', $this->returneeOf($character, $playing)))
            ->filter(fn (PlayerCharacter $character) => $character->returnee !== null)
            ->values();
    }

    /** @param SupportCollection<int, int> $playing */
    private function returneeOf(PlayerCharacter $character, SupportCollection $playing): ?User
    {
        foreach ($character->assignments->whereNotNull('ended_at') as $assignment) {
            $player = $this->players->firstWhere('id', $assignment->user_id);

            if ($player === null || $playing->contains($player->id)) {
                continue;
            }

            // Revenu dans la campagne après avoir quitté le personnage, ou son tout dernier joueur.
            if ($player->id === $character->previous_user_id || $player->pivot->created_at?->gte($assignment->ended_at)) {
                return $player;
            }
        }

        // Personnages d'avant l'historique des joueurs.
        $previous = $this->players->firstWhere('id', $character->previous_user_id);

        return $previous !== null && ! $playing->contains($previous->id) ? $previous : null;
    }

    public function giveBack(int $characterId): void
    {
        $this->authorize('update', $this->campaign);
        $character = $this->returning->firstWhere('id', $characterId);
        abort_if($character === null, 422);

        DB::transaction(fn () => $this->assignTo($character, $character->returnee->id));
        unset($this->characters, $this->returning);
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
            ->with('tags')
            ->orderBy('name')
            ->get();
    }

    /**
     * Tags qui marquent un prétiré, dans toutes les langues de l'interface (ceux de la campagne
     * de démonstration compris). Les fiches ainsi marquées sont proposées en tête.
     */
    public static function pregenTag(string $name): bool
    {
        static $names = null;
        $names ??= collect(['prétiré', 'pretire', 'pré-tiré', 'pregen', 'pre-gen', 'pregenerated', 'pré-généré'])
            ->merge(collect(glob(resource_path('demo/*.php')))->map(fn (string $file) => (require $file)['tags']['pregen'] ?? null))
            ->filter()
            ->map(fn (string $tag) => mb_strtolower($tag))
            ->unique()
            ->all();

        return in_array(mb_strtolower(trim($name)), $names, true);
    }

    /** Candidats rangés : prétirés d'abord, puis les autres fiches du monde, puis celles de la campagne. */
    #[Computed]
    public function candidateGroups(): SupportCollection
    {
        $pregen = fn (Entity $entity) => $entity->tags->contains(fn ($tag) => self::pregenTag($tag->name));

        return collect([
            __('Prétirés') => $this->candidates->filter($pregen),
            __('Autres personnages du monde (copiés dans la campagne)') => $this->candidates->reject($pregen)->filter->isWorldEntity(),
            __('Autres personnages de la campagne') => $this->candidates->reject($pregen)->reject->isWorldEntity(),
        ])->filter(fn ($group) => $group->isNotEmpty());
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

        $character = DB::transaction(function () {
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

            return $character;
        });

        $this->reset(['entityChoice', 'name', 'playerId']);
        unset($this->characters, $this->candidates);
        $this->offerTransfer($character);
    }

    public function assign(int $characterId, string $playerId): void
    {
        $this->authorize('update', $this->campaign);
        abort_unless($playerId === '' || $this->players->contains('id', (int) $playerId), 422);

        $character = $this->find($characterId);
        DB::transaction(fn () => $this->assignTo($character, $playerId === '' ? null : (int) $playerId));
        unset($this->characters);
        $this->offerTransfer($character);
    }

    /**
     * Anciens personnages du même joueur, du plus récemment joué au plus ancien.
     *
     * @return Collection<int, PlayerCharacter>
     */
    #[Computed]
    public function transferSources(): Collection
    {
        $target = $this->transferTo ? $this->campaign->playerCharacters()->find($this->transferTo) : null;

        if (! $target?->user_id) {
            return new Collection;
        }

        return $this->campaign->playerCharacters()->with('entity')
            ->where('user_id', $target->user_id)
            ->whereKeyNot($target->id)
            ->latest('updated_at')
            ->get();
    }

    /** @return Collection<int, CharacterGrant> ce que l'ancien personnage choisi peut transmettre */
    #[Computed]
    public function transferGrants(): Collection
    {
        $source = $this->transferSources->find((int) $this->transferFrom);

        return $source ? TransferGrants::transferable($source) : new Collection;
    }

    /** Ouvre le choix de ce qui passe d'un ancien personnage du joueur à celui-ci. */
    public function openTransfer(int $characterId): void
    {
        $this->authorize('update', $this->campaign);

        $this->transferTo = $this->find($characterId)->id;
        unset($this->transferSources, $this->transferGrants);
        $this->transferFrom = (string) ($this->transferSources->first()?->id ?? '');
        $this->updatedTransferFrom();
    }

    /** Un autre ancien personnage choisi : tout ce qu'il peut transmettre est coché d'office. */
    public function updatedTransferFrom(): void
    {
        unset($this->transferGrants);
        $this->transferIds = $this->transferGrants->modelKeys();
    }

    public function transfer(TransferGrants $action): void
    {
        $this->authorize('update', $this->campaign);

        $to = $this->find((int) $this->transferTo);
        $from = $this->transferSources->find((int) $this->transferFrom);
        abort_unless($from !== null, 404);

        $count = $action->handle($from, $to, $this->transferIds);
        session()->flash('status', trans_choice(':count élément transmis à :name.|:count éléments transmis à :name.', $count, ['name' => $to->entity->name]));

        $this->closeTransfer();
    }

    public function closeTransfer(): void
    {
        $this->reset(['transferTo', 'transferFrom', 'transferIds']);
        unset($this->transferSources, $this->transferGrants);
    }

    /** Nouveau personnage d'un joueur qui en avait un autre : proposer de lui transmettre ses acquis. */
    private function offerTransfer(PlayerCharacter $character): void
    {
        if ($character->user_id === null) {
            return;
        }

        $this->openTransfer($character->id);

        if ($this->transferGrants->isEmpty()) {
            $this->closeTransfer();
        }
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
        Plans::ensureRoom($this->campaign->owner, (int) $file->getSize(), "sheets.$key");

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
