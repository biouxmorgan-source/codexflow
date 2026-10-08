<?php

namespace App\Actions\Ai;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\Zone;
use App\Models\AiSuggestion;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Support\Ai\SuggestionParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Applique une proposition acceptée par le MJ, avec ses modifications éventuelles.
 * La proposition est revérifiée : le MJ a pu la modifier, et la campagne a pu changer depuis.
 * Les différences propres à la campagne vont dans son état, jamais dans la fiche du monde.
 */
class ApplySuggestion
{
    public function __construct(private readonly GiveToCharacters $give) {}

    /** @param  array<string, mixed>  $edited */
    public function handle(AiSuggestion $suggestion, User $gameMaster, array $edited = []): void
    {
        $analysis = $suggestion->analysis;
        $campaign = $analysis->campaign;

        Gate::forUser($gameMaster)->authorize('update', $campaign);
        abort_unless($suggestion->isPending(), 409);

        $payload = (new SuggestionParser($campaign))->normalize($suggestion->kind, array_merge($suggestion->payload, $edited));

        if ($payload === null) {
            throw ValidationException::withMessages([
                "drafts.{$suggestion->id}" => __('Cette proposition n’est plus valable : un texte est vide, ou une fiche a disparu de la campagne.'),
            ]);
        }

        foreach (['from', 'to', 'entity'] as $key) {
            if (isset($payload[$key])) {
                Gate::forUser($gameMaster)->authorize('update', Entity::findOrFail($payload[$key]));
            }
        }

        DB::transaction(function () use ($suggestion, $campaign, $payload, $analysis) {
            match ($suggestion->kind) {
                'summary' => $this->event($campaign, $analysis->play_session_id, [
                    'title' => $analysis->playSession
                        ? __('Résumé · :session', ['session' => $analysis->playSession->label()])
                        : __('Résumé de séance'),
                    'description' => $payload['text'],
                    'public' => $payload['public'],
                ]),
                'event' => $this->event($campaign, $analysis->play_session_id, $payload),
                'relation' => $this->relation($campaign, $payload),
                'status' => $this->state($campaign, $payload['entity'], fn ($state) => $state->status = $payload['status']),
                'note' => $this->state($campaign, $payload['entity'], fn ($state) => $state->gm_notes = trim($state->gm_notes."\n\n".$payload['text'])),
                'reveal' => $this->give->handle($campaign, $payload['characters'], ['kind' => 'entity', 'entity_id' => $payload['entity']]),
            };

            $suggestion->update(['payload' => $payload, 'status' => 'accepted']);
        });
    }

    /** @param  array{title: string, description: string, public: bool}  $payload */
    private function event(Campaign $campaign, ?int $sessionId, array $payload): void
    {
        $event = new TimelineEvent([
            'kind' => 'played',
            'title' => $payload['title'],
            'description' => $payload['description'] ?: null,
            'zone' => $payload['public'] ? Zone::Public : Zone::GameMaster,
        ]);
        $event->campaign()->associate($campaign);
        $event->user_id = $campaign->user_id;
        $event->play_session_id = $sessionId;
        $event->position = (int) $campaign->timelineEvents()->max('position') + 1;
        $event->save();
    }

    /** Relation propre à la campagne : l'IA ne réécrit pas le monde partagé. */
    private function relation(Campaign $campaign, array $payload): void
    {
        $exists = EntityRelation::query()->visibleIn($campaign)
            ->where('from_entity_id', $payload['from'])
            ->where('to_entity_id', $payload['to'])
            ->where('label', $payload['label'])
            ->exists();

        if ($exists) {
            return;
        }

        $relation = new EntityRelation([
            'label' => $payload['label'],
            'reverse_label' => $payload['reverse_label'] ?: null,
            'zone' => $payload['public'] ? Zone::Public : Zone::GameMaster,
        ]);
        $relation->owner()->associate($campaign->owner);
        $relation->from_entity_id = $payload['from'];
        $relation->to_entity_id = $payload['to'];
        $relation->campaign()->associate($campaign);
        $relation->save();
    }

    private function state(Campaign $campaign, int $entityId, callable $change): void
    {
        $state = Entity::findOrFail($entityId)->stateIn($campaign);
        $change($state);
        $state->save();
    }
}
