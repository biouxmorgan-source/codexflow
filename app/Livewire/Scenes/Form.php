<?php

namespace App\Livewire\Scenes;

use App\Enums\SceneStatus;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Scene;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Création et modification d'une scène : texte de préparation et fiches présentes.
 */
class Form extends Component
{
    use SuggestsEntities;

    public Campaign $campaign;

    public ?Scene $scene = null;

    #[Url(as: 'scenario')]
    public string $scenarioId = '';

    public string $chapter = '';

    public string $name = '';

    public string $description = '';

    public string $status = 'planned';

    /** @var list<array{id: int, name: string, type: string, note: string}> */
    public array $linked = [];

    public ?int $pickedEntityId = null;

    public function mount(Campaign $campaign, ?Scene $scene = null): void
    {
        $this->authorize('update', $campaign);

        if ($scene?->exists) {
            abort_unless($scene->scenario->campaign_id === $campaign->id, 404);

            $this->scene = $scene;
            $this->scenarioId = (string) $scene->scenario_id;
            $this->chapter = (string) $scene->chapter;
            $this->name = $scene->name;
            $this->description = (string) $scene->description;
            $this->status = $scene->status->value;
            $this->linked = $scene->entities()->with('type')->get()
                ->map(fn (Entity $entity) => self::row($entity, (string) $entity->pivot->note))
                ->all();

            return;
        }

        $this->scene = null;

        if (! $campaign->scenarios()->whereKey((int) $this->scenarioId)->exists()) {
            $this->scenarioId = (string) $campaign->scenarios()->value('id');
        }
    }

    /** @return list<string> */
    public function chapters(): array
    {
        return $this->campaign->scenes()
            ->where('scenario_id', (int) $this->scenarioId)
            ->whereNotNull('chapter')
            ->distinct()
            ->orderBy('chapter')
            ->pluck('chapter')
            ->all();
    }

    public function addEntity(): void
    {
        $entity = $this->pickedEntityId ? $this->campaign->availableEntities()->with('type')->find($this->pickedEntityId) : null;

        if ($entity === null) {
            $this->addError('pickedEntityId', 'Choisissez une fiche dans la liste.');

            return;
        }

        if (! collect($this->linked)->contains('id', $entity->id)) {
            $this->linked[] = self::row($entity, '');
        }

        $this->pickedEntityId = null;
    }

    public function removeEntity(int $index): void
    {
        unset($this->linked[$index]);
        $this->linked = array_values($this->linked);
    }

    public function save(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'scenarioId' => ['required', Rule::in($this->campaign->scenarios()->pluck('id')->map(fn ($id) => (string) $id)->all())],
            'chapter' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'status' => ['required', Rule::enum(SceneStatus::class)],
            'linked' => ['array', 'max:100'],
            'linked.*.note' => ['nullable', 'string', 'max:150'],
        ], attributes: [
            'scenarioId' => 'scénario',
            'chapter' => 'chapitre',
            'linked.*.note' => 'précision',
        ]);

        $scene = $this->scene ?? new Scene;
        $movedScenario = $scene->scenario_id !== (int) $this->scenarioId;
        $scene->fill([
            'chapter' => trim($this->chapter) ?: null,
            'name' => trim($this->name),
            'description' => $this->description ?: null,
            'status' => $this->status,
        ]);
        $scene->scenario_id = (int) $this->scenarioId;

        if ($movedScenario) {
            $scene->position = (int) Scene::where('scenario_id', $scene->scenario_id)->max('position') + 1;
        }

        $scene->save();

        // Seules les fiches utilisables dans la campagne peuvent être liées.
        $allowed = $this->campaign->availableEntities()
            ->whereKey(collect($this->linked)->pluck('id')->all())
            ->pluck('id')
            ->all();

        $scene->entities()->sync(collect($this->linked)
            ->filter(fn (array $row) => in_array($row['id'], $allowed, true))
            ->values()
            ->mapWithKeys(fn (array $row, int $position) => [$row['id'] => [
                'note' => trim((string) $row['note']) ?: null,
                'position' => $position,
            ]])
            ->all());

        $this->redirectRoute('scenes.show', [$this->campaign, $scene], navigate: true);
    }

    /** @return array{id: int, name: string, type: string, note: string} */
    private static function row(Entity $entity, string $note): array
    {
        return ['id' => $entity->id, 'name' => $entity->name, 'type' => $entity->type->name, 'note' => $note];
    }

    public function render()
    {
        return view('livewire.scenes.form', [
            'scenarios' => $this->campaign->scenarios()->get(),
        ])->title($this->scene ? 'Modifier '.$this->scene->name : 'Nouvelle scène');
    }
}
