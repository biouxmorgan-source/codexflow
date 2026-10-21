<?php

namespace App\Livewire\Library;

use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\Tag;
use App\Support\SoundLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Ma bibliothèque de sons : musiques et ambiances rangées une fois dans le compte, ajoutées aux
 * campagnes que je mène sans dupliquer le fichier.
 */
class Sounds extends Component
{
    use WithFileUploads;

    #[Url]
    public string $tag = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    public string $tags = '';

    public bool $loop = true;

    public ?int $editingId = null;

    public string $editTitle = '';

    public string $editTags = '';

    public bool $editLoop = true;

    /** @var array<int, string> son => campagne choisie pour l'y ajouter */
    public array $addTo = [];

    /** @return Collection<int, AudioTrack> */
    #[Computed]
    public function tracks(): Collection
    {
        return auth()->user()->libraryTracks()
            ->with(['tags', 'linkedCampaigns' => fn ($q) => $q->orderByRaw('lower(name)')])
            ->when($this->tag !== '', fn (Builder $q) => $q->whereHas('tags', fn (Builder $t) => $t->whereRaw('lower(tags.name) = ?', [mb_strtolower($this->tag)])))
            ->orderByRaw('lower(title)')
            ->get();
    }

    /** @return list<string> */
    #[Computed]
    public function tagNames(): array
    {
        return Tag::query()
            ->whereHas('audioTracks', fn (Builder $q) => $q->whereNull('campaign_id')->where('audio_tracks.user_id', auth()->id()))
            ->orderByRaw('lower(name)')
            ->pluck('name')
            ->all();
    }

    /** @return Collection<int, Campaign> campagnes où je suis MJ (propriétaire ou co-MJ) */
    #[Computed]
    public function campaigns(): Collection
    {
        return Campaign::query()->runBy(auth()->user())->orderByRaw('lower(name)')->get(['id', 'name', 'user_id']);
    }

    public function saveUploads(): void
    {
        $this->validate(SoundLibrary::rules(), attributes: ['uploads' => __('fichiers'), 'uploads.*' => __('fichier')]);

        SoundLibrary::store($this->uploads, auth()->user(), null, $this->loop, Tag::idsFromInput(auth()->user(), $this->tags));

        $this->reset('uploads', 'tags');
        unset($this->tracks, $this->tagNames);
    }

    public function edit(int $id): void
    {
        $track = $this->track($id);

        $this->resetValidation();
        $this->editingId = $track->id;
        $this->editTitle = $track->title;
        $this->editLoop = $track->loop;
        $this->editTags = $track->tags->pluck('name')->implode(', ');
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'editTitle', 'editTags', 'editLoop');
    }

    public function update(): void
    {
        $track = $this->track((int) $this->editingId);

        $this->validate([
            'editTitle' => ['required', 'string', 'max:255'],
            'editTags' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['editTitle' => __('titre'), 'editTags' => __('tags')]);

        $track->update(['title' => trim($this->editTitle), 'loop' => $this->editLoop]);
        $track->tags()->sync(Tag::idsFromInput(auth()->user(), $this->editTags));

        $this->cancel();
        unset($this->tracks, $this->tagNames);
    }

    public function addToCampaign(int $id): void
    {
        $track = $this->track($id);
        $campaign = $this->campaigns->firstWhere('id', (int) ($this->addTo[$id] ?? 0));
        abort_if($campaign === null, 404);

        SoundLibrary::link($track, $campaign);

        unset($this->addTo[$id], $this->tracks);
    }

    public function removeFromCampaign(int $id, int $campaignId): void
    {
        $track = $this->track($id);
        $campaign = $track->linkedCampaigns()->findOrFail($campaignId);

        SoundLibrary::detach($track, $campaign);

        unset($this->tracks);
    }

    public function delete(int $id): void
    {
        SoundLibrary::delete($this->track($id));

        unset($this->tracks, $this->tagNames);
    }

    private function track(int $id): AudioTrack
    {
        $track = auth()->user()->libraryTracks()->with('tags')->findOrFail($id);
        $this->authorize('update', $track);

        return $track;
    }

    public function render()
    {
        return view('livewire.library.sounds')->title(__('Ma bibliothèque de sons'));
    }
}
