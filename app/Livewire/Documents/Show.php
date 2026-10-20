<?php

namespace App\Livewire\Documents;

use App\Enums\Zone;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Tag;
use App\Support\Plans\Plans;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Show extends Component
{
    use SuggestsEntities;
    use WithFileUploads;

    public Campaign $campaign;

    public Document $document;

    public string $title = '';

    public string $description = '';

    public string $zone = 'gm';

    public string $tags = '';

    public bool $saved = false;

    /** Nouveau fichier qui remplace l'ancien, en gardant le document et tous ses liens. */
    public ?TemporaryUploadedFile $replacement = null;

    public bool $replaced = false;

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
        $this->replaced = false;
    }

    /**
     * Remplace le fichier sans toucher au document : scènes, fiches, règles, cartes,
     * secrets et personnages qui l'ont reçu montrent aussitôt la nouvelle version.
     */
    public function replaceFile(): void
    {
        $this->authorize('update', $this->document);

        // Une carte posée sur cette image a besoin d'une image.
        $map = $this->document->maps()->exists();

        $this->validate([
            'replacement' => ['required', 'file', 'mimes:'.($map ? 'jpg,jpeg,png,gif,webp' : Document::MIMES), 'max:51200'],
        ], $map ? ['replacement.mimes' => __('Ce document sert de carte : choisissez une image.')] : [], ['replacement' => __('fichier')]);

        Plans::ensureRoom($this->campaign->owner, max(0, (int) $this->replacement->getSize() - $this->document->size), 'replacement');

        if ($map) {
            $size = @getimagesize($this->replacement->getRealPath());

            if ($size === false || $size[0] < 1 || $size[1] < 1) {
                $this->addError('replacement', __('Cette image ne peut pas être lue.'));

                return;
            }
        }

        $old = $this->document->path;
        $name = mb_substr($this->replacement->getClientOriginalName(), 0, 255);

        $this->document->fill([
            'path' => $this->replacement->store('documents', $this->document->disk),
            'original_name' => $name,
            'mime_type' => $this->replacement->getMimeType() ?? 'application/octet-stream',
            'size' => $this->replacement->getSize(),
        ])->save();
        Storage::disk($this->document->disk)->delete($old);

        if ($map) {
            $this->document->maps->each->resizeTo($size[0], $size[1]);
        }

        $this->reset('replacement');
        $this->replaced = true;
        $this->saved = false;
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->document);

        $this->document->delete();

        $this->redirectRoute('documents.index', $this->campaign, navigate: true);
    }

    public function render()
    {
        $map = $this->document->maps()->first(['width', 'height']);

        return view('livewire.documents.show', [
            'mapSize' => $map ? ['width' => $map->width, 'height' => $map->height] : null,
            'scenes' => $this->campaign->scenes()->whereHas('documents', fn ($q) => $q->whereKey($this->document->id))->with('scenario')->get(),
            'entities' => $this->campaign->availableEntities()->whereHas('documents', fn ($q) => $q->whereKey($this->document->id))->orderBy('name')->get(),
            'rules' => $this->campaign->availableRules()->whereHas('documents', fn ($q) => $q->whereKey($this->document->id))->orderBy('title')->get(),
        ])->title($this->document->title);
    }
}
