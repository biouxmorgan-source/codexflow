<?php

namespace App\Livewire\Table;

use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Support\TableDisplay;
use App\Support\TableInitiative;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Ordre d'initiative, côté MJ (télécommande, carte) : préparer la liste, la montrer ou la cacher
 * sur l'écran de table, passer au tour suivant.
 */
class Initiative extends Component
{
    use SuggestsEntities;

    #[Locked]
    public Campaign $campaign;

    public ?int $entityId = null;

    public string $label = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
    }

    public function add(bool $ordered = true): void
    {
        $this->authorize('update', $this->campaign);

        $entity = $this->entityId ? $this->campaign->availableEntities()->find($this->entityId) : null;
        $label = trim($this->label) ?: $entity?->name;

        if ($label === null || $label === '') {
            $this->addError('label', __('Choisissez une fiche ou donnez un nom.'));

            return;
        }

        TableInitiative::add($this->campaign, $label, $entity, ordered: $ordered);
        $this->reset('entityId', 'label');
    }

    public function addCharacters(): void
    {
        $this->authorize('update', $this->campaign);
        TableInitiative::addCharacters($this->campaign);
    }

    /** Les jetons de la carte affichée à la table. */
    public function addTokens(): void
    {
        $this->authorize('update', $this->campaign);
        $map = $this->shownMap();
        abort_if($map === null, 404);

        TableInitiative::addTokens($this->campaign, $map->tokens()->with('entity')->get());
    }

    public function setValue(int $id, string $value): void
    {
        $this->authorize('update', $this->campaign);
        $value = str_replace(',', '.', trim($value));
        abort_unless($value === '' || (is_numeric($value) && abs((float) $value) < 100000), 422);

        TableInitiative::update($this->campaign, $id, ['value' => $value === '' ? null : round((float) $value, 2)]);
    }

    public function toggleVisible(int $id): void
    {
        $this->authorize('update', $this->campaign);
        $entry = $this->entry($id);
        TableInitiative::update($this->campaign, $id, ['visible' => ! $entry['visible']]);
    }

    /** Avec ou sans initiative (un PNJ présent, un objet). */
    public function toggleOrdered(int $id): void
    {
        $this->authorize('update', $this->campaign);
        $entry = $this->entry($id);
        TableInitiative::update($this->campaign, $id, ['ordered' => ! $entry['ordered']]);
    }

    public function shift(int $id, int $delta): void
    {
        $this->authorize('update', $this->campaign);
        TableInitiative::shift($this->campaign, $id, $delta);
    }

    public function remove(int $id): void
    {
        $this->authorize('update', $this->campaign);
        TableInitiative::remove($this->campaign, $id);
    }

    public function sort(): void
    {
        $this->authorize('update', $this->campaign);
        TableInitiative::sort($this->campaign);
    }

    public function step(int $delta = 1): void
    {
        $this->authorize('update', $this->campaign);
        TableInitiative::step($this->campaign, $delta);
    }

    public function toggleShown(): void
    {
        $this->authorize('update', $this->campaign);
        TableInitiative::show($this->campaign, ! TableInitiative::state($this->campaign)['shown']);
    }

    public function resetAll(): void
    {
        $this->authorize('update', $this->campaign);
        TableInitiative::reset($this->campaign);
    }

    /** @return array<string, mixed> */
    private function entry(int $id): array
    {
        $entry = collect(TableInitiative::state($this->campaign)['entries'])->firstWhere('id', $id);
        abort_if($entry === null, 404);

        return $entry;
    }

    private function shownMap()
    {
        $current = TableDisplay::current($this->campaign);

        return ($current['kind'] ?? null) === 'map' ? $current['map'] : null;
    }

    public function render()
    {
        $this->campaign->refresh();

        return view('livewire.table.initiative', [
            'state' => TableInitiative::state($this->campaign),
            'hasMap' => $this->shownMap() !== null,
        ]);
    }
}
