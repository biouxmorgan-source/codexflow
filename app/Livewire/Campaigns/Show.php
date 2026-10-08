<?php

namespace App\Livewire\Campaigns;

use App\Actions\Duplication\DuplicateCampaign;
use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Tag;
use App\Support\Plans\Plans;
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

    #[Url(as: 'tag', except: '')]
    public string $tag = '';

    public function mount(Campaign $campaign): void
    {
        $user = auth()->user();

        // Un joueur ou un spectateur qui arrive ici (lien partagé, favori) va sur sa propre page.
        if ($user->cannot('update', $campaign)) {
            $role = $campaign->roleOf($user);
            $character = $role === CampaignRole::Player
                ? $campaign->playerCharacters()->active()->where('user_id', $user->id)->first()
                : null;

            match (true) {
                $role === CampaignRole::Spectator => $this->redirectRoute('table.screen', $campaign),
                $character !== null => $this->redirectRoute('characters.show', [$campaign, $character], navigate: true),
                default => $this->authorize('update', $campaign),
            };
        }
    }

    /** @return Collection<int, Entity> */
    #[Computed]
    public function entities(): Collection
    {
        return $this->campaign->availableEntities()
            ->with([
                'type',
                'tags',
                'campaignStates' => fn ($q) => $q->where('campaign_id', $this->campaign->getKey()),
            ])
            ->when($this->search !== '', fn ($q) => $q->where('name', 'ilike', '%'.addcslashes($this->search, '%_\\').'%'))
            ->when($this->type !== '', fn ($q) => $q->where('entity_type_id', $this->type))
            ->when($this->tag !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->whereKey((int) $this->tag)))
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, EntityType> */
    #[Computed]
    public function types(): Collection
    {
        return EntityType::query()->availableTo($this->campaign->owner)->orderBy('id')->get();
    }

    /**
     * Étiquettes portées par au moins une fiche de la campagne.
     *
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function tags(): Collection
    {
        return Tag::query()
            ->where('user_id', $this->campaign->user_id)
            ->whereHas('entities', fn ($q) => $q->availableIn($this->campaign))
            ->orderByRaw('lower(name)')
            ->get();
    }

    public function duplicate(DuplicateCampaign $duplicateCampaign): void
    {
        $this->authorize('duplicate', $this->campaign);
        Plans::ensure(auth()->user(), 'duplication');
        Plans::ensureCanCreateCampaign(auth()->user());
        Plans::ensureRoom(auth()->user(), 0, 'plan');

        $copy = $duplicateCampaign->handle($this->campaign, auth()->user());

        $this->redirectRoute('campaigns.show', $copy, navigate: true);
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
