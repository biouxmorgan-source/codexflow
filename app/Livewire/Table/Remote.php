<?php

namespace App\Livewire\Table;

use App\Models\Campaign;
use App\Models\TableMap;
use App\Support\CampaignFeatures;
use App\Support\SessionContext;
use App\Support\TableDisplay;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Télécommande de l'écran de table, pensée pour le téléphone du MJ : montrer ou retirer,
 * passer à l'élément suivant de la scène, piloter la carte affichée, sans revenir à l'ordinateur.
 */
class Remote extends Component
{
    public Campaign $campaign;

    public string $text = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
    }

    /**
     * Ce que la scène en cours prépare pour la table, dans l'ordre : fiches, documents, règles.
     *
     * @return Collection<int, array{kind: string, id: int, label: string, type: string}>
     */
    #[Computed]
    public function sceneItems(): Collection
    {
        $scene = $this->campaign->openSession()?->currentScene;

        $items = SessionContext::cards($this->campaign, $scene)->map(fn (array $card) => [
            'kind' => 'entity', 'id' => $card['entity']->id, 'label' => $card['entity']->name, 'type' => __('Fiche'),
        ]);
        $documents = SessionContext::documents($this->campaign, $scene)->map(fn ($document) => [
            'kind' => 'document', 'id' => $document->id, 'label' => $document->title, 'type' => __('Document'),
        ]);
        $rules = SessionContext::rules($this->campaign, $scene)->map(fn ($rule) => [
            'kind' => 'rule', 'id' => $rule->id, 'label' => $rule->title, 'type' => __('Règle'),
        ]);

        return $items->concat($documents)->concat($rules)->values();
    }

    public function show(string $kind, int $id): void
    {
        $this->authorize('update', $this->campaign);

        abort_unless(in_array($kind, TableDisplay::KINDS, true) && TableDisplay::show($this->campaign, $kind, $id), 404);
    }

    /** Position de l'élément de la scène affiché à la table, null si rien de la scène n'est affiché. */
    private function shownIndex(): ?int
    {
        $index = $this->sceneItems->search(fn (array $item) => TableDisplay::isShowing($this->campaign, $item['kind'], $item['id']));

        return $index === false ? null : $index;
    }

    /** Le dernier élément de la scène est affiché : « Suivant » devient « Terminer ». */
    private function atLastItem(): bool
    {
        return $this->shownIndex() !== null && $this->shownIndex() === $this->sceneItems->count() - 1;
    }

    /** L'élément de la scène qui suit celui affiché (le premier si rien n'est affiché) ; après le dernier, l'écran est vidé. */
    public function next(): void
    {
        $this->authorize('update', $this->campaign);

        if ($this->atLastItem()) {
            TableDisplay::clear($this->campaign);

            return;
        }

        $index = $this->shownIndex();
        $next = $this->sceneItems->get($index === null ? 0 : $index + 1);

        if ($next !== null) {
            TableDisplay::show($this->campaign, $next['kind'], $next['id']);
        }
    }

    public function clear(): void
    {
        $this->authorize('update', $this->campaign);

        TableDisplay::clear($this->campaign);
    }

    public function toggleShare(): void
    {
        $this->authorize('update', $this->campaign);

        TableDisplay::share($this->campaign, ! $this->campaign->table_shared);
    }

    public function setTheme(string $theme): void
    {
        $this->authorize('update', $this->campaign);

        TableDisplay::theme($this->campaign, $theme);
    }

    public function announce(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate(['text' => ['required', 'string', 'max:500']], attributes: ['text' => __('annonce')]);
        TableDisplay::showText($this->campaign, trim($this->text));
        $this->reset('text');
    }

    /** Déplace la vue de la carte affichée d'un quart d'écran. */
    public function pan(int $dx, int $dy): void
    {
        $map = $this->shownMap();
        [$x, $y, $w, $h] = $map->viewBox();
        $map->setView($x + $w / 2 + max(-1, min(1, $dx)) * $w / 4, $y + $h / 2 + max(-1, min(1, $dy)) * $h / 4, $map->view_zoom);
        $map->save();
        TableDisplay::mapChanged($map);
    }

    public function zoom(bool $in): void
    {
        $map = $this->shownMap();
        [$x, $y, $w, $h] = $map->viewBox();
        $map->setView($x + $w / 2, $y + $h / 2, $map->view_zoom * ($in ? 1.25 : 0.8));
        $map->save();
        TableDisplay::mapChanged($map);
    }

    public function fit(): void
    {
        $map = $this->shownMap();
        $map->setView($map->width / 2, $map->height / 2, 1);
        $map->save();
        TableDisplay::mapChanged($map);
    }

    public function toggleToken(int $id): void
    {
        $map = $this->shownMap();
        $token = $map->tokens()->findOrFail($id);
        $token->update(['hidden' => ! $token->hidden]);
        TableDisplay::mapChanged($map);
    }

    public function clearRuler(): void
    {
        $map = $this->shownMap();
        $map->forceFill(['ruler' => null])->save();
        TableDisplay::mapChanged($map);
    }

    private function shownMap(): TableMap
    {
        $this->authorize('update', $this->campaign);

        $state = $this->campaign->fresh()->table_display;
        abort_unless(($state['kind'] ?? null) === 'map', 404);

        return $this->campaign->maps()->findOrFail((int) $state['id']);
    }

    public function render()
    {
        $this->campaign->refresh();
        $state = $this->campaign->table_display;
        $map = ($state['kind'] ?? null) === 'map' ? $this->campaign->maps()->with(['document', 'tokens.entity'])->find((int) $state['id']) : null;

        return view('livewire.table.remote', [
            'label' => TableDisplay::label($this->campaign),
            'shown' => TableDisplay::current($this->campaign) !== null,
            'scene' => $this->campaign->openSession()?->currentScene,
            'map' => $map,
            'maps' => CampaignFeatures::enabled($this->campaign, 'maps') ? $this->campaign->maps()->get(['id', 'name', 'campaign_id']) : collect(),
            'position' => $this->shownIndex(),
            'atLastItem' => $this->atLastItem(),
        ])->title(__('Télécommande · :name', ['name' => $this->campaign->name]));
    }
}
