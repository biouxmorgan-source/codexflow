<?php

namespace App\Livewire\Campaigns;

use App\Actions\Campaigns\CreateCampaign;
use App\Actions\Demo\LoadDemoCampaign;
use App\Actions\Duplication\DuplicateCampaign;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Support\Archive\ArchiveException;
use App\Support\Archive\CampaignImport;
use App\Support\Plans\Plans;
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

    /** Archive .zip exportée depuis LoreMundi. */
    public ?TemporaryUploadedFile $archive = null;

    public string $name = '';

    public string $description = '';

    /** Identifiant d'un jeu existant, ou « new » pour en créer un. */
    public string $gameChoice = 'new';

    public string $newGameName = '';

    /** Identifiant d'un monde existant, « new » pour en créer un, vide pour aucun. */
    public string $worldChoice = '';

    public string $newWorldName = '';

    /** Langue de la campagne de démonstration ; celle de l'interface par défaut. */
    public string $demoLocale = '';

    public function mount(): void
    {
        $this->demoLocale = app()->getLocale();

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
            ->withCount('playSessions')
            ->withMax('playSessions', 'started_at')
            ->orderByRaw("status = 'archived'")
            ->latest('updated_at')
            ->get();
    }

    /** Campagne terminée ou en pause : rangée à part, rouvrable à tout moment. */
    public function toggleArchive(int $id): void
    {
        $campaign = Campaign::findOrFail($id);
        $this->authorize('manage', $campaign);

        $archived = $campaign->status === CampaignStatus::Archived;
        $campaign->update([
            'status' => $archived ? CampaignStatus::Active : CampaignStatus::Archived,
            'archived_at' => $archived ? null : now(),
        ]);
        unset($this->campaigns);
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

        Plans::ensureCanCreateCampaign($user);

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

    /** Charge la campagne de démonstration : un contenu complet, pour visiter l'application sans rien préparer. */
    public function loadDemo(LoadDemoCampaign $loadDemo): void
    {
        $this->validate(['demoLocale' => ['required', Rule::in(LoadDemoCampaign::locales())]]);

        Plans::ensureCanCreateCampaign(auth()->user());
        $campaign = $loadDemo->handle(auth()->user(), $this->demoLocale);

        session()->flash('status', __('Campagne de démonstration chargée : « :name ». Vous en êtes le MJ : modifiez, dupliquez ou supprimez-la librement.', ['name' => $campaign->name]));
        $this->redirectRoute('campaigns.show', $campaign, navigate: true);
    }

    public function duplicate(int $id, DuplicateCampaign $duplicateCampaign): void
    {
        $campaign = Campaign::findOrFail($id);
        $this->authorize('duplicate', $campaign);
        Plans::ensure(auth()->user(), 'duplication');
        Plans::ensureCanCreateCampaign(auth()->user());
        Plans::ensureRoom(auth()->user(), 0, 'plan');

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

        Plans::ensure(auth()->user(), 'archive');
        Plans::ensureCanCreateCampaign(auth()->user(), 'archive');
        Plans::ensureRoom(auth()->user(), (int) $this->archive->getSize(), 'archive');

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
