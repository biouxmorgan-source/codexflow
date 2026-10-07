<?php

namespace App\Livewire\Documents;

use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Bibliothèque de documents de la campagne : ceux du jeu, du monde et de la campagne.
 */
class Index extends Component
{
    use WithFileUploads;

    public Campaign $campaign;

    #[Url]
    public string $kind = '';

    #[Url]
    public string $tag = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    public string $scope = 'campaign';

    public string $zone = 'gm';

    public string $tags = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<int, Document> */
    #[Computed]
    public function documents(): Collection
    {
        return $this->campaign->availableDocuments()
            ->with('tags')
            ->when($this->kind === 'pdf', fn (Builder $q) => $q->where('mime_type', 'application/pdf'))
            ->when($this->kind === 'image', fn (Builder $q) => $q->where('mime_type', 'like', 'image/%'))
            ->when($this->tag !== '', fn (Builder $q) => $q->whereHas('tags', fn (Builder $t) => $t->whereRaw('lower(tags.name) = ?', [mb_strtolower($this->tag)])))
            ->orderByRaw('lower(title)')
            ->get();
    }

    /** @return list<string> */
    #[Computed]
    public function tagNames(): array
    {
        return Tag::query()
            ->whereHas('documents', fn (Builder $q) => $q->availableIn($this->campaign))
            ->orderByRaw('lower(name)')
            ->pluck('name')
            ->all();
    }

    /** @return array<string, string> */
    public function scopes(): array
    {
        return array_filter([
            'campaign' => __('Cette campagne'),
            'world' => $this->campaign->world ? __('Le monde :name', ['name' => $this->campaign->world->name]) : null,
            'game' => __('Le jeu :name', ['name' => $this->campaign->gameSystem->name]),
        ]);
    }

    public function saveUploads(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'uploads' => ['required', 'array', 'max:10'],
            'uploads.*' => ['file', 'mimes:'.Document::MIMES, 'max:51200'],
            'scope' => ['required', Rule::in(array_keys($this->scopes()))],
            'zone' => ['required', Rule::enum(Zone::class)],
            'tags' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['uploads' => __('fichiers'), 'uploads.*' => __('fichier'), 'scope' => __('rangement'), 'zone' => __('visibilité')]);

        match ($this->scope) {
            'game' => $this->authorize('update', $this->campaign->gameSystem),
            'world' => $this->authorize('update', $this->campaign->world),
            default => null,
        };

        $tagIds = Tag::idsFromInput(auth()->user(), $this->tags);

        foreach ($this->uploads as $file) {
            $name = mb_substr($file->getClientOriginalName(), 0, 255);
            $document = new Document([
                'title' => mb_substr(pathinfo($name, PATHINFO_FILENAME) ?: $name, 0, 255),
                'zone' => $this->zone,
                'disk' => Document::DISK,
                'path' => $file->store('documents', Document::DISK),
                'original_name' => $name,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
            $document->owner()->associate(auth()->user());
            $document->game_system_id = $this->scope === 'game' ? $this->campaign->game_system_id : null;
            $document->world_id = $this->scope === 'world' ? $this->campaign->world_id : null;
            $document->campaign_id = $this->scope === 'campaign' ? $this->campaign->id : null;
            $document->save();
            $document->tags()->sync($tagIds);
        }

        $this->reset('uploads', 'tags');
        unset($this->documents, $this->tagNames);
    }

    public function render()
    {
        return view('livewire.documents.index')->title(__('Documents · :name', ['name' => $this->campaign->name]));
    }
}
