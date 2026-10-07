<?php

namespace App\Livewire\Documents;

use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;

    public Document $document;

    public string $title = '';

    public string $description = '';

    public string $zone = 'gm';

    public string $tags = '';

    public bool $saved = false;

    public function mount(Campaign $campaign, Document $document): void
    {
        $this->authorize('update', $campaign);
        abort_unless($campaign->availableDocuments()->whereKey($document->id)->exists(), 404);
        $this->authorize('view', $document);

        $this->title = $document->title;
        $this->description = (string) $document->description;
        $this->zone = $document->zone->value;
        $this->tags = $document->tags->pluck('name')->implode(', ');
    }

    public function save(): void
    {
        $this->authorize('update', $this->document);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'zone' => ['required', Rule::enum(Zone::class)],
            'tags' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['title' => __('titre'), 'zone' => __('visibilité')]);

        $this->document->fill([
            'title' => trim($this->title),
            'description' => trim($this->description) ?: null,
            'zone' => $this->zone,
        ])->save();
        $this->document->tags()->sync(Tag::idsFromInput($this->campaign->owner, $this->tags));
        $this->document->unsetRelation('tags');

        $this->saved = true;
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->document);

        $this->document->delete();

        $this->redirectRoute('documents.index', $this->campaign, navigate: true);
    }

    public function render()
    {
        return view('livewire.documents.show', [
            'scenes' => $this->campaign->scenes()->whereHas('documents', fn ($q) => $q->whereKey($this->document->id))->with('scenario')->get(),
            'entities' => $this->campaign->availableEntities()->whereHas('documents', fn ($q) => $q->whereKey($this->document->id))->orderBy('name')->get(),
            'rules' => $this->campaign->availableRules()->whereHas('documents', fn ($q) => $q->whereKey($this->document->id))->orderBy('title')->get(),
        ])->title($this->document->title);
    }
}
