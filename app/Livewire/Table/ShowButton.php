<?php

namespace App\Livewire\Table;

use App\Enums\Zone;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Rule as RuleModel;
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

    /** Document PDF à la table : on en tourne les pages depuis sa page. */
    public function turn(int $delta): void
    {
        $this->authorize('update', $this->campaign);
        abort_unless($this->kind === 'document' && TableDisplay::isShowing($this->campaign->refresh(), 'document', $this->itemId), 404);

        TableDisplay::turn($this->campaign, $delta);
    }

    #[On('table-changed')]
    public function refresh(): void {}

    /** L'écran change ailleurs (télécommande, autre onglet) : l'indicateur suit en direct. */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => 'refresh'];
    }

    public function render()
    {
        $campaign = $this->campaign->fresh();
        $showing = TableDisplay::isShowing($campaign, $this->kind, $this->itemId);

        return view('livewire.table.show-button', [
            'showing' => $showing,
            'pdfPages' => $showing && $this->kind === 'document' && TableDisplay::showsPdf($campaign)
                ? ['page' => TableDisplay::page($campaign), 'pages' => TableDisplay::pages($campaign)]
                : null,
            'gmOnly' => $this->isGameMasterOnly(),
        ]);
    }

    /** Pièce jointe ou règle de la zone MJ : le MJ confirme avant de la montrer à toute la table. */
    private function isGameMasterOnly(): bool
    {
        $model = match ($this->kind) {
            'attachment' => Attachment::find($this->itemId),
            'rule' => RuleModel::find($this->itemId),
            default => null,
        };

        return $model?->zone === Zone::GameMaster;
    }
}
