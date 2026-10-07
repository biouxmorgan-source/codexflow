<?php

namespace App\Livewire\Imports;

use App\Actions\Imports\ImportEntities;
use App\Actions\Imports\ImportFieldDefinitions;
use App\Actions\Imports\ImportRules;
use App\Actions\Imports\ImportScenes;
use App\Enums\Zone;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Support\Import\TabularFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Import en masse : aperçu, association des colonnes, puis création après validation du MJ.
 */
class Create extends Component
{
    use WithFileUploads;

    private const MODES = ['entities', 'fields', 'rules', 'scenes'];

    public Campaign $campaign;

    /** « entities » : des fiches et leurs valeurs ; « fields » : une liste de champs ; « rules » : des règles ; « scenes » : des scénarios et leurs scènes. */
    #[Url(except: 'entities')]
    public string $mode = 'entities';

    public ?TemporaryUploadedFile $file = null;

    /** @var array<int, string> */
    public array $mapping = [];

    public string $defaultTypeId = '';

    public string $scope = 'campaign';

    /** Rattachement des règles importées : « game » (tout le jeu) ou « campaign ». */
    public string $ruleScope = 'game';

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

        if (! in_array($this->mode, self::MODES, true)) {
            $this->mode = 'entities';
        }

        $this->defaultTypeId = (string) $this->types->first()?->getKey();
        $this->scope = $campaign->world_id ? 'world' : 'campaign';
    }

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()->availableTo($this->campaign->owner)->orderBy('id')->get();
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
        $this->validateOnly('file', $this->fileRules(), attributes: ['file' => __('fichier')]);
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
            'mode' => ['required', Rule::in(self::MODES)],
            'ruleScope' => ['required', Rule::in(['game', 'campaign'])],
            'defaultTypeId' => ['required', Rule::in($this->types->modelKeys())],
            'newTypeId' => ['nullable', Rule::in($this->types->modelKeys())],
            'scope' => ['required', Rule::in($this->campaign->world_id ? ['world', 'campaign'] : ['campaign'])],
            'newGroup' => ['nullable', 'string', 'max:100'],
            'newZone' => ['required', Rule::enum(Zone::class)],
            'mapping.*' => ['string', 'max:30'],
        ], attributes: ['file' => __('fichier'), 'newGroup' => __('groupe')]);

        $action = $this->action();

        if ($action === null || ($action->plan()['valid'] ?? 0) === 0) {
            $this->addError('file', __('Aucune ligne valide à importer.'));

            return;
        }

        $this->result = ActivityLog::batch(fn () => $action->run());
        $this->reset('file', 'mapping');
        unset($this->definitions, $this->table, $this->plan);
    }

    private function action(): ImportEntities|ImportFieldDefinitions|ImportRules|ImportScenes|null
    {
        $table = $this->table;

        if (! $table instanceof TabularFile) {
            return null;
        }

        if ($this->mode === 'fields') {
            return new ImportFieldDefinitions($this->campaign->gameSystem, $this->campaign->owner, $table);
        }

        if ($this->mode === 'scenes') {
            return new ImportScenes($this->campaign, $table, $this->updateExisting);
        }

        if ($this->mode === 'rules') {
            return new ImportRules($this->campaign, $this->campaign->owner, $table, $this->ruleScope, $this->updateExisting);
        }

        return new ImportEntities($this->campaign, $this->campaign->owner, $table, $this->mapping, [
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
        return view('livewire.imports.create')->title(__('Importer depuis un fichier'));
    }
}
