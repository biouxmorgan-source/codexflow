<?php

namespace App\Livewire\Scenes;

use App\Enums\SceneStatus;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Scene;
use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
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

    public string $tags = '';

    /** @var list<array{id: int, name: string, type: string, note: string}> */
    public array $linked = [];

    public ?int $pickedEntityId = null;

    /** @var list<int> Règles liées, dans l'ordre. */
    public array $ruleIds = [];

    /** @var list<int> Documents liés, dans l'ordre. */
    public array $documentIds = [];

    public ?int $pickedRuleId = null;

    public ?int $pickedDocumentId = null;

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
            $this->ruleIds = $scene->rules()->pluck('rules.id')->all();
            $this->documentIds = $scene->documents()->pluck('documents.id')->all();
            $this->tags = $scene->tags->pluck('name')->implode(', ');

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

    /** @return list<string> */
    #[Computed]
    public function existingTags(): array
    {
        return Tag::query()->where('user_id', auth()->id())->has('scenes')->orderByRaw('lower(name)')->pluck('name')->all();
    }

    public function addEntity(): void
    {
        $entity = $this->pickedEntityId ? $this->campaign->availableEntities()->with('type')->find($this->pickedEntityId) : null;

        if ($entity === null) {
            $this->addError('pickedEntityId', __('Choisissez une fiche dans la liste.'));

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

    public function addRule(): void
    {
        if ($this->pickedRuleId && $this->campaign->availableRules()->whereKey($this->pickedRuleId)->exists()) {
            $this->ruleIds = array_values(array_unique([...$this->ruleIds, (int) $this->pickedRuleId]));
        }

        $this->pickedRuleId = null;
    }

    public function removeRule(int $id): void
    {
        $this->ruleIds = array_values(array_diff($this->ruleIds, [$id]));
    }

    public function addDocument(): void
    {
        if ($this->pickedDocumentId && $this->campaign->availableDocuments()->whereKey($this->pickedDocumentId)->exists()) {
            $this->documentIds = array_values(array_unique([...$this->documentIds, (int) $this->pickedDocumentId]));
        }

        $this->pickedDocumentId = null;
    }

    public function removeDocument(int $id): void
    {
        $this->documentIds = array_values(array_diff($this->documentIds, [$id]));
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
            'tags' => ['nullable', 'string', 'max:1000'],
            'linked' => ['array', 'max:100'],
            'linked.*.note' => ['nullable', 'string', 'max:150'],
        ], attributes: [
            'scenarioId' => __('scénario'),
            'chapter' => __('chapitre'),
            'linked.*.note' => __('précision'),
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

        $scene->tags()->sync(Tag::idsFromInput(auth()->user(), $this->tags));
        $scene->rules()->sync(self::positions($this->campaign->availableRules()->whereKey($this->ruleIds)->pluck('id')->all(), $this->ruleIds));
        $scene->documents()->sync(self::positions($this->campaign->availableDocuments()->whereKey($this->documentIds)->pluck('id')->all(), $this->documentIds));

        $this->redirectRoute('scenes.show', [$this->campaign, $scene], navigate: true);
    }

    /**
     * Garde l'ordre choisi, sans les éléments qui ne sont pas utilisables dans la campagne.
     *
     * @param  list<int>  $allowed
     * @param  list<int>  $ordered
     * @return array<int, array{position: int}>
     */
    private static function positions(array $allowed, array $ordered): array
    {
        return collect($ordered)
            ->filter(fn (int $id) => in_array($id, $allowed, true))
            ->values()
            ->mapWithKeys(fn (int $id, int $position) => [$id => ['position' => $position]])
            ->all();
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
            'rules' => $this->campaign->availableRules()->orderByRaw('lower(title)')->get(['id', 'title', 'category'])->keyBy('id'),
            'documents' => $this->campaign->availableDocuments()->orderByRaw('lower(title)')->get(['id', 'title', 'mime_type'])->keyBy('id'),
        ])->title($this->scene ? __('Modifier :name', ['name' => $this->scene->name]) : __('Nouvelle scène'));
    }
}
