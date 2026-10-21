<?php

namespace App\Livewire\Campaigns;

use App\Actions\Demo\LoadDemoCampaign;
use App\Models\DemoLibraryItem;
use App\Support\Archive\ArchiveException;
use App\Support\Demo\DemoLibrary as Library;
use App\Support\Plans\Plans;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Bibliothèque de démos : des campagnes prêtes à jouer, dont chaque MJ charge sa propre copie en un clic.
 * La démonstration de SagaWyn vient en premier, puis les jeux d'auteurs et d'éditeurs avec le lien où les acheter.
 */
class DemoLibrary extends Component
{
    /** Langue de la démonstration de SagaWyn ; celle de l'interface par défaut. */
    public string $demoLocale = '';

    public function mount(): void
    {
        $this->demoLocale = in_array(app()->getLocale(), LoadDemoCampaign::locales(), true) ? app()->getLocale() : 'fr';
    }

    /** Démos publiées, celles dans la langue de l'interface d'abord. */
    #[Computed]
    public function items(): Collection
    {
        return DemoLibraryItem::published()
            ->orderByRaw('locale = ? desc', [app()->getLocale()])
            ->orderBy('title')
            ->get();
    }

    /** Un compte démo a déjà sa campagne : la bibliothèque s'ouvre une fois le compte débloqué. */
    #[Computed]
    public function locked(): bool
    {
        return Plans::effective(auth()->user()) === Plans::DEMO;
    }

    public function loadDemo(LoadDemoCampaign $loadDemo): void
    {
        abort_if($this->locked, 403);
        $this->validate(['demoLocale' => ['required', Rule::in(LoadDemoCampaign::locales())]]);
        Plans::ensureCanCreateCampaign(auth()->user(), 'demo');

        $campaign = $loadDemo->handle(auth()->user(), $this->demoLocale);

        session()->flash('status', __('Campagne de démonstration chargée : « :name ». Vous en êtes le MJ : modifiez, dupliquez ou supprimez-la librement.', ['name' => $campaign->name]));
        $this->redirectRoute('campaigns.show', $campaign, navigate: true);
    }

    public function load(int $id, Library $library): void
    {
        abort_if($this->locked, 403);
        $item = DemoLibraryItem::published()->findOrFail($id);

        try {
            [$campaign, $notices] = $library->load(auth()->user(), $item);
        } catch (ArchiveException $e) {
            $this->addError('demo', $e->getMessage());

            return;
        }

        session()->flash('status', implode(' ', [__('Démo chargée : « :name ». Vous en êtes le MJ : modifiez, dupliquez ou supprimez-la librement.', ['name' => $campaign->name]), ...$notices]));
        $this->redirectRoute('campaigns.show', $campaign, navigate: true);
    }

    public function render()
    {
        return view('livewire.campaigns.demo-library')->title(__('Bibliothèque de démos'));
    }
}
