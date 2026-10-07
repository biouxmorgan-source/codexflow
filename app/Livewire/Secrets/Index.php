<?php

namespace App\Livewire\Secrets;

use App\Livewire\Concerns\RevealsSecrets;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Secret;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Les secrets de la campagne : des informations indépendantes des fiches, reliées à des fiches,
 * scènes ou documents, que chaque personnage connaît ou non.
 */
class Index extends Component
{
    use RevealsSecrets;
    use SuggestsEntities;

    public Campaign $campaign;

    /** Pré-remplit un nouveau secret relié à un élément : « entity:12 », « scene:3 », « document:5 ». */
    #[Url(as: 'lier', except: '')]
    public string $link = '';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?int $editingId = null;

    public bool $editing = false;

    public string $title = '';

    public string $body = '';

    /** @var list<int> */
    public array $entityIds = [];

    /** @var list<int> */
    public array $sceneIds = [];

    /** @var list<int> */
    public array $documentIds = [];

    public ?int $pickedEntityId = null;

    public string $pickedSceneId = '';

    public string $pickedDocumentId = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);

        if (preg_match('/^(entity|scene|document):(\d+)$/', $this->link, $m)) {
            $this->create();
            match ($m[1]) {
                'entity' => $this->entityIds = $campaign->availableEntities()->whereKey((int) $m[2])->pluck('entities.id')->all(),
                'scene' => $this->sceneIds = $campaign->scenes()->whereKey((int) $m[2])->pluck('scenes.id')->all(),
                'document' => $this->documentIds = $campaign->availableDocuments()->whereKey((int) $m[2])->pluck('documents.id')->all(),
            };
        }
    }

    /** @return Collection<int, Secret> */
    #[Computed]
    public function secrets(): Collection
    {
        $like = '%'.addcslashes($this->search, '%_\\').'%';

        return $this->campaign->secrets()
            ->with(['entities', 'scenes', 'documents', 'grants'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('title', 'ilike', $like)->orWhere('body', 'ilike', $like)
                ->orWhereHas('entities', fn ($e) => $e->where('name', 'ilike', $like))))
            ->orderByRaw('lower(title)')
            ->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editing = true;
    }

    public function edit(int $id): void
    {
        $secret = $this->campaign->secrets()->findOrFail($id);

        $this->resetForm();
        $this->editing = true;
        $this->editingId = $secret->id;
        $this->title = $secret->title;
        $this->body = (string) $secret->body;
        $this->entityIds = $secret->entities()->pluck('entities.id')->all();
        $this->sceneIds = $secret->scenes()->pluck('scenes.id')->all();
        $this->documentIds = $secret->documents()->pluck('documents.id')->all();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->link = '';
    }

    public function addEntity(): void
    {
        if ($this->pickedEntityId && $this->campaign->availableEntities()->whereKey($this->pickedEntityId)->exists()) {
            $this->entityIds = array_values(array_unique([...$this->entityIds, $this->pickedEntityId]));
        }

        $this->pickedEntityId = null;
    }

    public function updatedPickedSceneId(string $id): void
    {
        if (ctype_digit($id) && $this->campaign->scenes()->whereKey((int) $id)->exists()) {
            $this->sceneIds = array_values(array_unique([...$this->sceneIds, (int) $id]));
        }

        $this->pickedSceneId = '';
    }

    public function updatedPickedDocumentId(string $id): void
    {
        if (ctype_digit($id) && $this->campaign->availableDocuments()->whereKey((int) $id)->exists()) {
            $this->documentIds = array_values(array_unique([...$this->documentIds, (int) $id]));
        }

        $this->pickedDocumentId = '';
    }

    public function unlink(string $kind, int $id): void
    {
        match ($kind) {
            'entity' => $this->entityIds = array_values(array_diff($this->entityIds, [$id])),
            'scene' => $this->sceneIds = array_values(array_diff($this->sceneIds, [$id])),
            'document' => $this->documentIds = array_values(array_diff($this->documentIds, [$id])),
            default => null,
        };
    }

    public function save(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000'],
        ], attributes: ['title' => __('secret'), 'body' => __('détails')]);

        DB::transaction(function () {
            $secret = $this->editingId ? $this->campaign->secrets()->findOrFail($this->editingId) : new Secret;
            $secret->fill(['title' => trim($this->title), 'body' => trim($this->body) ?: null]);
            $secret->campaign()->associate($this->campaign);

            if (! $secret->exists) {
                $secret->owner()->associate($this->campaign->owner);
            }

            $secret->save();

            // Seuls les éléments utilisables dans la campagne peuvent être reliés.
            $secret->entities()->sync($this->campaign->availableEntities()->whereKey($this->entityIds)->pluck('entities.id'));
            $secret->scenes()->sync($this->campaign->scenes()->whereKey($this->sceneIds)->pluck('scenes.id'));
            $secret->documents()->sync($this->campaign->availableDocuments()->whereKey($this->documentIds)->pluck('documents.id'));
        });

        $this->cancel();
        unset($this->secrets);
    }

    public function delete(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $this->campaign->secrets()->findOrFail($id)->delete();
        $this->cancel();
        unset($this->secrets);
    }

    protected function secretsChanged(): void
    {
        unset($this->secrets);
        $this->dispatch('character-grants-changed');
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editing', 'editingId', 'title', 'body', 'entityIds', 'sceneIds', 'documentIds', 'pickedEntityId', 'pickedSceneId', 'pickedDocumentId');
    }

    public function render()
    {
        return view('livewire.secrets.index', [
            'linkedEntities' => $this->campaign->availableEntities()->whereKey($this->entityIds)->orderBy('name')->get(['entities.id', 'name']),
            'scenes' => $this->campaign->scenes()->with('scenario')->orderBy('scenarios.position')->orderBy('scenes.position')->get(),
            'documents' => $this->campaign->availableDocuments()->orderByRaw('lower(title)')->get(['documents.id', 'title']),
        ])->title(__('Secrets · :name', ['name' => $this->campaign->name]));
    }
}
