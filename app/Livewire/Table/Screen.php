<?php

namespace App\Livewire\Table;

use App\Models\Campaign;
use App\Models\MapToken;
use App\Support\Dice\TableDice;
use App\Support\TableAudio;
use App\Support\TableDisplay;
use App\Support\TableInitiative;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

/**
 * Écran de table : une fenêtre plein écran, à glisser sur le second écran (télé, projecteur).
 * Le MJ y pousse une carte, une image, une fiche ou une annonce depuis le mode Session.
 * Un joueur qui suit l'écran y déplace le jeton de son personnage et ceux que le MJ lui confie.
 * Par le lien invité, une personne sans compte le regarde, sans rien pouvoir y toucher.
 */
#[Layout('components.layouts.base', ['plain' => true])]
class Screen extends Component
{
    #[Locked]
    public Campaign $campaign;

    /** Jeton du lien invité ; null pour un membre connecté. */
    #[Locked]
    public ?string $guest = null;

    /** Musique jouée par l'écran ({url, loop, volume, playing, key}), envoyée à Alpine à chaque rendu. */
    #[Locked]
    public ?array $audio = null;

    public function mount(?Campaign $campaign = null, ?string $token = null): void
    {
        if ($token !== null) {
            $campaign = TableDisplay::guestCampaign($token);
            abort_if($campaign === null, 404);
            $this->guest = $token;
            $this->campaign = $campaign;

            return;
        }

        // Le MJ l'affiche sur la télé ; un joueur le suit sur son appareil si le MJ le partage.
        abort_if($campaign === null, 404);
        $this->authorize('view', $campaign);
        abort_unless(auth()->check() && TableDisplay::canWatch(auth()->user(), $campaign), 403);
        $this->campaign = $campaign;
    }

    /** @return array<string, string> mises à jour en direct (Reverb) ; l'invité, lui, se resynchronise seul */
    public function getListeners(): array
    {
        return $this->guest === null ? ['echo-private:users.'.auth()->id().',.activity' => '$refresh'] : [];
    }

    /** Le MJ tourne la page du PDF sur son écran : les joueurs qui suivent tournent avec lui. */
    public function turnTo(int $page): void
    {
        $this->authorize('update', $this->campaign);

        TableDisplay::turnTo($this->campaign->refresh(), $page);
    }

    public function knowPages(int $pages): void
    {
        $this->authorize('update', $this->campaign);

        TableDisplay::knowPages($this->campaign->refresh(), $pages);
    }

    /** Un participant pose son jeton ailleurs : vérifié ici, jamais sur la foi de l'interface. */
    #[Renderless]
    public function moveToken(int $id, float $x, float $y): void
    {
        abort_if($this->guest !== null || ! auth()->check(), 403);
        $this->campaign->refresh();
        $display = TableDisplay::current($this->campaign);
        abort_unless(($display['kind'] ?? null) === 'map', 404);

        $map = $display['map'];
        abort_unless(in_array($id, MapToken::movableIds(auth()->user(), $this->campaign, $map->tokens), true), 403);

        $map->tokens->find($id)->update([
            'x' => round(max(0, min($map->width, $x)), 2),
            'y' => round(max(0, min($map->height, $y)), 2),
        ]);
        TableDisplay::mapChanged($map);
    }

    private function watching(): bool
    {
        return $this->guest !== null
            ? $this->campaign->table_guest_token === $this->guest && TableDisplay::guestCanWatch($this->campaign)
            : TableDisplay::canWatch(auth()->user(), $this->campaign);
    }

    /** @return array{url: string, loop: bool, volume: int, playing: bool, key: string}|null */
    private function audioState(bool $watching): ?array
    {
        $audio = $watching && $this->guest === null && TableAudio::canHear(auth()->user(), $this->campaign) ? TableAudio::current($this->campaign) : null;

        return $audio === null ? null : [
            'url' => route('table.audio', [$this->campaign, 'v' => $audio['key']]),
            'loop' => $audio['loop'],
            'volume' => $audio['volume'],
            'playing' => $audio['playing'],
            'key' => $audio['key'],
        ];
    }

    /** Adresse d'un fichier affiché, par le lien invité ou par la campagne. */
    public function fileUrl(string $kind, array $parameters = [], ?MapToken $token = null): string
    {
        if ($this->guest !== null) {
            return route('table.guest.'.$kind, array_filter([$this->guest, 'mapToken' => $token?->id]) + $parameters);
        }

        return route('table.'.$kind, array_filter([$this->campaign, $token]) + $parameters);
    }

    public function render()
    {
        $this->campaign->refresh();
        // Le MJ a cessé de partager pendant que le joueur regardait : écran neutre, sans erreur.
        $watching = $this->watching();
        $this->audio = $this->audioState($watching);
        $this->dispatch('table-audio', state: $this->audio);
        $display = $watching ? TableDisplay::current($this->campaign) : null;

        return view('livewire.table.screen', [
            'display' => $display,
            'stopped' => ! $watching,
            'initiative' => $watching ? TableInitiative::forTable($this->campaign) : null,
            'dice' => $watching ? TableDice::forScreen($this->campaign) : null,
            'movable' => ($display['kind'] ?? null) === 'map' && $this->guest === null
                ? MapToken::movableIds(auth()->user(), $this->campaign, $display['map']->tokens)
                : [],
        ])->title(__('Écran de table · :name', ['name' => $this->campaign->name]));
    }
}
