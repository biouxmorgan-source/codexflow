<?php

namespace App\Livewire\Maps;

use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\MapPoint;
use App\Models\Scene;
use App\Models\TableMap;
use App\Support\TableDisplay;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

/**
 * La carte de campagne : la vue d'ensemble, avec des points d'intérêt posés par le MJ. Un point
 * mène à une sous-carte (un clic l'ouvre, et l'on remonte par le fil d'Ariane), à une scène,
 * jouée ou à venir, ou à une fiche. Tout est au MJ : seuls les noms des points « visibles à la
 * table » apparaissent sur l'écran de table, quand la carte y est affichée.
 */
class Atlas extends Component
{
    use SuggestsEntities;

    #[Locked]
    public Campaign $campaign;

    #[Locked]
    public ?TableMap $map = null;

    /** « Placer des points » : un clic sur la carte pose un point, on les glisse pour les déplacer. */
    public bool $editing = false;

    #[Locked]
    public ?int $selectedId = null;

    public string $pointLabel = '';

    public string $pointNote = '';

    public string $pointTarget = '';

    public string $pointScene = '';

    /** Fiche choisie dans le sélecteur ; la fiche déjà liée reste tant qu'on n'en choisit pas d'autre. */
    public ?int $pointEntityId = null;

    public bool $pointShown = false;

    public function mount(Campaign $campaign, ?TableMap $map = null): void
    {
        $this->authorize('update', $campaign);
        abort_if($map !== null && $map->campaign_id !== $campaign->id, 404);

        $this->map = $map ?? $campaign->campaignMap;
    }

    /** @return Collection<int, TableMap> */
    #[Computed]
    public function maps(): Collection
    {
        return $this->campaign->maps()->get();
    }

    /** @return Collection<int, Scene> scènes de la campagne, dans l'ordre des scénarios */
    #[Computed]
    public function scenes(): Collection
    {
        return $this->campaign->scenes()->with('scenario')
            ->orderBy('scenarios.position')->orderBy('scenarios.id')->orderBy('scenes.position')->orderBy('scenes.id')
            ->get();
    }

    #[Computed]
    public function selected(): ?MapPoint
    {
        return $this->selectedId === null ? null : $this->map?->points()->with(['target', 'scene', 'entity'])->find($this->selectedId);
    }

    /** Cette carte devient la carte de campagne, celle qui s'ouvre en premier. */
    public function makeCampaignMap(): void
    {
        $map = $this->currentMap();

        DB::transaction(function () use ($map) {
            $this->campaign->maps()->whereKeyNot($map->id)->where('is_campaign_map', true)->update(['is_campaign_map' => false]);
            $map->forceFill(['is_campaign_map' => true])->save();
        });
    }

    /** Un point posé là où le MJ a cliqué, aussitôt ouvert pour le nommer et le relier. */
    public function addPoint(float $x, float $y): void
    {
        $map = $this->currentMap();
        $count = $map->points()->count();

        $point = $map->points()->create([
            'label' => __('Nouveau point'),
            'color' => MapPoint::COLORS[$count % count(MapPoint::COLORS)],
            'x' => round(max(0, min($map->width, $x)), 2),
            'y' => round(max(0, min($map->height, $y)), 2),
            'shown_at_table' => false,
        ]);

        $this->select($point->id);
        $this->dispatch('point-added');
    }

    #[Renderless]
    public function movePoint(int $id, float $x, float $y): void
    {
        $point = $this->point($id);
        $point->update([
            'x' => round(max(0, min($this->map->width, $x)), 2),
            'y' => round(max(0, min($this->map->height, $y)), 2),
        ]);

        if ($point->shown_at_table) {
            TableDisplay::mapChanged($this->map);
        }
    }

    public function select(int $id): void
    {
        $point = $this->point($id);

        $this->selectedId = $point->id;
        $this->fill([
            'pointLabel' => $point->label,
            'pointNote' => (string) $point->note,
            'pointTarget' => (string) $point->target_map_id,
            'pointScene' => (string) $point->scene_id,
            'pointEntityId' => null,
            'pointShown' => $point->shown_at_table,
        ]);
        $this->resetValidation();
        unset($this->selected);
    }

    public function deselect(): void
    {
        $this->selectedId = null;
        unset($this->selected);
    }

    public function savePoint(): void
    {
        $point = $this->point((int) $this->selectedId);

        $this->validate([
            'pointLabel' => ['required', 'string', 'max:100'],
            'pointNote' => ['nullable', 'string', 'max:5000'],
            'pointTarget' => ['nullable', Rule::in($this->maps->modelKeys())],
            'pointScene' => ['nullable', Rule::in($this->scenes->modelKeys())],
        ], attributes: ['pointLabel' => __('nom'), 'pointNote' => __('note'), 'pointTarget' => __('sous-carte'), 'pointScene' => __('scène')]);

        if ($this->pointTarget === (string) $this->map->id) {
            $this->addError('pointTarget', __('Un point ne peut pas mener à sa propre carte.'));

            return;
        }

        $entity = $this->pointEntityId === null ? null : $this->campaign->availableEntities()->find($this->pointEntityId);

        $point->fill([
            'label' => trim($this->pointLabel),
            'note' => trim($this->pointNote) ?: null,
            'shown_at_table' => $this->pointShown,
        ]);
        $point->target_map_id = $this->pointTarget === '' ? null : (int) $this->pointTarget;
        $point->scene_id = $this->pointScene === '' ? null : (int) $this->pointScene;

        if ($entity !== null) {
            $point->entity()->associate($entity);
        }

        $point->save();
        $this->pointEntityId = null;
        unset($this->selected);
        TableDisplay::mapChanged($this->map);
        $this->dispatch('point-saved');
    }

    public function removeEntity(): void
    {
        $point = $this->point((int) $this->selectedId);
        $point->entity()->dissociate()->save();
        unset($this->selected);
    }

    /** Couleur suivante dans la palette. */
    public function recolorPoint(int $id): void
    {
        $point = $this->point($id);
        $index = array_search($point->color, MapPoint::COLORS, true);
        $point->update(['color' => MapPoint::COLORS[($index === false ? 0 : $index + 1) % count(MapPoint::COLORS)]]);
        TableDisplay::mapChanged($this->map);
    }

    public function toggleShown(int $id): void
    {
        $point = $this->point($id);
        $point->update(['shown_at_table' => ! $point->shown_at_table]);

        if ($this->selectedId === $point->id) {
            $this->pointShown = $point->shown_at_table;
        }

        TableDisplay::mapChanged($this->map);
    }

    public function deletePoint(int $id): void
    {
        $this->point($id)->delete();

        if ($this->selectedId === $id) {
            $this->deselect();
        }

        TableDisplay::mapChanged($this->map);
    }

    private function currentMap(): TableMap
    {
        $this->authorize('update', $this->campaign);
        abort_if($this->map === null, 404);

        return $this->map;
    }

    private function point(int $id): MapPoint
    {
        return $this->currentMap()->points()->findOrFail($id);
    }

    public function render()
    {
        $map = $this->map?->load(['document', 'points' => fn ($q) => $q->with(['target', 'scene', 'entity'])]);

        return view('livewire.maps.atlas', [
            'points' => $map?->points ?? collect(),
            'parents' => $map?->parents() ?? [],
            // Les cartes dont un point mène ici, quand il y en a plusieurs : le fil d'Ariane n'en suit qu'une.
            'leadingHere' => $map === null ? collect() : $map->pointsLeadingHere()->with('map')->get()->pluck('map')->unique('id')->values(),
        ])->title($map === null
            ? __('Carte de campagne · :name', ['name' => $this->campaign->name])
            : __(':map · Carte de campagne · :name', ['map' => $map->name, 'name' => $this->campaign->name]));
    }
}
