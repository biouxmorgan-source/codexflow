<?php

namespace App\Livewire\Campaigns;

use App\Actions\Campaigns\CreateCampaign;
use App\Actions\Duplication\DuplicateCampaign;
use App\Models\Campaign;
use App\Support\Archive\ArchiveException;
use App\Support\Archive\CampaignImport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public bool $creating = false;

    public bool $importing = false;

    /** Archive .zip exportée depuis CodexFlow. */
    public ?TemporaryUploadedFile $archive = null;

    public string $name = '';

    public string $description = '';

    /** Identifiant d'un jeu existant, ou « new » pour en créer un. */
    public string $gameChoice = 'new';

    public string $newGameName = '';

    /** Identifiant d'un monde existant, « new » pour en créer un, vide pour aucun. */
    public string $worldChoice = '';

    public string $newWorldName = '';

    public function mount(): void
    {
        $firstGame = $this->gameSystems->first();

        if ($firstGame !== null) {
            $this->gameChoice = (string) $firstGame->getKey();
        }
    }

    /** @return Collection<int, Campaign> */
    #[Computed]
    public function campaigns(): Collection
    {
        return Campaign::query()
            ->visibleTo(auth()->user())
            ->with([
                'gameSystem', 'world',
                'members' => fn ($q) => $q->whereKey(auth()->id()),
                'playerCharacters' => fn ($q) => $q->active()->where('user_id', auth()->id())->with('entity'),
            ])
            ->orderByRaw("status = 'archived'")
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function gameSystems(): Collection
    {
        return auth()->user()->gameSystems()->orderBy('name')->get();
    }

    #[Computed]
    public function worlds(): Collection
    {
        return auth()->user()->worlds()->orderBy('name')->get();
    }

    public function create(CreateCampaign $createCampaign): void
    {
        $user = auth()->user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'gameChoice' => ['required', Rule::in(['new', ...$this->gameSystems->modelKeys()])],
            'newGameName' => ['required_if:gameChoice,new', 'nullable', 'string', 'max:255'],
            'worldChoice' => ['nullable', Rule::in(['', 'new', ...$this->worlds->modelKeys()])],
            'newWorldName' => ['required_if:worldChoice,new', 'nullable', 'string', 'max:255'],
        ], attributes: [
            'gameChoice' => __('jeu'),
            'worldChoice' => __('monde'),
            'newGameName' => __('nom du nouveau jeu'),
            'newWorldName' => __('nom du nouveau monde'),
        ]);

        $createCampaign->handle($user, [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'game_system_id' => $this->gameChoice === 'new' ? null : (int) $this->gameChoice,
            'new_game_name' => $this->gameChoice === 'new' ? $this->newGameName : null,
            'world_id' => in_array($this->worldChoice, ['', 'new'], true) ? null : (int) $this->worldChoice,
            'new_world_name' => $this->worldChoice === 'new' ? $this->newWorldName : null,
        ]);

        $this->reset(['creating', 'name', 'description', 'newGameName', 'worldChoice', 'newWorldName']);
        unset($this->campaigns, $this->gameSystems, $this->worlds);
        $this->mount();
    }

    public function duplicate(int $id, DuplicateCampaign $duplicateCampaign): void
    {
        $campaign = Campaign::findOrFail($id);
        $this->authorize('duplicate', $campaign);

        $copy = $duplicateCampaign->handle($campaign, auth()->user());

        session()->now('status', __('Campagne dupliquée : « :name ».', ['name' => $copy->name]));
        unset($this->campaigns);
    }

    /** Limite réelle du serveur, en kilo-octets : au-delà, le fichier n'arrive même pas à Livewire. */
    #[Computed]
    public function maxArchiveSize(): int
    {
        $bytes = fn (string $key) => match (true) {
            ($value = ini_get($key)) === false || $value === '' => PHP_INT_MAX,
            default => (int) $value * match (strtolower(substr($value, -1))) {
                'g' => 1024 ** 3,
                'm' => 1024 ** 2,
                'k' => 1024,
                default => 1,
            },
        };

        return (int) (min($bytes('upload_max_filesize'), $bytes('post_max_size'), 512 * 1024 * 1024) / 1024);
    }

    public function importArchive(): void
    {
        $this->validate(
            ['archive' => ['required', 'file', 'mimes:zip', 'max:'.$this->maxArchiveSize]],
            attributes: ['archive' => __('archive')],
        );

        try {
            $campaign = (new CampaignImport(auth()->user()))->handle($this->archive->getRealPath());
        } catch (ArchiveException $e) {
            $this->addError('archive', $e->getMessage());

            return;
        }

        $this->archive->delete();
        $this->reset('importing', 'archive');
        session()->flash('status', __('Campagne importée : « :name ».', ['name' => $campaign->name]));
        $this->redirectRoute('campaigns.show', $campaign, navigate: true);
    }

    public function render()
    {
        return view('livewire.campaigns.index')->title(__('Mes campagnes'));
    }
}
