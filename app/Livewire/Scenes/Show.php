<?php

namespace App\Livewire\Scenes;

use App\Enums\SceneStatus;
use App\Models\Campaign;
use App\Models\MapPoint;
use App\Models\Scene;
use App\Models\ToPlayItem;
use App\Support\CampaignFeatures;
use App\Support\EntityLinks;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;

    public Scene $scene;

    public function mount(Campaign $campaign, Scene $scene): void
    {
        $this->authorize('update', $campaign);
        abort_unless($scene->scenario->campaign_id === $campaign->id, 404);
    }

    public function setStatus(string $status): void
    {
        $this->authorize('update', $this->campaign);

        $this->scene->status = SceneStatus::from($status);
        $this->scene->save();
    }

    /** « Jouée comme prévu » d'un geste, après la partie : la scène passe aussi en « Jouée ». */
    public function togglePlayedAsPlanned(): void
    {
        $this->authorize('update', $this->campaign);

        $this->scene->played_as_planned = ! $this->scene->played_as_planned;

        if ($this->scene->played_as_planned && $this->scene->status !== SceneStatus::Skipped) {
            $this->scene->status = SceneStatus::Played;
        }

        $this->scene->save();
    }

    public string $toPlayBody = '';

    public function addToPlay(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate(['toPlayBody' => ['required', 'string', 'max:500']], attributes: ['toPlayBody' => __('élément à jouer')]);

        $item = new ToPlayItem(['body' => trim($this->toPlayBody), 'position' => (int) $this->campaign->toPlayItems()->max('position') + 1]);
        $item->campaign()->associate($this->campaign);
        $this->scene->toPlayItems()->save($item);

        $this->reset('toPlayBody');
    }

    public function deleteToPlay(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $this->scene->toPlayItems()->findOrFail($id)->delete();
    }

    public function delete(): void
    {
        $this->authorize('update', $this->campaign);

        $this->scene->delete();

        $this->redirectRoute('scenarios.index', $this->campaign, navigate: true);
    }

    public function render()
    {
        $siblings = $this->scene->scenario->scenes()->get(['id', 'name']);
        $index = $siblings->search(fn (Scene $scene) => $scene->is($this->scene));

        return view('livewire.scenes.show', [
            'description' => EntityLinks::render($this->scene->description, $this->campaign),
            'whatHappened' => EntityLinks::render($this->scene->what_happened, $this->campaign),
            'entities' => $this->scene->entities()
                ->with(['type', 'campaignStates' => fn ($q) => $q->where('campaign_id', $this->campaign->id)])
                ->get(),
            'toPlay' => $this->scene->toPlayItems()->get(),
            'rules' => $this->scene->rules()->availableIn($this->campaign)->get(),
            'documents' => $this->scene->documents()->availableIn($this->campaign)->get(),
            'tracks' => $this->scene->audioTracks()->availableIn($this->campaign)->get(),
            'previous' => $index > 0 ? $siblings[$index - 1] : null,
            'next' => $siblings[$index + 1] ?? null,
            // Les points de la carte de campagne qui situent la scène.
            'mapPoints' => CampaignFeatures::enabled($this->campaign, 'maps')
                ? MapPoint::query()->where('scene_id', $this->scene->id)->whereHas('map', fn ($q) => $q->where('campaign_id', $this->campaign->id))->with('map')->get()
                : collect(),
        ])->title($this->scene->name);
    }
}
