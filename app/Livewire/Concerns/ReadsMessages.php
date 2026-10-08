<?php

namespace App\Livewire\Concerns;

use App\Enums\Zone;
use App\Models\CharacterGrant;
use App\Models\Message;
use App\Models\PlayerCharacter;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Lecture des messages d'une campagne (page Messages et panneau de discussion).
 * Le composant doit exposer une propriété publique $campaign.
 */
trait ReadsMessages
{
    #[Computed]
    public function isGameMaster(): bool
    {
        return $this->campaign->isGameMaster(auth()->user());
    }

    /** Personnage actif du joueur connecté : c'est depuis lui qu'il écrit au MJ. */
    #[Computed]
    public function myCharacter(): ?PlayerCharacter
    {
        return $this->campaign->playerCharacters()->active()->where('user_id', auth()->id())->with('entity')->first();
    }

    /**
     * Éléments révélés aux personnages du joueur, par personnage : une pièce jointe
     * n'est un lien que si le personnage la connaît encore.
     *
     * @return Collection<int, Collection<int, CharacterGrant>>
     */
    #[Computed]
    public function knowledge(): Collection
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
        // Appelable depuis le navigateur avec n'importe quel identifiant : on reste dans la campagne.
        abort_unless($message->campaign_id === $this->campaign->id, 404);

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
}
