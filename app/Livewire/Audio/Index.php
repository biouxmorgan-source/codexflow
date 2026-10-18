<?php

namespace App\Livewire\Audio;

use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\Tag;
use App\Support\Demo\DemoLock;
use App\Support\SoundLibrary;
use App\Support\TableAudio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Bibliothèque sonore de la campagne : musiques et ambiances préparées avant la partie,
 * lancées ensuite depuis le mode Session. On y ajoute aussi les sons de sa bibliothèque
 * personnelle, sans copier le fichier.
 */
class Index extends Component
{
    use WithFileUploads;

    public Campaign $campaign;

    #[Url]
    public string $tag = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    public string $tags = '';

    public bool $loop = true;

    /** Téléverser dans sa bibliothèque personnelle (et l'ajouter ici) plutôt que dans la campagne. */
    public bool $toLibrary = false;

    /** @var list<int|string> sons de sa bibliothèque à ajouter à la campagne */
    public array $libraryPicks = [];

    public ?int $editingId = null;

    public string $editTitle = '';

    public string $editTags = '';

    public bool $editLoop = true;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<int, AudioTrack> */
    #[Computed]
    public function tracks(): Collection
    {
        return $this->campaign->availableTracks()
            ->with(['tags', 'owner:id,name'])
            ->when($this->tag !== '', fn (Builder $q) => $q->whereHas('tags', fn (Builder $t) => $t->whereRaw('lower(tags.name) = ?', [mb_strtolower($this->tag)])))
            ->orderByRaw('lower(title)')
            ->get();
    }

    /** @return list<string> */
    #[Computed]
    public function tagNames(): array
    {
        return Tag::query()
            ->whereHas('audioTracks', fn (Builder $q) => $q->availableIn($this->campaign))
            ->orderByRaw('lower(name)')
            ->pluck('name')
            ->all();
    }

    /** @return Collection<int, AudioTrack> sons de ma bibliothèque pas encore dans la campagne */
    #[Computed]
    public function libraryChoices(): Collection
    {
        return auth()->user()->libraryTracks()
            ->whereNotIn('id', $this->campaign->availableTracks()->select('audio_tracks.id'))
            ->orderByRaw('lower(title)')
            ->get();
    }

    public function saveUploads(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate(SoundLibrary::rules(), attributes: ['uploads' => __('fichiers'), 'uploads.*' => __('fichier')]);

        $owner = $this->toLibrary ? auth()->user() : $this->campaign->owner;
        DemoLock::guardCampaign($this->campaign);
        $ids = SoundLibrary::store($this->uploads, $owner, $this->toLibrary ? null : $this->campaign, $this->loop, Tag::idsFromInput($owner, $this->tags));

        if ($this->toLibrary) {
            $this->campaign->libraryTracks()->syncWithoutDetaching($ids);
        }

        $this->reset('uploads', 'tags');
        unset($this->tracks, $this->tagNames, $this->libraryChoices);
    }

    /** Ajoute à la campagne des sons de ma bibliothèque : le fichier n'est pas copié. */
    public function addFromLibrary(): void
    {
        $this->authorize('update', $this->campaign);

        foreach (auth()->user()->libraryTracks()->whereKey(array_map('intval', $this->libraryPicks))->get() as $track) {
            SoundLibrary::link($track, $this->campaign);
        }

        $this->reset('libraryPicks');
        unset($this->tracks, $this->tagNames, $this->libraryChoices);
    }

    /** Range un son de la campagne dans ma bibliothèque, pour le réutiliser ailleurs. */
    public function moveToLibrary(int $id): void
    {
        $track = $this->track($id);
        abort_if($track->inLibrary() || $track->user_id !== auth()->id(), 403);

        SoundLibrary::moveToLibrary($track);

        unset($this->tracks, $this->tagNames, $this->libraryChoices);
    }

    /** Un son de bibliothèque quitte la campagne ; il reste dans la bibliothèque de son MJ. */
    public function removeFromCampaign(int $id): void
    {
        $track = $this->track($id);
        abort_unless($track->inLibrary(), 404);

        SoundLibrary::detach($track, $this->campaign);

        unset($this->tracks, $this->tagNames, $this->libraryChoices);
    }

    public function edit(int $id): void
    {
        $track = $this->track($id);
        $this->authorize('update', $track);

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
        $this->authorize('update', $track);

        $this->validate([
            'editTitle' => ['required', 'string', 'max:255'],
            'editTags' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['editTitle' => __('titre'), 'editTags' => __('tags')]);

        $track->update(['title' => trim($this->editTitle), 'loop' => $this->editLoop]);
        $track->tags()->sync(Tag::idsFromInput($track->owner, $this->editTags));

        $this->cancel();
        unset($this->tracks, $this->tagNames);
    }

    public function delete(int $id): void
    {
        $track = $this->track($id);
        abort_if($track->inLibrary(), 404);
        $this->authorize('delete', $track);

        SoundLibrary::delete($track);
        unset($this->tracks, $this->tagNames, $this->libraryChoices);
    }

    public function playOnTable(int $id): void
    {
        $this->authorize('update', $this->campaign);

        TableAudio::play($this->campaign, $this->track($id));
    }

    private function track(int $id): AudioTrack
    {
        $this->authorize('update', $this->campaign);

        return $this->campaign->availableTracks()->with('tags')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.audio.index')->title(__('Sons · :name', ['name' => $this->campaign->name]));
    }
}
