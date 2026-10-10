<?php

namespace App\Livewire\Audio;

use App\Models\AudioTrack;
use App\Models\Campaign;
use App\Models\Tag;
use App\Support\Plans\Plans;
use App\Support\TableAudio;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Bibliothèque sonore de la campagne : musiques et ambiances préparées avant la partie,
 * lancées ensuite depuis le mode Session.
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
        return $this->campaign->audioTracks()
            ->with('tags')
            ->when($this->tag !== '', fn (Builder $q) => $q->whereHas('tags', fn (Builder $t) => $t->whereRaw('lower(tags.name) = ?', [mb_strtolower($this->tag)])))
            ->orderByRaw('lower(title)')
            ->get();
    }

    /** @return list<string> */
    #[Computed]
    public function tagNames(): array
    {
        return Tag::query()
            ->whereHas('audioTracks', fn (Builder $q) => $q->where('campaign_id', $this->campaign->id))
            ->orderByRaw('lower(name)')
            ->pluck('name')
            ->all();
    }

    public function saveUploads(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'uploads' => ['required', 'array', 'max:20'],
            'uploads.*' => ['file', 'max:'.AudioTrack::MAX_KB, function (string $attribute, mixed $file, Closure $fail) {
                if (! $file instanceof TemporaryUploadedFile || ! AudioTrack::accepts($file)) {
                    $fail(__('Le fichier doit être un son : mp3, ogg, m4a, wav, flac ou webm.'));
                }
            }],
            'tags' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['uploads' => __('fichiers'), 'uploads.*' => __('fichier')]);

        Plans::ensureRoom($this->campaign->owner, array_sum(array_map(fn ($file) => (int) $file->getSize(), $this->uploads)), 'uploads');

        $tagIds = Tag::idsFromInput($this->campaign->owner, $this->tags);

        foreach ($this->uploads as $file) {
            $name = mb_substr($file->getClientOriginalName(), 0, 255);
            $track = new AudioTrack([
                'title' => mb_substr(pathinfo($name, PATHINFO_FILENAME) ?: $name, 0, 255),
                'loop' => $this->loop,
                'disk' => AudioTrack::DISK,
                'path' => $file->storeAs('audio', Str::random(40).'.'.strtolower($file->getClientOriginalExtension()), AudioTrack::DISK),
                'original_name' => $name,
                'mime_type' => AudioTrack::audioMime($file),
                'size' => $file->getSize(),
            ]);
            $track->owner()->associate($this->campaign->owner);
            $track->campaign()->associate($this->campaign);
            $track->save();
            $track->tags()->sync($tagIds);
        }

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
        $track->tags()->sync(Tag::idsFromInput($this->campaign->owner, $this->editTags));

        $this->cancel();
        unset($this->tracks, $this->tagNames);
    }

    public function delete(int $id): void
    {
        $track = $this->track($id);
        $this->authorize('delete', $track);

        if (($this->campaign->table_audio['track'] ?? null) === $track->id) {
            TableAudio::stop($this->campaign);
        }

        $track->delete();
        unset($this->tracks, $this->tagNames);
    }

    public function playOnTable(int $id): void
    {
        $this->authorize('update', $this->campaign);

        TableAudio::play($this->campaign, $this->track($id));
    }

    private function track(int $id): AudioTrack
    {
        $this->authorize('update', $this->campaign);

        return $this->campaign->audioTracks()->with('tags')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.audio.index')->title(__('Sons · :name', ['name' => $this->campaign->name]));
    }
}
