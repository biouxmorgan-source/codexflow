<?php

namespace App\Livewire\Timeline;

use App\Enums\Zone;
use App\Livewire\Concerns\SuggestsEntities;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\PlayerCharacter;
use App\Models\TimelineEvent;
use App\Support\EntityLinks;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Chronologie de la campagne : histoire du monde, événements prévus, événements joués.
 * Le MJ la tient ; les joueurs lisent les événements en zone publique.
 */
class Index extends Component
{
    use SuggestsEntities;

    public Campaign $campaign;

    #[Url(as: 'type', except: '')]
    public string $kind = '';

    public bool $editing = false;

    public ?int $editingId = null;

    public string $formKind = 'world';

    public string $dateLabel = '';

    public string $title = '';

    public string $description = '';

    public bool $public = false;

    public string $sessionId = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorize('play', $campaign);
    }

    #[Computed]
    public function isGameMaster(): bool
    {
        return $this->campaign->isGameMaster(auth()->user());
    }

    /** Le personnage du joueur, pour ne relier que les fiches qu'il connaît. */
    #[Computed]
    public function character(): ?PlayerCharacter
    {
        return $this->isGameMaster ? null : $this->campaign->playerCharacters()->active()->where('user_id', auth()->id())->first();
    }

    /** @return Collection<int, TimelineEvent> */
    #[Computed]
    public function events(): Collection
    {
        return $this->campaign->timelineEvents()
            ->when(! $this->isGameMaster, fn ($q) => $q->visibleToPlayers())
            ->when(in_array($this->kind, TimelineEvent::KINDS, true), fn ($q) => $q->where('kind', $this->kind))
            ->with(['playSession', 'scene'])
            ->ordered()
            ->get();
    }

    public function linkedDescription(TimelineEvent $event): HtmlString
    {
        if ($this->isGameMaster) {
            return EntityLinks::render($event->description, $this->campaign);
        }

        $character = $this->character;
        $known = $character ? $character->grants()->where('kind', 'entity')->pluck('entity_id')->push($character->entity_id)->flip() : collect();

        return EntityLinks::render($event->description, $this->campaign, fn (Entity $entity) => isset($known[$entity->id]) && $entity->id !== $character?->entity_id
            ? route('characters.entity', [$this->campaign, $character, $entity])
            : null);
    }

    public function create(string $kind = 'world'): void
    {
        $this->authorize('update', $this->campaign);

        $this->resetValidation();
        $this->reset('editingId', 'dateLabel', 'title', 'description');
        $this->formKind = in_array($kind, TimelineEvent::KINDS, true) ? $kind : 'world';
        $this->public = TimelineEvent::defaultZone($this->formKind) === Zone::Public;
        $this->sessionId = $this->formKind === 'played' ? (string) $this->campaign->openSession()?->id : '';
        $this->editing = true;
    }

    public function updatedFormKind(): void
    {
        if ($this->editingId === null) {
            $this->public = TimelineEvent::defaultZone($this->formKind) === Zone::Public;
        }
    }

    public function edit(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $event = $this->campaign->timelineEvents()->findOrFail($id);
        $this->resetValidation();
        $this->fill([
            'editingId' => $event->id,
            'formKind' => $event->kind,
            'dateLabel' => (string) $event->date_label,
            'title' => $event->title,
            'description' => (string) $event->description,
            'public' => $event->zone === Zone::Public,
            'sessionId' => (string) $event->play_session_id,
            'editing' => true,
        ]);
    }

    public function save(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'formKind' => ['required', Rule::in(TimelineEvent::KINDS)],
            'dateLabel' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'sessionId' => ['nullable', Rule::in($this->campaign->playSessions()->pluck('id')->map(fn ($id) => (string) $id))],
        ], attributes: ['title' => __('événement'), 'dateLabel' => __('date'), 'description' => __('description')]);

        $event = $this->editingId
            ? $this->campaign->timelineEvents()->findOrFail($this->editingId)
            : new TimelineEvent;

        $event->fill([
            'kind' => $this->formKind,
            'date_label' => trim($this->dateLabel) ?: null,
            'title' => trim($this->title),
            'description' => trim($this->description) ?: null,
            'zone' => $this->public ? Zone::Public : Zone::GameMaster,
        ]);
        $event->play_session_id = $this->sessionId === '' ? null : (int) $this->sessionId;

        if (! $event->exists) {
            $event->campaign()->associate($this->campaign);
            $event->user_id = $this->campaign->user_id;
            $event->position = (int) $this->campaign->timelineEvents()->max('position') + 1;

            // Pendant une séance, un événement joué est rattaché à la scène en cours.
            $session = $this->campaign->openSession();
            if ($this->formKind === 'played' && $session && $event->play_session_id === $session->id) {
                $event->scene_id = $session->current_scene_id;
            }
        }

        $event->save();
        $this->editing = false;
        unset($this->events);
    }

    public function cancel(): void
    {
        $this->editing = false;
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $this->authorize('update', $this->campaign);

        $this->campaign->timelineEvents()->findOrFail($id)->delete();
        unset($this->events);
    }

    /** Monte ou descend un événement d'un cran dans la chronologie entière (pas seulement le filtre). */
    public function move(int $id, int $direction): void
    {
        $this->authorize('update', $this->campaign);

        $all = $this->campaign->timelineEvents()->ordered()->get(['id', 'position']);
        $index = $all->search(fn (TimelineEvent $event) => $event->id === $id);
        abort_if($index === false, 404);

        $swap = $all->get($index + ($direction < 0 ? -1 : 1));

        if ($swap === null) {
            return;
        }

        $event = $all->get($index);
        TimelineEvent::whereKey($event->id)->update(['position' => $swap->position]);
        TimelineEvent::whereKey($swap->id)->update(['position' => $event->position]);
        unset($this->events);
    }

    public function render()
    {
        return view('livewire.timeline.index', [
            'kinds' => TimelineEvent::kinds(),
            'sessions' => $this->isGameMaster ? $this->campaign->playSessions()->orderByDesc('number')->get(['id', 'number', 'campaign_id']) : collect(),
        ])->title(__('Chronologie · :name', ['name' => $this->campaign->name]));
    }
}
