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
#[Layout('components.layouts.base')]
class Screen extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
    }

    public function render()
    {
        $this->campaign->refresh();

        return view('livewire.table.screen', [
            'display' => TableDisplay::current($this->campaign),
        ])->title('Écran de table · '.$this->campaign->name);
    }
}
