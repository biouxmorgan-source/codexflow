<?php

namespace App\Livewire\Maps;

use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\MapPoint;
use App\Models\Scene;
use App\Models\TableMap;
use App\Support\Demo\DemoLock;
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
 * mène à une sous-carte (un clic l'ouvre, et l'on remonte par le fil d'Ariane) et se relie à
 * plusieurs scènes, jouées ou à venir, et fiches. Tout est au MJ : seuls les noms des points
 * « visibles à la table » apparaissent sur l'écran de table, quand la carte y est affichée.
 * Si la carte est à la table, la sous-carte ouverte depuis un point l'y remplace, et de même en remontant.
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

    /** Fiche choisie dans le sélecteur, ajoutée au point par « Ajouter ». */
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
        return $this->selectedId === null ? null : $this->map?->points()->with(['target', 'scenes', 'entities'])->find($this->selectedId);
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
        ], attributes: ['pointLabel' => __('nom'), 'pointNote' => __('note'), 'pointTarget' => __('sous-carte')]);

        if ($this->pointTarget === (string) $this->map->id) {
            $this->addError('pointTarget', __('Un point ne peut pas mener à sa propre carte.'));

            return;
        }

        $point->fill([
            'label' => trim($this->pointLabel),
            'note' => trim($this->pointNote) ?: null,
            'shown_at_table' => $this->pointShown,
        ]);
        $point->target_map_id = $this->pointTarget === '' ? null : (int) $this->pointTarget;
        $point->save();
        unset($this->selected);
        TableDisplay::mapChanged($this->map);
        $this->dispatch('point-saved');
    }

    /** Une scène de plus qui se joue ici. */
    public function addScene(string $sceneId): void
    {
        $point = $this->point((int) $this->selectedId);
        $scene = $this->scenes->find((int) $sceneId);
        abort_if($scene === null, 422);

        DemoLock::guardCampaign($this->campaign);
        $point->link('scenes', $scene->id);
        unset($this->selected);
    }

    /** La fiche choisie dans le sélecteur rejoint le lieu. */
    public function addEntity(): void
    {
        $point = $this->point((int) $this->selectedId);
        $entity = $this->pointEntityId === null ? null : $this->campaign->availableEntities()->find($this->pointEntityId);

        if ($entity === null) {
            $this->addError('pointEntityId', __('Choisissez une fiche dans la liste.'));

            return;
        }

        DemoLock::guardCampaign($this->campaign);
        $point->link('entities', $entity->id);
        $this->pointEntityId = null;
        unset($this->selected);
    }

    /** Retire une scène ou une fiche du lieu, sans rien supprimer d'autre. */
    public function unlink(string $kind, int $id): void
    {
        $point = $this->point((int) $this->selectedId);
        abort_unless(in_array($kind, ['scenes', 'entities'], true), 422);

        DemoLock::guardCampaign($this->campaign);
        $point->{$kind}()->detach($id);
        unset($this->selected);
    }

    /**
     * Ouvre une autre carte de la campagne depuis un point ou le fil d'Ariane. Si la carte actuelle
     * est à la table, la nouvelle l'y remplace : les joueurs suivent le MJ dans la sous-carte.
     */
    public function openMap(int $id): void
    {
        $from = $this->currentMap();
        $map = $this->campaign->maps()->findOrFail($id);

        if (TableDisplay::isShowing($this->campaign->refresh(), 'map', $from->id)) {
            TableDisplay::show($this->campaign, 'map', $map->id);
        }

        $this->redirectRoute('maps.atlas', [$this->campaign, $map], navigate: true);
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
        $map = $this->map?->load(['document', 'points' => fn ($q) => $q->with(['target', 'scenes', 'entities'])]);

        return view('livewire.maps.atlas', [
            'points' => $map?->points ?? collect(),
            'parents' => $map?->parents() ?? [],
            // Les cartes dont un point mène ici, quand il y en a plusieurs : le fil d'Ariane n'en suit qu'une.
            'leadingHere' => $map === null ? collect() : $map->pointsLeadingHere()->with('map')->get()->pluck('map')->unique('id')->values(),
            // Les lieux qui ouvrent cette carte : leurs scènes et fiches restent à portée de clic dans la sous-carte.
            'places' => $map === null ? collect() : $map->pointsLeadingHere()->with(['map', 'scenes', 'entities'])->orderBy('id')->get(),
        ])->title($map === null
            ? __('Carte de campagne · :name', ['name' => $this->campaign->name])
            : __(':map · Carte de campagne · :name', ['map' => $map->name, 'name' => $this->campaign->name]));
    }
}
