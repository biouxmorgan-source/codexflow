<?php

namespace App\Livewire\Characters;

use App\Actions\Characters\ExchangeGrant;
use App\Actions\Characters\GiveToCharacters;
use App\Actions\Characters\PlayerAdditions;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\CharacterNote;
use App\Models\Entity;
use App\Models\ExchangeRequest;
use App\Models\FieldDefinition;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\TimelineEvent;
use App\Models\ToPlayItem;
use App\Support\CampaignFeatures;
use App\Support\Locale;
use App\Support\Notify;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
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

    /** Ajout par le joueur en cours : information ou possession ('' = formulaire fermé). */
    public string $addKind = '';

    public string $addTitle = '';

    public string $addBody = '';

    public int $addQuantity = 1;

    /**
     * Mode « Voir comme… » : le MJ voit la fiche exactement comme le joueur du personnage,
     * en lecture seule. Toute action qui modifie quelque chose est refusée côté serveur.
     */
    #[Url(as: 'comme')]
    public bool $viewAs = false;

    public function mount(Campaign $campaign, PlayerCharacter $character): void
    {
        abort_unless($character->campaign_id === $campaign->id, 404);
        $this->authorize('view', $character);
        // Réservé au MJ : un joueur n'a rien à « voir comme », il voit déjà sa propre fiche.
        abort_if($this->viewAs && ! $campaign->isGameMaster(auth()->user()), 403);

        $this->fillValues();
    }

    /** Le MJ en titre, qu'il soit ou non en mode « Voir comme… » (voir isGameMaster). */
    #[Computed]
    public function canViewAs(): bool
    {
        return $this->campaign->isGameMaster(auth()->user());
    }

    /** Refuse toute modification en mode « Voir comme… » (le MJ pourrait appeler l'action directement). */
    private function ensureNotViewingAs(): void
    {
        abort_if($this->viewAs, 403);
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
        return $this->character->grants()->with(['entity.type', 'document', 'rule', 'exchangeRequest.to.entity'])->get()
            // Une règle repassée en zone MJ n'est plus lisible, même si elle avait été ouverte.
            ->reject(fn (CharacterGrant $grant) => $grant->kind === 'rule' && $grant->rule?->zone !== Zone::Public)
            ->values();
    }

    /**
     * Ce que le joueur peut choisir dans un champ référence ou fichier : seulement les fiches
     * et documents que son personnage connaît.
     *
     * @return array{entities: list<string>, documents: array<int, string>}
     */
    #[Computed]
    public function fieldChoices(): array
    {
        return [
            'entities' => $this->grants->map(fn (CharacterGrant $grant) => $grant->entity?->name)->filter()->unique()->sort()->values()->all(),
            'documents' => $this->grants->map(fn (CharacterGrant $grant) => $grant->document)->filter()->sortBy('title')->pluck('title', 'id')->all(),
        ];
    }

    /**
     * Autocomplétion des liens [[…]] dans les notes du joueur : seulement les fiches que
     * son personnage connaît, jamais le reste de la campagne.
     *
     * @return list<array{id: int, name: string, type: string}>
     */
    #[Renderless]
    public function suggestEntities(string $query): array
    {
        if (! $this->canWrite) {
            return [];
        }

        $query = mb_strtolower(trim(mb_substr($query, 0, 60)));

        return $this->grants
            ->map(fn (CharacterGrant $grant) => $grant->entity)
            ->filter()
            ->unique('id')
            ->filter(fn (Entity $entity) => $query === '' || str_contains(mb_strtolower($entity->name), $query))
            ->sortBy(fn (Entity $entity) => [! str_starts_with(mb_strtolower($entity->name), $query), mb_strtolower($entity->name)])
            ->take(8)
            ->map(fn (Entity $entity) => ['id' => $entity->id, 'name' => $entity->name, 'type' => $entity->type?->name ?? ''])
            ->values()
            ->all();
    }

    #[On('character-grants-changed')]
    public function refreshGrants(): void
    {
        unset($this->grants);
    }

    /** Le MJ reprend un objet ou cache à nouveau une fiche. */
    public function revoke(int $grantId): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->isGameMaster, 403);

        GiveToCharacters::revoke($this->character->grants()->findOrFail($grantId));
        unset($this->grants);
    }

    /** Le joueur ouvre le formulaire « Donner » sous un élément. */
    public function startExchange(?int $grantId): void
    {
        $this->ensureNotViewingAs();
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
        $this->ensureNotViewingAs();
        abort_unless($this->canExchange, 403);

        $this->validate([
            'exchangeTo' => ['required', ValidationRule::in($this->companions->modelKeys())],
            'exchangeQuantity' => ['integer', 'min:1'],
        ], ['exchangeTo.required' => __('Choisissez un personnage.')], ['exchangeTo' => __('destinataire'), 'exchangeQuantity' => __('quantité')]);

        $grant = $this->character->grants()->findOrFail($this->exchangeGrantId);
        $to = $this->companions->firstWhere('id', (int) $this->exchangeTo);

        $result = app(ExchangeGrant::class)->handle($grant, $to, $grant->kind === 'possession' ? $this->exchangeQuantity : null);

        $this->exchangeGrantId = null;
        $this->flashExchange = match (true) {
            $result instanceof ExchangeRequest => __('Proposé à :name : le MJ doit valider l’échange.', ['name' => $to->entity->name]),
            $grant->kind === 'possession' => __('Donné à :name.', ['name' => $to->entity->name]),
            default => __('Transmis à :name.', ['name' => $to->entity->name]),
        };
        unset($this->grants, $this->journal);
    }

    /** Le joueur retire un échange qu'il avait proposé. */
    public function cancelExchange(int $grantId): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->isOwner, 403);

        app(ExchangeGrant::class)->cancel($this->exchangeRequestFor($grantId));
        unset($this->grants);
    }

    /** Le MJ accepte ou refuse un échange proposé par le joueur. */
    public function answerExchange(int $grantId, bool $accept): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->isGameMaster, 403);

        $request = $this->exchangeRequestFor($grantId);
        $accept ? app(ExchangeGrant::class)->approve($request) : app(ExchangeGrant::class)->reject($request);
        unset($this->grants, $this->journal);
    }

    private function exchangeRequestFor(int $grantId): ExchangeRequest
    {
        return ExchangeRequest::where('from_character_id', $this->character->id)->where('character_grant_id', $grantId)->firstOrFail();
    }

    /** Le joueur note lui-même sur sa fiche : fiche non verrouillée, personnage actif. */
    #[Computed]
    public function canAdd(): bool
    {
        return $this->canWrite && ! $this->character->locked;
    }

    /** Notes et intentions : un personnage au repos garde son historique, en lecture seule. */
    #[Computed]
    public function canWrite(): bool
    {
        return $this->isOwner && $this->character->is_active;
    }

    public function openAdd(string $kind): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->canAdd && in_array($kind, ['', ...PlayerAdditions::KINDS], true), 403);

        $this->addKind = $kind;
        $this->reset('addTitle', 'addBody', 'addQuantity');
        $this->resetValidation();
    }

    public function addOwn(): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->canAdd && in_array($this->addKind, PlayerAdditions::KINDS, true), 403);

        $this->validate([
            'addTitle' => ['required', 'string', 'max:200'],
            'addBody' => ['nullable', 'string', 'max:5000'],
            'addQuantity' => ['integer', 'min:1', 'max:1000000'],
        ], attributes: ['addTitle' => $this->addKind === 'possession' ? __('objet') : __('titre'), 'addBody' => __('détail'), 'addQuantity' => __('quantité')]);

        app(PlayerAdditions::class)->add($this->character, $this->addKind, $this->addTitle, $this->addBody, $this->addQuantity);

        $this->openAdd('');
        unset($this->grants, $this->journal);
    }

    /** Le joueur efface ce qu'il avait noté lui-même. */
    public function removeOwn(int $grantId): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->canAdd, 403);

        app(PlayerAdditions::class)->remove($this->character->grants()->findOrFail($grantId));
        unset($this->grants, $this->journal);
    }

    /** Le MJ valide un objet ajouté par le joueur. */
    public function validateGrant(int $grantId): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->isGameMaster, 403);

        app(PlayerAdditions::class)->validate($this->character->grants()->findOrFail($grantId));
        unset($this->grants, $this->journal);
    }

    /** Le joueur peut donner à un autre personnage : fiche non verrouillée, au moins un compagnon. */
    #[Computed]
    public function canExchange(): bool
    {
        return $this->canAdd && $this->companions->isNotEmpty() && CampaignFeatures::enabled($this->campaign, 'exchanges');
    }

    /** Seul le joueur du personnage écrit ses notes et ses intentions. */
    #[Computed]
    public function isOwner(): bool
    {
        return ! $this->viewAs && $this->character->isPlayedBy(auth()->user());
    }

    /** @return Collection<int, CharacterNote> notes de la campagne que la personne connectée peut lire */
    #[Computed]
    public function notes(): Collection
    {
        return CharacterNote::query()
            // « Voir comme… » : les notes que lit le joueur du personnage, toutes fiches confondues.
            ->when($this->viewAs,
                fn ($q) => $q->visibleToCharacter($this->character),
                fn ($q) => $q->visibleTo(auth()->user(), $this->campaign))
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

    /**
     * Fil de la campagne, vu par le joueur : séances, événements joués connus de la table et
     * messages au groupe, du plus récent au plus ancien. Rien de la zone MJ, rien d'un autre personnage.
     *
     * @return \Illuminate\Support\Collection<int, array{at: Carbon, kind: string, text: string, url: ?string}>
     */
    #[Computed]
    public function feed(): \Illuminate\Support\Collection
    {
        $sessions = $this->campaign->playSessions()->latest('started_at')->limit(10)->get()
            ->map(fn ($session) => ['at' => $session->started_at, 'kind' => __('Séance'), 'text' => $session->label(), 'url' => null]);

        $events = CampaignFeatures::enabled($this->campaign, 'timeline')
            ? TimelineEvent::query()->where('campaign_id', $this->campaign->id)->visibleToPlayers()->latest('id')->limit(10)->get()
                ->map(fn (TimelineEvent $event) => ['at' => $event->created_at, 'kind' => __('Événement'), 'text' => trim(($event->date_label ? $event->date_label.' · ' : '').$event->title), 'url' => route('timeline.index', $this->campaign)])
            : collect();

        $messages = Message::query()->where('campaign_id', $this->campaign->id)->whereNull('player_character_id')
            ->with('senderCharacter.entity')->latest('id')->limit(10)->get()
            ->map(fn (Message $message) => ['at' => $message->created_at, 'kind' => __('Message au groupe'), 'text' => $message->senderLabel().' : '.Notify::excerpt((string) $message->body, 140), 'url' => route('messages.index', $this->campaign)]);

        return collect()->concat($sessions)->concat($events)->concat($messages)
            ->filter(fn (array $item) => $item['at'] !== null)
            ->sortByDesc('at')->take(15)->values();
    }

    public function saveNote(): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->canWrite, 403);

        $this->validate([
            'noteBody' => ['required', 'string', 'max:20000'],
            'noteVisibility' => [ValidationRule::in(array_keys(CharacterNote::VISIBILITIES))],
            'noteShares' => [ValidationRule::requiredIf($this->noteVisibility === 'players'), 'array'],
            'noteShares.*' => [ValidationRule::in($this->companions->modelKeys())],
        ], ['noteShares.required' => __('Choisissez au moins un personnage.')], ['noteBody' => __('note')]);

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
        $this->ensureNotViewingAs();
        abort_unless($this->canWrite, 403);

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
        $this->ensureNotViewingAs();
        abort_unless($this->canWrite, 403);

        $this->character->notes()->where('user_id', auth()->id())->findOrFail($noteId)->delete();
        unset($this->notes);
    }

    /** Intention du joueur ou demande de règle : elle arrive dans « À jouer » du MJ. */
    public function addIntention(): void
    {
        $this->ensureNotViewingAs();
        abort_unless($this->canWrite, 403);

        $this->validate([
            'intentionBody' => [ValidationRule::requiredIf($this->intentionRuleId === ''), 'nullable', 'string', 'max:450'],
            'intentionRuleId' => ['nullable', ValidationRule::in($this->publicRules->modelKeys())],
        ], ['intentionBody.required' => __('Écrivez ce que vous voulez tenter, ou choisissez une règle.')], ['intentionBody' => __('intention')]);

        $rule = $this->intentionRuleId === '' ? null : $this->publicRules->find((int) $this->intentionRuleId);
        // Texte enregistré, lu par le MJ : écrit dans la langue du MJ propriétaire de la campagne.
        $body = trim($this->intentionBody) ?: __('Demande à tester la règle « :title »', ['title' => $rule->title], Locale::for($this->campaign->owner));

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
        return ! $this->viewAs && auth()->user()->can('play', $this->character);
    }

    /** Contrôles du MJ affichés : jamais en mode « Voir comme… ». */
    #[Computed]
    public function isGameMaster(): bool
    {
        return ! $this->viewAs && $this->canViewAs;
    }

    /** Ajuste un compteur modifiable : −1, +1… */
    public function adjust(int $definitionId, int $delta): void
    {
        $this->ensureNotViewingAs();
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
        $this->ensureNotViewingAs();
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
        $this->ensureNotViewingAs();
        abort_unless($this->canPlay, 403);

        $this->resetValidation();
        $parsed = [];
        $errors = [];

        $known = collect($this->fieldChoices['entities'])->map(fn (string $name) => mb_strtolower($name));

        foreach ($this->fields->where('player_editable', true) as $definition) {
            $raw = $this->values[$definition->id] ?? null;
            // Une fiche que le personnage ne connaît pas n'est pas liée : le texte reste tel quel,
            // sans révéler qu'une fiche de ce nom existe dans la campagne.
            $refName = $definition->type === FieldType::EntityRef && is_string($raw)
                ? mb_strtolower(trim(preg_match('/^\[\[([^|\]]+)/u', trim($raw), $match) ? $match[1] : str_replace(['[', ']', '|'], '', $raw)))
                : '';
            $unknownRef = $refName !== '' && ! $known->contains($refName)
                && $raw !== $definition->type->input($this->entity->fieldValue($definition));
            [$value, $error] = $definition->parse($raw, $unknownRef ? null : $this->campaign);

            // Un document que le personnage ne connaît pas ne peut pas être choisi (sauf s'il y était déjà).
            if ($error === null && $value !== null && $definition->type === FieldType::File
                && ! array_key_exists($value, $this->fieldChoices['documents']) && $value !== $this->entity->fieldValue($definition)) {
                $error = __('ce document ne fait pas partie de la campagne');
            }

            if ($error !== null) {
                $errors['values.'.$definition->id] = __(':field : :error.', ['field' => $definition->name, 'error' => $error]);
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
            ? route('characters.entity', [$this->campaign, $this->character, $linked, ...$this->viewAsQuery()])
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

    /** @return array<string, int> paramètre qui garde le mode « Voir comme… » dans les liens */
    public function viewAsQuery(): array
    {
        return $this->viewAs ? ['comme' => 1] : [];
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
