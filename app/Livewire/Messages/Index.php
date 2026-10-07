<?php

namespace App\Livewire\Messages;

use App\Actions\Messages\SendMessage;
use App\Enums\Zone;
use App\Livewire\Concerns\SuggestsEntities;
use App\Livewire\HeaderBadges;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Document;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Messagerie de la campagne. Le MJ a une conversation par personnage et une pour le groupe ;
 * le joueur voit, dans un seul fil, ses échanges avec le MJ et les messages au groupe.
 */
class Index extends Component
{
    use SuggestsEntities;

    public Campaign $campaign;

    /** Conversation ouverte par le MJ : identifiant du personnage, vide pour le groupe. */
    #[Url(as: 'personnage', except: '')]
    public string $conversation = '';

    public string $body = '';

    /** Pièce jointe du MJ : entity, document, rule ou vide. */
    public string $refKind = '';

    public ?int $refEntityId = null;

    public string $refDocumentId = '';

    public string $refRuleId = '';

    /** @var list<int|string> message au groupe adressé seulement à ces personnages */
    public array $selected = [];

    public ?string $flash = null;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('view', $campaign);

        if (! $this->isGameMaster) {
            $this->conversation = '';
        } elseif ($this->conversation !== '' && ! $this->characters->contains('id', (int) $this->conversation)) {
            $this->conversation = '';
        } elseif ($this->conversation === '' && $this->groupUnread === 0) {
            // Le MJ arrive directement sur la conversation qui attend une réponse.
            $this->conversation = (string) ($this->characters->firstWhere('unread', '>', 0)?->id ?? '');
        }
    }

    #[Computed]
    public function isGameMaster(): bool
    {
        return $this->campaign->isGameMaster(auth()->user());
    }

    /**
     * Personnages du MJ : actifs et confiés à un joueur, ou ayant déjà une conversation.
     *
     * @return Collection<int, PlayerCharacter>
     */
    #[Computed]
    public function characters(): Collection
    {
        if (! $this->isGameMaster) {
            return new Collection;
        }

        $user = auth()->user();

        return $this->campaign->playerCharacters()
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('is_active', true)->whereNotNull('user_id'))
                ->orWhereHas('messages'))
            ->with(['entity', 'player'])
            ->withCount(['messages as unread' => fn ($q) => $q->unreadBy($user)])
            ->get()
            ->sortBy(fn (PlayerCharacter $character) => mb_strtolower($character->entity->name))
            ->values();
    }

    #[Computed]
    public function groupUnread(): int
    {
        return $this->campaign->messages()->whereNull('player_character_id')->unreadBy(auth()->user())->count();
    }

    /** Personnage actif du joueur connecté : c'est depuis lui qu'il écrit au MJ. */
    #[Computed]
    public function myCharacter(): ?PlayerCharacter
    {
        return $this->campaign->playerCharacters()->active()->where('user_id', auth()->id())->with('entity')->first();
    }

    #[Computed]
    public function currentCharacter(): ?PlayerCharacter
    {
        return $this->conversation === '' ? null : $this->characters->firstWhere('id', (int) $this->conversation);
    }

    /** @return Collection<int, Message> les 200 derniers messages de la conversation, du plus ancien au plus récent */
    #[Computed]
    public function messages(): Collection
    {
        return Message::query()
            ->visibleTo(auth()->user(), $this->campaign)
            ->when($this->isGameMaster, fn ($q) => $this->conversation === ''
                ? $q->whereNull('player_character_id')
                : $q->where('player_character_id', (int) $this->conversation))
            ->with(['sender', 'character.entity', 'entity', 'document', 'rule'])
            ->latest('id')
            ->limit(200)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Éléments révélés aux personnages du joueur, par personnage : une pièce jointe
     * n'est un lien que si le personnage la connaît encore.
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, CharacterGrant>>
     */
    #[Computed]
    public function knowledge(): \Illuminate\Support\Collection
    {
        if ($this->isGameMaster) {
            return collect();
        }

        return CharacterGrant::query()
            ->whereIn('kind', ['entity', 'document', 'rule'])
            ->whereHas('character', fn ($q) => $q->where('campaign_id', $this->campaign->id)->where('user_id', auth()->id()))
            ->get()
            ->groupBy('player_character_id');
    }

    /**
     * Lien vers la pièce jointe pour la personne connectée, ou null si elle ne peut pas l'ouvrir.
     *
     * @return array{label: string, url: string}|null
     */
    public function reference(Message $message): ?array
    {
        $kind = match (true) {
            $message->entity_id !== null => 'entity',
            $message->document_id !== null => 'document',
            $message->rule_id !== null => 'rule',
            default => null,
        };

        if ($kind === null || $message->{$kind} === null) {
            return null;
        }

        $item = $message->{$kind};
        $label = $kind === 'entity' ? $item->name : $item->title;

        if ($this->isGameMaster) {
            return ['label' => $label, 'url' => match ($kind) {
                'entity' => route('entities.show', [$this->campaign, $item]),
                'document' => route('documents.show', [$this->campaign, $item]),
                'rule' => route('rules.show', [$this->campaign, $item]),
            }];
        }

        $character = $message->character ?? $this->myCharacter;
        $known = $character !== null && ($this->knowledge->get($character->id) ?? collect())
            ->contains(fn (CharacterGrant $grant) => $grant->kind === $kind && $grant->{$kind.'_id'} === $item->id);

        if (! $known || ($kind === 'rule' && $item->zone !== Zone::Public)) {
            return null;
        }

        return ['label' => $label, 'url' => match ($kind) {
            'entity' => route('characters.entity', [$this->campaign, $character, $item]),
            'document' => route('characters.document', [$this->campaign, $character, $item]),
            'rule' => route('characters.show', [$this->campaign, $character]).'#section-rule',
        }];
    }

    /** @return Collection<int, Document> */
    #[Computed]
    public function documents(): Collection
    {
        return $this->campaign->availableDocuments()->orderBy('title')->get(['id', 'title']);
    }

    /** @return Collection<int, Rule> règles publiques, les seules qu'on peut ouvrir aux joueurs */
    #[Computed]
    public function rules(): Collection
    {
        return $this->campaign->availableRules()->where('zone', Zone::Public)->orderBy('title')->get(['id', 'title']);
    }

    public function open(string $conversation): void
    {
        abort_unless($this->isGameMaster, 403);
        abort_unless($conversation === '' || $this->characters->contains('id', (int) $conversation), 404);

        $this->conversation = $conversation;
        $this->reset(['body', 'refKind', 'refEntityId', 'refDocumentId', 'refRuleId', 'selected', 'flash']);
        $this->resetValidation();
        unset($this->messages);
    }

    public function send(SendMessage $send): void
    {
        $this->validate([
            'body' => ['required', 'string', 'max:5000'],
            'refKind' => [ValidationRule::in($this->isGameMaster ? ['', 'entity', 'document', 'rule'] : [''])],
            'refEntityId' => [ValidationRule::requiredIf($this->refKind === 'entity'), 'nullable', 'integer'],
            'refDocumentId' => [ValidationRule::requiredIf($this->refKind === 'document'), ValidationRule::in(['', ...$this->documents->modelKeys()])],
            'refRuleId' => [ValidationRule::requiredIf($this->refKind === 'rule'), ValidationRule::in(['', ...$this->rules->modelKeys()])],
            'selected.*' => [ValidationRule::in($this->characters->modelKeys())],
        ], [
            'refEntityId.required' => 'Choisissez la fiche à joindre.',
            'refDocumentId.required' => 'Choisissez le document à joindre.',
            'refRuleId.required' => 'Choisissez la règle à joindre.',
        ], ['body' => 'message']);

        if ($this->isGameMaster) {
            $characterIds = $this->conversation !== '' ? [(int) $this->conversation] : array_map('intval', $this->selected);
        } else {
            abort_if($this->myCharacter === null, 403);
            $characterIds = [$this->myCharacter->id];
        }

        $reference = match ($this->refKind) {
            'entity' => ['kind' => 'entity', 'id' => (int) $this->refEntityId],
            'document' => ['kind' => 'document', 'id' => (int) $this->refDocumentId],
            'rule' => ['kind' => 'rule', 'id' => (int) $this->refRuleId],
            default => null,
        };

        $sent = $send->handle($this->campaign, auth()->user(), $characterIds, $this->body, $reference);

        $this->flash = $this->conversation === '' && $this->selected !== []
            ? 'Message privé envoyé à '.$sent->count().' personnage'.($sent->count() > 1 ? 's' : '').'.'
            : null;

        $this->reset(['body', 'refKind', 'refEntityId', 'refDocumentId', 'refRuleId', 'selected']);
        unset($this->messages, $this->characters, $this->knowledge);
    }

    /** Ce qui s'affiche est lu, avec les notifications de ces messages. */
    private function markRead(): void
    {
        $changed = auth()->user()->unreadNotifications()
            ->whereRaw("data->>'kind' = 'message'")
            ->whereRaw("(data->>'campaign_id')::bigint = ?", [$this->campaign->id])
            ->when($this->isGameMaster, fn ($q) => $this->conversation === ''
                ? $q->whereRaw("data->>'character_id' is null")
                : $q->whereRaw("(data->>'character_id')::bigint = ?", [(int) $this->conversation]))
            ->update(['read_at' => now()]);

        $unread = $this->messages->filter(fn (Message $message) => $message->sender_id !== auth()->id())->modelKeys();

        $now = now();
        $changed += $unread === [] ? 0 : DB::table('message_reads')->insertOrIgnore(array_map(fn (int $id) => [
            'message_id' => $id,
            'user_id' => auth()->id(),
            'read_at' => $now,
        ], $unread));

        if ($changed > 0) {
            unset($this->characters, $this->groupUnread);
            // L'en-tête a pu s'afficher avant : on lui demande de recompter.
            $this->dispatch('messages-read')->to(HeaderBadges::class);
        }
    }

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
    }

    public function render()
    {
        $this->markRead();

        return view('livewire.messages.index')->title('Messages · '.$this->campaign->name);
    }
}
