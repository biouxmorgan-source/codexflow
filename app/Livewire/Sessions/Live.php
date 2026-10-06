<?php

namespace App\Livewire\Sessions;

use App\Enums\SceneStatus;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\PlaySession;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\SessionNote;
use App\Models\ToPlayItem;
use App\Support\SessionContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Écran du MJ pendant la partie : scène en cours, fiches utiles, « À jouer », « Épinglé » et notes.
 */
class Live extends Component
{
    use SuggestsEntities;

    public Campaign $campaign;

    public string $noteBody = '';

    public string $toPlayBody = '';

    public bool $toPlayForScene = true;

    public ?int $pickedPinId = null;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);
    }

    #[Computed]
    public function session(): ?PlaySession
    {
        return $this->campaign->openSession()?->load('currentScene.scenario');
    }

    /** @return Collection<int, Scene> */
    #[Computed]
    public function scenes(): Collection
    {
        return $this->campaign->scenes()
            ->with('scenario')
            ->orderBy('scenarios.position')
            ->orderBy('scenarios.id')
            ->orderBy('scenes.position')
            ->orderBy('scenes.id')
            ->get();
    }

    /** @return SupportCollection<int, array{entity: Entity, note: ?string, source: string}> */
    #[Computed]
    public function cards(): SupportCollection
    {
        return SessionContext::cards($this->campaign, $this->session?->currentScene);
    }

    /** @return Collection<int, Rule> */
    #[Computed]
    public function rules(): Collection
    {
        return SessionContext::rules($this->campaign, $this->session?->currentScene);
    }

    /** @return Collection<int, Document> */
    #[Computed]
    public function documents(): Collection
    {
        return SessionContext::documents($this->campaign, $this->session?->currentScene);
    }

    /** @return Collection<int, Entity> */
    #[Computed]
    public function pins(): Collection
    {
        $pins = $this->campaign->pins()->get();
        SessionContext::load($this->campaign, $pins);

        return $pins;
    }

    /** @return Collection<int, ToPlayItem> */
    #[Computed]
    public function toPlay(): Collection
    {
        $sceneId = $this->session?->current_scene_id;

        return $this->campaign->toPlayItems()
            ->pending()
            ->where(fn ($q) => $q->whereNull('scene_id')->when($sceneId, fn ($q) => $q->orWhere('scene_id', $sceneId)))
            ->with(['scene', 'rule'])
            ->get();
    }

    /** @return Collection<int, SessionNote> */
    #[Computed]
    public function notes(): Collection
    {
        return $this->session?->notes()->with('scene')->latest()->latest('id')->get() ?? new Collection;
    }

    /** @return Collection<int, PlaySession> */
    #[Computed]
    public function pastSessions(): Collection
    {
        return $this->campaign->playSessions()->whereNotNull('ended_at')->withCount('notes')->latest('number')->get();
    }

    public function start(): void
    {
        $this->authorize('update', $this->campaign);

        if ($this->session !== null) {
            return;
        }

        $session = $this->campaign->playSessions()->create([
            'number' => (int) $this->campaign->playSessions()->max('number') + 1,
            'started_at' => now(),
        ]);

        // Reprend là où la dernière séance s'est arrêtée, sinon à la première scène à jouer.
        $resume = $this->scenes->first(fn (Scene $scene) => $scene->status === SceneStatus::InProgress)
            ?? $this->scenes->first(fn (Scene $scene) => in_array($scene->status, [SceneStatus::Available, SceneStatus::Planned], true));

        if ($resume) {
            $this->activate($session, $resume);
        }

        $this->refreshAll();
    }

    public function end(): void
    {
        $this->authorize('update', $this->campaign);

        $this->session?->update(['ended_at' => now()]);
        $this->refreshAll();
    }

    public function setScene(?int $sceneId): void
    {
        $this->authorize('update', $this->campaign);

        $session = $this->session;
        abort_if($session === null, 404);

        $scene = $sceneId ? $this->campaign->scenes()->findOrFail($sceneId) : null;
        $previous = $session->currentScene;

        if ($previous && $previous->status === SceneStatus::InProgress && ! $previous->is($scene)) {
            $previous->update(['status' => SceneStatus::Available]);
        }

        $this->activate($session, $scene);
        $this->refreshAll();
    }

    /**
     * Marque la scène en cours comme jouée et passe à la suivante encore à jouer.
     */
    public function nextScene(): void
    {
        $this->authorize('update', $this->campaign);

        $session = $this->session;
        abort_if($session === null || $session->currentScene === null, 404);

        $current = $session->currentScene;
        $current->update(['status' => SceneStatus::Played]);

        $index = $this->scenes->search(fn (Scene $scene) => $scene->is($current));
        $next = $this->scenes->slice($index + 1)
            ->first(fn (Scene $scene) => ! in_array($scene->status, [SceneStatus::Played, SceneStatus::Skipped], true));

        $this->activate($session, $next);
        $this->refreshAll();
    }

    public function addNote(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate(['noteBody' => ['required', 'string', 'max:5000']], attributes: ['noteBody' => 'note']);

        $session = $this->session;
        abort_if($session === null, 404);

        $note = new SessionNote(['body' => trim($this->noteBody)]);
        $note->author()->associate(auth()->user());
        $note->scene_id = $session->current_scene_id;
        $session->notes()->save($note);

        $this->reset('noteBody');
        unset($this->notes);
    }

    public function deleteNote(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $this->session?->notes()->findOrFail($id)->delete();
        unset($this->notes);
    }

    public function addToPlay(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate(['toPlayBody' => ['required', 'string', 'max:500']], attributes: ['toPlayBody' => 'élément à jouer']);

        $item = new ToPlayItem(['body' => trim($this->toPlayBody), 'position' => (int) $this->campaign->toPlayItems()->max('position') + 1]);
        $item->scene_id = $this->toPlayForScene ? $this->session?->current_scene_id : null;
        $this->campaign->toPlayItems()->save($item);

        $this->reset('toPlayBody');
        unset($this->toPlay);
    }

    public function markPlayed(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $this->campaign->toPlayItems()->findOrFail($id)->update(['done_at' => now()]);
        unset($this->toPlay);
    }

    public function pin(): void
    {
        $this->authorize('update', $this->campaign);

        $entity = $this->pickedPinId ? $this->campaign->availableEntities()->find($this->pickedPinId) : null;

        if ($entity === null) {
            $this->addError('pickedPinId', 'Choisissez une fiche dans la liste.');

            return;
        }

        $this->campaign->pins()->syncWithoutDetaching([
            $entity->id => ['position' => (int) DB::table('campaign_pins')->where('campaign_id', $this->campaign->id)->max('position') + 1],
        ]);

        $this->pickedPinId = null;
        unset($this->pins);
    }

    public function unpin(int $entityId): void
    {
        $this->authorize('update', $this->campaign);

        $this->campaign->pins()->detach($entityId);
        unset($this->pins);
    }

    private function activate(PlaySession $session, ?Scene $scene): void
    {
        if ($scene && $scene->status !== SceneStatus::InProgress) {
            $scene->update(['status' => SceneStatus::InProgress]);
        }

        $session->currentScene()->associate($scene);
        $session->save();
    }

    private function refreshAll(): void
    {
        unset($this->session, $this->scenes, $this->cards, $this->toPlay, $this->notes, $this->pastSessions);
    }

    public function render()
    {
        return view('livewire.sessions.live', [
            'fieldDefinitions' => $this->campaign->gameSystem->fieldDefinitions()->ordered()->get(),
        ])->title('Session · '.$this->campaign->name);
    }
}
