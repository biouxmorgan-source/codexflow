<?php

namespace App\Livewire\Table;

use App\Models\Campaign;
use App\Support\TableDisplay;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Écran de table : une fenêtre plein écran, à glisser sur le second écran (télé, projecteur).
 * Le MJ y pousse une carte, une image, une fiche ou une annonce depuis le mode Session.
 */
#[Layout('components.layouts.base', ['plain' => true])]
class Screen extends Component
{
    public Campaign $campaign;

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

    public function render()
    {
        $this->campaign->refresh();
        // Le MJ a cessé de partager pendant que le joueur regardait : écran neutre, sans erreur.
        $watching = TableDisplay::canWatch(auth()->user(), $this->campaign);

        return view('livewire.table.screen', [
            'display' => $watching ? TableDisplay::current($this->campaign) : null,
            'stopped' => ! $watching,
        ])->title(__('Écran de table · :name', ['name' => $this->campaign->name]));
    }
}
