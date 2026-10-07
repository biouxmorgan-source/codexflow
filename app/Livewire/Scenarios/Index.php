<?php

namespace App\Livewire\Scenarios;

use App\Enums\SceneStatus;
use App\Models\Campaign;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Plan de la campagne : scénarios, chapitres facultatifs et scènes.
 */
class Index extends Component
{
    public Campaign $campaign;

    /** Tag choisi pour ne montrer que les scènes qui le portent. */
    #[Url]
    public ?int $tag = null;

    public ?int $editingId = null;

    public string $name = '';

    public string $summary = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<int, Scenario> */
    #[Computed]
    public function scenarios(): Collection
    {
        return $this->campaign->scenarios()->with('scenes.tags')->get();
    }

    #[Computed]
    public function filterTag(): ?Tag
    {
        return $this->tag ? Tag::query()->where('user_id', auth()->id())->find($this->tag) : null;
    }

    public function edit(int $id): void
    {
        $scenario = $this->find($id);

        $this->resetValidation();
        $this->editingId = $scenario->id;
        $this->name = $scenario->name;
        $this->summary = (string) $scenario->summary;
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'name', 'summary');
    }

    public function save(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
        ], attributes: ['name' => __('nom'), 'summary' => __('résumé')]);

        $scenario = $this->editingId ? $this->find($this->editingId) : new Scenario([
            'position' => (int) $this->campaign->scenarios()->max('position') + 1,
        ]);
        $scenario->fill(['name' => trim($this->name), 'summary' => trim($this->summary) ?: null]);
        $this->campaign->scenarios()->save($scenario);

        $this->cancel();
        unset($this->scenarios);
    }

    public function delete(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $this->find($id)->delete();

        unset($this->scenarios);
    }

    public function moveScenario(int $id, int $direction): void
    {
        $this->authorize('update', $this->campaign);

        self::swap($this->scenarios, $id, $direction);
        unset($this->scenarios);
    }

    public function moveScene(int $id, int $direction): void
    {
        $this->authorize('update', $this->campaign);

        $scene = $this->scene($id);
        self::swap($scene->scenario->scenes, $id, $direction);
        unset($this->scenarios);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->authorize('update', $this->campaign);

        $scene = $this->scene($id);
        $scene->status = SceneStatus::from($status);
        $scene->save();

        unset($this->scenarios);
    }

    /**
     * Échange une ligne avec sa voisine puis renumérote toute la liste.
     *
     * @param  \Illuminate\Support\Collection<int, Model>  $items
     */
    private static function swap($items, int $id, int $direction): void
    {
        $ids = $items->modelKeys();
        $index = array_search($id, $ids, true);
        $target = $index === false ? null : $index + ($direction < 0 ? -1 : 1);

        if ($target === null || ! isset($ids[$target])) {
            return;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
        $model = $items->first()::class;

        foreach ($ids as $position => $key) {
            $model::whereKey($key)->update(['position' => $position]);
        }
    }

    private function find(int $id): Scenario
    {
        return $this->campaign->scenarios()->findOrFail($id);
    }

    private function scene(int $id): Scene
    {
        return $this->campaign->scenes()->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.scenarios.index')->title(__('Scénarios · :name', ['name' => $this->campaign->name]));
    }
}
