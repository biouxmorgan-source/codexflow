<?php

namespace App\Livewire\Table;

use App\Models\Campaign;
use App\Support\TableAudio;
use App\Support\TableDisplay;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Écran de table : une fenêtre plein écran, à glisser sur le second écran (télé, projecteur).
 * Le MJ y pousse une carte, une image, une fiche ou une annonce depuis le mode Session.
 */
#[Layout('components.layouts.base', ['plain' => true])]
class Screen extends Component
{
    public Campaign $campaign;

    /** Musique jouée par l'écran ({url, loop, volume, playing, key}), envoyée à Alpine à chaque rendu. */
    #[Locked]
    public ?array $audio = null;

    public function mount(Campaign $campaign): void
    {
        // Le MJ l'affiche sur la télé ; un joueur le suit sur son appareil si le MJ le partage.
        abort_unless(TableDisplay::canWatch(auth()->user(), $campaign), 403);
    }

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
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

    /** @return array{url: string, loop: bool, volume: int, playing: bool, key: string}|null */
    private function audioState(bool $watching): ?array
    {
        $audio = $watching && TableAudio::canHear(auth()->user(), $this->campaign) ? TableAudio::current($this->campaign) : null;

        return $audio === null ? null : [
            'url' => route('table.audio', [$this->campaign, 'v' => $audio['key']]),
            'loop' => $audio['loop'],
            'volume' => $audio['volume'],
            'playing' => $audio['playing'],
            'key' => $audio['key'],
        ];
    }

    public function render()
    {
        $this->campaign->refresh();
        // Le MJ a cessé de partager pendant que le joueur regardait : écran neutre, sans erreur.
        $watching = TableDisplay::canWatch(auth()->user(), $this->campaign);
        $this->audio = $this->audioState($watching);
        $this->dispatch('table-audio', state: $this->audio);

        return view('livewire.table.screen', [
            'display' => $watching ? TableDisplay::current($this->campaign) : null,
            'stopped' => ! $watching,
        ])->title(__('Écran de table · :name', ['name' => $this->campaign->name]));
    }
}
