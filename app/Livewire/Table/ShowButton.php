<?php

namespace App\Livewire\Table;

use App\Models\Campaign;
use App\Support\TableDisplay;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Bouton « Afficher à la table », posé sur les pages de contenu (fiche, portrait, illustration,
 * document, règle) pour envoyer l'élément sur l'écran de table sans passer par le mode Session.
 */
class ShowButton extends Component
{
    #[Locked]
    public Campaign $campaign;

    #[Locked]
    public string $kind;

    #[Locked]
    public int $itemId;

    public string $label = '';

    public bool $compact = false;

    public function mount(Campaign $campaign, string $kind, int $itemId): void
    {
        $this->authorize('update', $campaign);

        validator(['kind' => $kind], ['kind' => Rule::in(TableDisplay::KINDS)])->validate();
    }

    public function show(): void
    {
        $this->authorize('update', $this->campaign);

        abort_unless(TableDisplay::show($this->campaign, $this->kind, $this->itemId), 404);

        // Les autres boutons de la page ne sont plus « à la table ».
        $this->dispatch('table-changed');
    }

    #[On('table-changed')]
    public function refresh(): void {}

    public function render()
    {
        return view('livewire.table.show-button', [
            'showing' => TableDisplay::isShowing($this->campaign->fresh(), $this->kind, $this->itemId),
        ]);
    }
}
