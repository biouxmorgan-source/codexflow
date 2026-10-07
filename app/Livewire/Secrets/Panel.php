<?php

namespace App\Livewire\Secrets;

use App\Livewire\Concerns\RevealsSecrets;
use App\Models\Campaign;
use App\Models\Secret;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Les secrets reliés à une fiche, une scène ou un document (ou à plusieurs, en mode Session),
 * avec la révélation d'un clic.
 */
class Panel extends Component
{
    use RevealsSecrets;

    #[Locked]
    public Campaign $campaign;

    /** @var array<string, list<int>> éléments suivis : ['entity' => [..], 'scene' => [..], 'document' => [..]] */
    #[Locked]
    public array $items = [];

    /** Élément auquel un nouveau secret sera relié (« entity:12 »), vide en mode Session. */
    #[Locked]
    public string $link = '';

    public bool $compact = false;

    public function mount(Campaign $campaign, array $items, string $link = '', bool $compact = false): void
    {
        $this->authorize('update', $campaign);

        $this->items = array_map(fn ($ids) => array_values(array_map('intval', (array) $ids)), array_intersect_key($items, array_flip(['entity', 'scene', 'document'])));
    }

    /** @return Collection<int, Secret> */
    #[Computed]
    public function secrets(): Collection
    {
        $relations = ['entity' => 'entities', 'scene' => 'scenes', 'document' => 'documents'];

        return $this->campaign->secrets()
            ->where(function ($query) use ($relations) {
                $query->whereRaw('false');

                foreach ($this->items as $kind => $ids) {
                    if ($ids !== []) {
                        $query->orWhereHas($relations[$kind], fn ($q) => $q->whereKey($ids));
                    }
                }
            })
            ->with(['entities', 'scenes', 'documents', 'grants'])
            ->orderByRaw('lower(title)')
            ->get();
    }

    #[On('character-grants-changed')]
    public function refresh(): void
    {
        unset($this->secrets);
    }

    protected function secretsChanged(): void
    {
        unset($this->secrets);
    }

    public function render()
    {
        return view('livewire.secrets.panel');
    }
}
