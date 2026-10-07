<?php

namespace App\Livewire\Messages;

use App\Actions\Messages\SendMessage;
use App\Enums\Zone;
use App\Livewire\Concerns\ReadsMessages;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Collection;
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
    use ReadsMessages;
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

    /** Destinataire d'un joueur : gm (en privé) ou group (toute la table). */
    public string $to = 'gm';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('play', $campaign);

        if (! $this->isGameMaster) {
            $this->conversation = '';
        } elseif ($this->conversation !== '' && ! $this->characters->contains('id', (int) $this->conversation)) {
            $this->conversation = '';
        } elseif ($this->conversation === '' && $this->groupUnread === 0) {
            // Le MJ arrive directement sur la conversation qui attend une réponse.
            $this->conversation = (string) ($this->characters->firstWhere('unread', '>', 0)?->id ?? '');
        }
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
            ->with(['sender', 'senderCharacter.entity', 'character.entity', 'entity', 'document', 'rule'])
            ->latest('id')
            ->limit(200)
            ->get()
            ->reverse()
            ->values();
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
            'to' => [ValidationRule::in(['gm', 'group'])],
        ], [
            'refEntityId.required' => __('Choisissez la fiche à joindre.'),
            'refDocumentId.required' => __('Choisissez le document à joindre.'),
            'refRuleId.required' => __('Choisissez la règle à joindre.'),
        ], ['body' => __('message')]);

        if ($this->isGameMaster) {
            $characterIds = $this->conversation !== '' ? [(int) $this->conversation] : array_map('intval', $this->selected);
        } else {
            abort_if($this->myCharacter === null, 403);
            $characterIds = $this->to === 'group' ? [] : [$this->myCharacter->id];
        }

        $reference = match ($this->refKind) {
            'entity' => ['kind' => 'entity', 'id' => (int) $this->refEntityId],
            'document' => ['kind' => 'document', 'id' => (int) $this->refDocumentId],
            'rule' => ['kind' => 'rule', 'id' => (int) $this->refRuleId],
            default => null,
        };

        $sent = $send->handle($this->campaign, auth()->user(), $characterIds, $this->body, $reference);

        $this->flash = $this->conversation === '' && $this->selected !== []
            ? trans_choice('Message privé envoyé à :count personnage.|Message privé envoyé à :count personnages.', $sent->count())
            : null;

        $this->reset(['body', 'refKind', 'refEntityId', 'refDocumentId', 'refRuleId', 'selected']);
        unset($this->messages, $this->characters, $this->knowledge);
    }

    /** Ce qui s'affiche est lu, avec les notifications de ces messages. */
    private function markRead(): void
    {
        $scope = match (true) {
            ! $this->isGameMaster => null,
            $this->conversation === '' => 'group',
            default => (int) $this->conversation,
        };

        if (Message::markRead(auth()->user(), $this->campaign, $this->messages, $scope) > 0) {
            unset($this->characters, $this->groupUnread);
            // L'en-tête a pu s'afficher avant : on lui demande de recompter.
            $this->dispatch('messages-read');
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

        return view('livewire.messages.index')->title(__('Messages · :name', ['name' => $this->campaign->name]));
    }
}
