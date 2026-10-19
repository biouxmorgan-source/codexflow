<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\DemoLibraryItem;
use App\Support\Demo\DemoLibrary as Library;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Bibliothèque de démos, côté administrateur : une campagne de son compte devient une démo
 * que tout MJ charge en un clic (récompense « Créateur de monde » : le jeu d'un auteur ou d'un éditeur).
 */
class DemoLibrary extends Component
{
    /** Démo en cours de modification ; null pour une nouvelle. */
    public ?int $editingId = null;

    public bool $editing = false;

    public string $campaignId = '';

    public string $title = '';

    public string $gameName = '';

    public string $publisher = '';

    public string $summary = '';

    public string $storeUrl = '';

    public string $locale = 'fr';

    public function mount(): void
    {
        $this->authorize('admin');
        $this->locale = app()->getLocale();
    }

    /** @return Collection<int, DemoLibraryItem> */
    #[Computed]
    public function items(): Collection
    {
        return DemoLibraryItem::with('sourceCampaign:id,name')->orderByDesc('published')->orderBy('title')->get();
    }

    /** Campagnes de l'administrateur qui peuvent devenir une démo. */
    #[Computed]
    public function campaigns(): Collection
    {
        return auth()->user()->ownedCampaigns()->orderBy('name')->get(['id', 'name']);
    }

    public function create(): void
    {
        $this->reset('editingId', 'campaignId', 'title', 'gameName', 'publisher', 'summary', 'storeUrl');
        $this->locale = app()->getLocale();
        $this->resetValidation();
        $this->editing = true;
    }

    public function edit(int $id): void
    {
        $item = DemoLibraryItem::findOrFail($id);
        $this->editingId = $item->id;
        $this->campaignId = (string) $item->source_campaign_id;
        $this->title = $item->title;
        $this->gameName = $item->game_name;
        $this->publisher = (string) $item->publisher;
        $this->summary = (string) $item->summary;
        $this->storeUrl = (string) $item->store_url;
        $this->locale = $item->locale;
        $this->resetValidation();
        $this->editing = true;
    }

    public function save(Library $library): void
    {
        $this->authorize('admin');

        $this->validate([
            'campaignId' => [$this->editingId ? 'nullable' : 'required', Rule::in(['', ...array_map('strval', $this->campaigns->modelKeys())])],
            'title' => ['required', 'string', 'max:150'],
            'gameName' => ['required', 'string', 'max:150'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'storeUrl' => ['nullable', 'url:https,http', 'max:500'],
            'locale' => ['required', Rule::in(array_keys(Locale::available()))],
        ], attributes: [
            'campaignId' => __('campagne'),
            'title' => __('titre'),
            'gameName' => __('jeu'),
            'publisher' => __('auteur ou éditeur'),
            'summary' => __('présentation'),
            'storeUrl' => __('lien vers la boutique'),
        ]);

        $item = $this->editingId ? DemoLibraryItem::findOrFail($this->editingId) : new DemoLibraryItem(['archive_path' => '']);
        $item->fill([
            'title' => trim($this->title),
            'game_name' => trim($this->gameName),
            'publisher' => trim($this->publisher) ?: null,
            'summary' => trim($this->summary) ?: null,
            'store_url' => trim($this->storeUrl) ?: null,
            'locale' => $this->locale,
        ])->save();

        // Une nouvelle démo, ou une autre campagne choisie : son contenu est figé maintenant.
        if ($this->campaignId !== '' && (! $this->editingId || (int) $this->campaignId !== $item->source_campaign_id)) {
            $library->snapshot($item, Campaign::findOrFail((int) $this->campaignId));
        }

        session()->now('status', $this->editingId ? __('Démo enregistrée.') : __('Démo ajoutée. Elle reste cachée tant qu’elle n’est pas publiée.'));
        $this->reset('editing', 'editingId');
        unset($this->items);
    }

    /** Reprend le contenu actuel de la campagne d'origine ; les copies déjà chargées ne changent pas. */
    public function refresh(int $id, Library $library): void
    {
        $this->authorize('admin');

        $item = DemoLibraryItem::findOrFail($id);
        $campaign = $item->sourceCampaign;
        abort_unless($campaign && $campaign->user_id === auth()->id(), 404);

        $library->snapshot($item, $campaign);
        session()->now('status', __('Contenu de « :title » mis à jour depuis la campagne.', ['title' => $item->title]));
        unset($this->items);
    }

    public function togglePublished(int $id): void
    {
        $this->authorize('admin');

        $item = DemoLibraryItem::findOrFail($id);
        $item->update(['published' => ! $item->published]);
        unset($this->items);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin');

        DemoLibraryItem::findOrFail($id)->delete();
        unset($this->items);
    }

    public function render()
    {
        return view('livewire.admin.demo-library')->title(__('Administration · Bibliothèque de démos'));
    }
}
