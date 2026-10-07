<?php

namespace App\Livewire\Characters;

use App\Actions\Characters\ExchangeGrant;
use App\Actions\Characters\GiveToCharacters;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\CharacterNote;
use App\Models\Entity;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\ToPlayItem;
use App\Support\Notify;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;
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

    public ?int $editingNoteId = null;

    public string $noteBody = '';

    public string $noteVisibility = 'gm';

    /** @var list<int|string> personnages avec qui partager la note */
    public array $noteShares = [];

    public string $intentionBody = '';

    public string $intentionRuleId = '';

    /** Échange en cours : l'élément que le joueur donne, à qui, combien. */
    public ?int $exchangeGrantId = null;

    public string $exchangeTo = '';

    public int $exchangeQuantity = 1;

    public string $flashExchange = '';

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
        return $this->character->grants()->with(['entity.type', 'document', 'rule'])->get()
            // Une règle repassée en zone MJ n'est plus lisible, même si elle avait été ouverte.
            ->reject(fn (CharacterGrant $grant) => $grant->kind === 'rule' && $grant->rule?->zone !== Zone::Public)
            ->values();
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

    /** Le joueur ouvre le formulaire « Donner » sous un élément. */
    public function startExchange(?int $grantId): void
    {
        abort_unless($this->canExchange, 403);

        $grant = $grantId ? $this->character->grants()->findOrFail($grantId) : null;
        $this->exchangeGrantId = $grant?->id;
        $this->exchangeTo = (string) ($this->companions->count() === 1 ? $this->companions->first()->id : '');
        $this->exchangeQuantity = max(1, (int) $grant?->quantity);
        $this->resetValidation();
    }

    /** Donne l'objet, ou transmet la connaissance, à un autre personnage. */
    public function exchange(): void
    {
        abort_unless($this->canExchange, 403);

        $this->validate([
            'exchangeTo' => ['required', ValidationRule::in($this->companions->modelKeys())],
            'exchangeQuantity' => ['integer', 'min:1'],
        ], ['exchangeTo.required' => 'Choisissez un personnage.'], ['exchangeTo' => 'destinataire', 'exchangeQuantity' => 'quantité']);

        $grant = $this->character->grants()->findOrFail($this->exchangeGrantId);
        $to = $this->companions->firstWhere('id', (int) $this->exchangeTo);

        app(ExchangeGrant::class)->handle($grant, $to, $grant->kind === 'possession' ? $this->exchangeQuantity : null);

        $this->exchangeGrantId = null;
        $this->flashExchange = ($grant->kind === 'possession' ? 'Donné à ' : 'Transmis à ').$to->entity->name.'.';
        unset($this->grants, $this->journal);
    }

    /** Le joueur peut donner à un autre personnage : fiche non verrouillée, au moins un compagnon. */
    #[Computed]
    public function canExchange(): bool
    {
        return $this->isOwner && ! $this->character->locked && $this->companions->isNotEmpty();
    }

    /** Seul le joueur du personnage écrit ses notes et ses intentions. */
    #[Computed]
    public function isOwner(): bool
    {
        return $this->character->isPlayedBy(auth()->user());
    }

    /** @return Collection<int, CharacterNote> notes de la campagne que la personne connectée peut lire */
    #[Computed]
    public function notes(): Collection
    {
        return CharacterNote::query()
            ->visibleTo(auth()->user(), $this->campaign)
            ->when($this->isGameMaster, fn ($q) => $q->where('player_character_id', $this->character->id))
            ->with(['character.entity', 'author', 'playSession', 'sharedWith.entity'])
            ->latest('id')
            ->get();
    }

    /** @return Collection<int, PlayerCharacter> les autres personnages actifs, pour le partage */
    #[Computed]
    public function companions(): Collection
    {
        return $this->campaign->playerCharacters()->active()->with(['entity', 'player'])
            ->whereKeyNot($this->character->id)->whereNotNull('user_id')->get();
    }

    /** @return Collection<int, Rule> règles ouvertes au personnage, que le joueur peut demander à tester */
    #[Computed]
    public function publicRules(): Collection
    {
        return $this->grants->where('kind', 'rule')->map(fn (CharacterGrant $grant) => $grant->rule)->sortBy(fn (Rule $rule) => mb_strtolower($rule->title))->values();
    }

    /** @return Collection<int, ToPlayItem> */
    #[Computed]
    public function intentions(): Collection
    {
        return $this->character->intentions()->with('rule')->latest('id')->limit(20)->get();
    }

    /**
     * Journal du personnage : ce qui lui a été révélé, donné ou repris. Rien d'autre.
     *
     * @return Collection<int, ActivityLog>
     */
    #[Computed]
    public function journal(): Collection
    {
        return ActivityLog::query()
            ->where('campaign_id', $this->campaign->id)
            ->where('subject_type', 'grant')
            ->where(fn ($q) => $q
                ->whereRaw("(diff->'character'->>'new')::bigint = ?", [$this->character->id])
                ->orWhereRaw("(diff->'character'->>'old')::bigint = ?", [$this->character->id]))
            ->latest('id')
            ->limit(30)
            ->get();
    }

    public function saveNote(): void
    {
        abort_unless($this->isOwner, 403);

        $this->validate([
            'noteBody' => ['required', 'string', 'max:20000'],
            'noteVisibility' => [ValidationRule::in(array_keys(CharacterNote::VISIBILITIES))],
            'noteShares' => [ValidationRule::requiredIf($this->noteVisibility === 'players'), 'array'],
            'noteShares.*' => [ValidationRule::in($this->companions->modelKeys())],
        ], ['noteShares.required' => 'Choisissez au moins un personnage.'], ['noteBody' => 'note']);

        $note = $this->editingNoteId
            ? $this->character->notes()->where('user_id', auth()->id())->findOrFail($this->editingNoteId)
            : new CharacterNote(['visibility' => 'gm']);

        $note->fill(['body' => trim($this->noteBody), 'visibility' => $this->noteVisibility]);

        if (! $note->exists) {
            $note->character()->associate($this->character);
            $note->author()->associate(auth()->user());
            $note->playSession()->associate($this->campaign->openSession());
        }

        $note->save();
        $note->sharedWith()->sync($this->noteVisibility === 'players' ? array_map('intval', $this->noteShares) : []);

        $this->cancelNote();
    }

    public function editNote(int $noteId): void
    {
        abort_unless($this->isOwner, 403);

        $note = $this->character->notes()->where('user_id', auth()->id())->findOrFail($noteId);
        $this->editingNoteId = $note->id;
        $this->noteBody = $note->body;
        $this->noteVisibility = $note->visibility;
        $this->noteShares = $note->sharedWith()->pluck('player_characters.id')->all();
    }

    public function cancelNote(): void
    {
        $this->resetValidation();
        $this->reset(['editingNoteId', 'noteBody', 'noteVisibility', 'noteShares']);
        unset($this->notes);
    }

    public function deleteNote(int $noteId): void
    {
        abort_unless($this->isOwner, 403);

        $this->character->notes()->where('user_id', auth()->id())->findOrFail($noteId)->delete();
        unset($this->notes);
    }

    /** Intention du joueur ou demande de règle : elle arrive dans « À jouer » du MJ. */
    public function addIntention(): void
    {
        abort_unless($this->isOwner, 403);

        $this->validate([
            'intentionBody' => [ValidationRule::requiredIf($this->intentionRuleId === ''), 'nullable', 'string', 'max:450'],
            'intentionRuleId' => ['nullable', ValidationRule::in($this->publicRules->modelKeys())],
        ], ['intentionBody.required' => 'Écrivez ce que vous voulez tenter, ou choisissez une règle.'], ['intentionBody' => 'intention']);

        $rule = $this->intentionRuleId === '' ? null : $this->publicRules->find((int) $this->intentionRuleId);
        $body = trim($this->intentionBody) ?: 'Demande à tester la règle « '.$rule->title.' »';

        $item = new ToPlayItem(['body' => mb_substr($body, 0, 500), 'position' => (int) $this->campaign->toPlayItems()->max('position') + 1]);
        $item->campaign()->associate($this->campaign);
        $item->character()->associate($this->character);
        $item->rule()->associate($rule);
        $item->save();
        Notify::gameMasters($this->campaign, 'intention', $this->entity->name.' : '.Notify::excerpt($body), route('sessions.live', $this->campaign), $this->character);

        $this->reset(['intentionBody', 'intentionRuleId']);
        unset($this->intentions);
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

    /** Le MJ ou le joueur a modifié la fiche ailleurs : on recharge, sauf la saisie en cours. */
    public function characterChanged(): void
    {
        $this->character->refresh();
        unset($this->entity, $this->canPlay);

        if (! $this->editing) {
            $this->fillValues();
        }
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

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return [
            'echo-private:users.'.auth()->id().',.activity' => '$refresh',
            'echo-private:characters.'.$this->character->id.',.changed' => 'characterChanged',
        ];
    }

    public function render()
    {
        return view('livewire.characters.show')->title($this->entity->name.' · '.$this->campaign->name);
    }
}
