<?php

namespace App\Livewire\Imports;

use App\Actions\Imports\ImportEntities;
use App\Actions\Imports\ImportFieldDefinitions;
use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Support\Import\TabularFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Import en masse : aperçu, association des colonnes, puis création après validation du MJ.
 */
class Create extends Component
{
    use WithFileUploads;

    public Campaign $campaign;

    /** « entities » : des fiches et leurs valeurs ; « fields » : une liste de champs. */
    public string $mode = 'entities';

    public ?TemporaryUploadedFile $file = null;

    /** @var array<int, string> */
    public array $mapping = [];

    public string $defaultTypeId = '';

    public string $scope = 'campaign';

    public bool $updateExisting = true;

    public string $newGroup = '';

    public string $newZone = 'public';

    public string $newTypeId = '';

    /** @var array<string, int>|null */
    public ?array $result = null;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
        $this->authorize('update', $campaign->gameSystem);

        $this->defaultTypeId = (string) $this->types->first()?->getKey();
        $this->scope = $campaign->world_id ? 'world' : 'campaign';
    }

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()->availableTo(auth()->user())->orderBy('id')->get();
    }

    /** @return Collection<int, FieldDefinition> */
    #[Computed]
    public function definitions(): Collection
    {
        return $this->campaign->gameSystem->fieldDefinitions()->with('entityType')->ordered()->get();
    }

    /**
     * Le fichier lu, ou le message expliquant pourquoi il ne l'est pas.
     */
    #[Computed]
    public function table(): TabularFile|string|null
    {
        if ($this->file === null || $this->getErrorBag()->has('file')) {
            return null;
        }

        try {
            return TabularFile::read($this->file->getRealPath(), $this->file->getClientOriginalExtension());
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        }
    }

    #[Computed]
    public function plan(): ?array
    {
        $action = $this->action();

        return $action?->plan();
    }

    public function updatedFile(): void
    {
        $this->result = null;
        $this->validateOnly('file', $this->fileRules(), attributes: ['file' => 'fichier']);
        $this->guess();
    }

    public function updatedMode(): void
    {
        $this->result = null;
        $this->guess();
    }

    public function import(): void
    {
        $this->authorize('update', $this->campaign->gameSystem);
        $this->validate($this->fileRules() + [
            'mode' => ['required', Rule::in(['entities', 'fields'])],
            'defaultTypeId' => ['required', Rule::in($this->types->modelKeys())],
            'newTypeId' => ['nullable', Rule::in($this->types->modelKeys())],
            'scope' => ['required', Rule::in($this->campaign->world_id ? ['world', 'campaign'] : ['campaign'])],
            'newGroup' => ['nullable', 'string', 'max:100'],
            'newZone' => ['required', Rule::enum(Zone::class)],
            'mapping.*' => ['string', 'max:30'],
        ], attributes: ['file' => 'fichier', 'newGroup' => 'groupe']);

        $action = $this->action();

        if ($action === null || ($action->plan()['valid'] ?? 0) === 0) {
            $this->addError('file', 'Aucune ligne valide à importer.');

            return;
        }

        $this->result = $action->run();
        $this->reset('file', 'mapping');
        unset($this->definitions, $this->table, $this->plan);
    }

    private function action(): ImportEntities|ImportFieldDefinitions|null
    {
        $table = $this->table;

        if (! $table instanceof TabularFile) {
            return null;
        }

        if ($this->mode === 'fields') {
            return new ImportFieldDefinitions($this->campaign->gameSystem, auth()->user(), $table);
        }

        return new ImportEntities($this->campaign, auth()->user(), $table, $this->mapping, [
            'default_type_id' => (int) $this->defaultTypeId,
            'scope' => $this->scope,
            'update_existing' => $this->updateExisting,
            'new_group' => trim($this->newGroup) ?: null,
            'new_zone' => Zone::tryFrom($this->newZone) ?? Zone::Public,
            'new_type_id' => $this->newTypeId === '' ? null : (int) $this->newTypeId,
        ]);
    }

    private function guess(): void
    {
        $table = $this->table;
        $this->mapping = $table instanceof TabularFile && $this->mode === 'entities'
            ? ImportEntities::guessMapping($table->headers, $this->definitions)
            : [];

        // Avec une colonne « Type », les nouveaux champs valent pour tous les types de fiche.
        $this->newTypeId = in_array('type', $this->mapping, true) ? '' : $this->defaultTypeId;
    }

    /** @return array<string, list<string>> */
    private function fileRules(): array
    {
        return ['file' => ['required', 'file', 'extensions:csv,txt,json', 'max:5120']];
    }

    public function render()
    {
        return view('livewire.imports.create')->title('Importer depuis un fichier');
    }
}
