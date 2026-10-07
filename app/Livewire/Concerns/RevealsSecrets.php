<?php

namespace App\Livewire\Concerns;

use App\Actions\Characters\GiveToCharacters;
use App\Models\PlayerCharacter;
use App\Models\Secret;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

/**
 * Révéler un secret d'un clic à un personnage (ou à toute la table), ou le faire oublier.
 * Utilisé par la page Secrets et par le panneau des fiches, scènes et documents.
 */
trait RevealsSecrets
{
    /** @return Collection<int, PlayerCharacter> personnages actifs de la campagne */
    #[Computed]
    public function tableCharacters(): Collection
    {
        return $this->campaign->playerCharacters()->active()->with('entity')->get()
            ->sortBy(fn (PlayerCharacter $character) => mb_strtolower($character->entity->name))
            ->values();
    }

    /** Révèle à un personnage, ou à tous les personnages actifs si aucun n'est précisé. */
    public function revealSecret(int $secretId, ?int $characterId = null): void
    {
        $this->authorize('update', $this->campaign);

        $secret = $this->campaign->secrets()->findOrFail($secretId);
        $ids = $characterId === null
            ? $this->tableCharacters->modelKeys()
            : [$this->tableCharacters->find($characterId)?->id ?? abort(404)];

        app(GiveToCharacters::class)->handle($this->campaign, $ids, ['kind' => 'information', 'secret_id' => $secret->id]);
        $this->secretsChanged();
    }

    /** Annule la révélation pour un personnage : le secret quitte ses connaissances (noté au journal). */
    public function forgetSecret(int $secretId, int $characterId): void
    {
        $this->authorize('update', $this->campaign);

        $grant = Secret::query()->whereBelongsTo($this->campaign)->findOrFail($secretId)
            ->grants()->where('player_character_id', $characterId)->firstOrFail();

        GiveToCharacters::revoke($grant);
        $this->secretsChanged();
    }

    protected function secretsChanged(): void
    {
        $this->dispatch('character-grants-changed');
    }
}
