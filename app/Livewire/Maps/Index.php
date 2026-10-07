<?php

namespace App\Livewire\Maps;

use App\Models\Campaign;
use App\Models\Document;
use App\Models\TableMap;
use App\Support\TableDisplay;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Les cartes de la campagne : une image des documents, préparée pour l'écran de table
 * (grille, jetons), puis montrée aux joueurs.
 */
class Index extends Component
{
    public Campaign $campaign;

    /** Pré-sélectionne une image : « En faire une carte » depuis un document. */
    #[Url(as: 'document', except: '')]
    public string $documentId = '';

    public string $name = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);

        if ($this->documentId !== '') {
            $this->name = (string) $this->images->find((int) $this->documentId)?->title;
        }
    }

    /** @return Collection<int, Document> images utilisables dans la campagne */
    #[Computed]
    public function images(): Collection
    {
        return $this->campaign->availableDocuments()->where('mime_type', 'like', 'image/%')->orderBy('title')->get();
    }

    public function updatedDocumentId(): void
    {
        $this->name = (string) $this->images->find((int) $this->documentId)?->title;
    }

    public function create(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'documentId' => ['required', Rule::in($this->images->modelKeys())],
            'name' => ['required', 'string', 'max:255'],
        ], attributes: ['documentId' => __('image'), 'name' => __('nom')]);

        $document = $this->images->find((int) $this->documentId);
        $size = @getimagesizefromstring((string) Storage::disk($document->disk)->get($document->path));

        if ($size === false || $size[0] < 1 || $size[1] < 1) {
            $this->addError('documentId', __('Cette image ne peut pas être lue.'));

            return;
        }

        $map = new TableMap(['name' => trim($this->name)]);
        $map->campaign()->associate($this->campaign);
        $map->document()->associate($document);
        $map->width = $size[0];
        $map->height = $size[1];
        $map->grid_size = TableMap::defaultGridSize($size[0]);
        $map->save();

        $this->redirectRoute('maps.show', [$this->campaign, $map], navigate: true);
    }

    public function delete(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $map = $this->campaign->maps()->findOrFail($id);

        if (TableDisplay::isShowing($this->campaign, 'map', $map->id)) {
            TableDisplay::clear($this->campaign);
        }

        $map->delete();
    }

    public function render()
    {
        return view('livewire.maps.index', [
            'maps' => $this->campaign->maps()->with('document')->withCount('tokens')->get(),
        ])->title(__('Cartes · :name', ['name' => $this->campaign->name]));
    }
}
