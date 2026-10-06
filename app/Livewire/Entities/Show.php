<?php

namespace App\Livewire\Entities;

use App\Models\Campaign;
use App\Models\CampaignEntityState;
use App\Models\Entity;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;

    public Entity $entity;

    public string $status = '';

    public string $stateNotes = '';

    public bool $stateSaved = false;

    public function mount(Campaign $campaign, Entity $entity): void
    {
        $this->authorize('update', $campaign);
        abort_unless($campaign->availableEntities()->whereKey($entity->getKey())->exists(), 404);
        $this->authorize('view', $entity);

        $state = $this->state();
        $this->status = (string) $state->status;
        $this->stateNotes = (string) $state->gm_notes;
    }

    public function saveState(): void
    {
        $this->authorize('update', $this->entity);

        $this->validate([
            'status' => ['nullable', 'string', 'max:60'],
            'stateNotes' => ['nullable', 'string', 'max:20000'],
        ], attributes: ['status' => 'statut', 'stateNotes' => 'notes de campagne']);

        $state = $this->state();
        $state->fill([
            'status' => $this->status ?: null,
            'gm_notes' => $this->stateNotes ?: null,
        ]);

        if ($state->exists || $state->status !== null || $state->gm_notes !== null) {
            $state->save();
        }

        $this->stateSaved = true;
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->entity);

        $this->entity->delete();

        $this->redirectRoute('campaigns.show', $this->campaign, navigate: true);
    }

    private function state(): CampaignEntityState
    {
        return $this->entity->stateIn($this->campaign);
    }

    public function render()
    {
        return view('livewire.entities.show', [
            'otherCampaigns' => $this->entity->isWorldEntity()
                ? $this->entity->world->campaigns()->whereKeyNot($this->campaign->getKey())->count()
                : 0,
        ])->title($this->entity->name);
    }
}
