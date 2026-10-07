<?php

namespace App\Livewire\Characters;

use App\Actions\Characters\GiveToCharacters;
use App\Models\Campaign;
use App\Models\PlayerCharacter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Panneau « Révéler / Donner » du MJ, posé sur une fiche, un document ou un personnage.
 */
class Give extends Component
{
    #[Locked]
    public Campaign $campaign;

    /** Nature imposée par la page (entity, document, rule) ; vide = information ou objet au choix. */
    #[Locked]
    public string $fixedKind = '';

    #[Locked]
    public ?int $entityId = null;

    #[Locked]
    public ?int $documentId = null;

    #[Locked]
    public ?int $ruleId = null;

    /** Personnage visé d'office (page d'un personnage). */
    #[Locked]
    public ?int $characterId = null;

    public string $kind = 'information';

    /** @var list<int|string> */
    public array $selected = [];

    public string $title = '';

    public string $body = '';

    public string $quantity = '';

    public ?string $flash = null;

    public function mount(): void
    {
        $this->authorize('update', $this->campaign);
        $this->kind = $this->fixedKind ?: 'information';

        if ($this->characterId) {
            $this->selected = [$this->characterId];
        }
    }

    /** @return Collection<int, PlayerCharacter> personnages actifs, avec ceux qui ont déjà l'élément */
    #[Computed]
    public function characters(): Collection
    {
        return $this->campaign->playerCharacters()
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $this->characterId))
            ->with(['entity', 'player'])
            ->withExists(['grants as already' => fn ($q) => $q
                ->when($this->fixedKind === 'entity', fn ($q) => $q->where('entity_id', $this->entityId))
                ->when($this->fixedKind === 'document', fn ($q) => $q->where('document_id', $this->documentId))
                ->when($this->fixedKind === 'rule', fn ($q) => $q->where('rule_id', $this->ruleId))
                ->when($this->fixedKind === '', fn ($q) => $q->whereRaw('false'))])
            ->get()
            ->sortBy(fn (PlayerCharacter $character) => mb_strtolower($character->entity->name))
            ->values();
    }

    public function give(GiveToCharacters $give): void
    {
        $this->authorize('update', $this->campaign);
        $free = $this->fixedKind === '';

        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => [Rule::in($this->characters->modelKeys())],
            'kind' => [Rule::in($free ? ['information', 'possession'] : [$this->fixedKind])],
            'title' => [$free ? 'required' : 'nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ], [
            'selected.required' => 'Choisissez au moins un personnage.',
        ], ['title' => $this->kind === 'possession' ? 'objet' : 'titre', 'body' => 'texte', 'quantity' => 'quantité']);

        $count = $give->handle($this->campaign, array_map('intval', $this->selected), [
            'kind' => $this->kind,
            'entity_id' => $this->entityId,
            'document_id' => $this->documentId,
            'rule_id' => $this->ruleId,
            'title' => $this->title,
            'body' => $this->body,
            'quantity' => $this->quantity === '' ? null : (int) $this->quantity,
        ]);

        $this->flash = match (true) {
            $count === 0 => 'Ces personnages l\'avaient déjà.',
            in_array($this->kind, ['entity', 'information', 'rule'], true) => 'Révélé à '.$count.' personnage'.($count > 1 ? 's' : '').'.',
            default => 'Donné à '.$count.' personnage'.($count > 1 ? 's' : '').'.',
        };

        $this->reset(['title', 'body', 'quantity']);
        $this->selected = $this->characterId ? [$this->characterId] : [];
        unset($this->characters);
        $this->dispatch('character-grants-changed');
    }

    public function render()
    {
        return view('livewire.characters.give');
    }
}
