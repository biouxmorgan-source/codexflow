<?php

namespace App\Livewire;

use App\Actions\Messages\SendMessage;
use App\Livewire\Concerns\ReadsMessages;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\PlayerCharacter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Panneau de discussion ancré en bas de l'écran, sur toutes les pages d'une campagne.
 * Onglets : « Groupe » et un par personnage pour le MJ ; « MJ » et « Groupe » pour le joueur.
 * Mêmes règles de visibilité que la page Messages (Message::visibleTo).
 */
class ChatDock extends Component
{
    use ReadsMessages;

    #[Locked]
    public Campaign $campaign;

    public bool $open = false;

    /** Onglet : group, gm (joueur) ou identifiant d'un personnage (MJ). */
    public string $tab = 'group';

    public string $body = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('view', $campaign);

        $saved = session('chat.'.$campaign->id, []);
        $this->open = (bool) ($saved['open'] ?? false);
        $this->tab = (string) ($saved['tab'] ?? ($this->isGameMaster ? 'group' : 'gm'));

        if (! $this->tabs->contains('key', $this->tab)) {
            $this->tab = $this->tabs->first()['key'];
        }
    }

    /**
     * Onglets avec leur nombre de non-lus.
     *
     * @return \Illuminate\Support\Collection<int, array{key: string, label: string, unread: int}>
     */
    #[Computed]
    public function tabs(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        $group = ['key' => 'group', 'label' => 'Groupe', 'unread' => $this->scoped('group')->unreadBy($user)->count()];

        if (! $this->isGameMaster) {
            return collect([
                ['key' => 'gm', 'label' => 'MJ', 'unread' => $this->scoped('gm')->unreadBy($user)->count()],
                $group,
            ]);
        }

        $characters = $this->campaign->playerCharacters()
            ->where(fn ($q) => $q->where(fn ($q) => $q->where('is_active', true)->whereNotNull('user_id'))->orWhereHas('messages'))
            ->with('entity')
            ->withCount(['messages as unread' => fn ($q) => $q->unreadBy($user)])
            ->get()
            ->sortBy(fn (PlayerCharacter $character) => mb_strtolower($character->entity->name));

        return collect([$group])->concat($characters->map(fn (PlayerCharacter $character) => [
            'key' => (string) $character->id,
            'label' => $character->entity->name,
            'unread' => (int) $character->unread,
        ])->values());
    }

    #[Computed]
    public function unread(): int
    {
        return $this->tabs->sum('unread');
    }

    /** @return Collection<int, Message> les 40 derniers messages de l'onglet */
    #[Computed]
    public function messages(): Collection
    {
        return $this->scoped($this->tab)
            ->with(['sender', 'senderCharacter.entity', 'character.entity', 'entity', 'document', 'rule'])
            ->latest('id')
            ->limit(40)
            ->get()
            ->reverse()
            ->values();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
        $this->remember();
    }

    public function select(string $tab): void
    {
        abort_unless($this->tabs->contains('key', $tab), 404);

        $this->tab = $tab;
        $this->open = true;
        $this->resetValidation();
        $this->remember();
    }

    public function send(SendMessage $send): void
    {
        $this->validate(['body' => ['required', 'string', 'max:5000']], [], ['body' => 'message']);
        abort_unless($this->tabs->contains('key', $this->tab), 404);

        $characterIds = match (true) {
            $this->tab === 'group' => [],
            $this->tab === 'gm' => [$this->myCharacter?->id ?? abort(403)],
            default => [(int) $this->tab],
        };

        $send->handle($this->campaign, auth()->user(), $characterIds, $this->body);

        $this->reset('body');
        unset($this->messages, $this->tabs, $this->unread);
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            'echo-private:users.'.auth()->id().',.activity' => '$refresh',
            'messages-read' => '$refresh',
        ];
    }

    /** @return Builder<Message> messages visibles de l'onglet */
    private function scoped(string $tab): Builder
    {
        $query = Message::query()->visibleTo(auth()->user(), $this->campaign);

        return match ($tab) {
            'group' => $query->whereNull('player_character_id'),
            'gm' => $query->whereNotNull('player_character_id'),
            default => $query->where('player_character_id', (int) $tab),
        };
    }

    private function remember(): void
    {
        session(['chat.'.$this->campaign->id => ['open' => $this->open, 'tab' => $this->tab]]);
    }

    public function render()
    {
        if ($this->open) {
            $scope = match ($this->tab) {
                'group' => 'group',
                'gm' => $this->myCharacter?->id,
                default => (int) $this->tab,
            };

            if (Message::markRead(auth()->user(), $this->campaign, $this->messages, $scope) > 0) {
                unset($this->tabs, $this->unread);
                $this->dispatch('messages-read')->to(HeaderBadges::class);
            }

            $this->dispatch('chat-updated');
        }

        return view('livewire.chat-dock');
    }
}
