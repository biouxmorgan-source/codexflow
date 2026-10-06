<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'type', except: '')]
    public string $type = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    /** @return Collection<int, Entity> */
    #[Computed]
    public function entities(): Collection
    {
        return $this->campaign->availableEntities()
            ->with([
                'type',
                'campaignStates' => fn ($q) => $q->where('campaign_id', $this->campaign->getKey()),
            ])
            ->when($this->search !== '', fn ($q) => $q->where('name', 'ilike', '%'.addcslashes($this->search, '%_\\').'%'))
            ->when($this->type !== '', fn ($q) => $q->where('entity_type_id', $this->type))
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()->availableTo(auth()->user())->orderBy('id')->get();
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->campaign);

        DB::transaction(fn () => $this->campaign->delete());

        $this->redirectRoute('campaigns.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.campaigns.show')->title($this->campaign->name);
    }
}
