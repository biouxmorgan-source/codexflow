<?php

namespace Tests\Feature\Ai;

use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Livewire\Ai\Index;
use App\Models\AiSuggestion;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\PlayerCharacter;
use App\Models\PlaySession;
use App\Models\SessionNote;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\World;
use App\Support\Ai\SessionPrompt;
use App\Support\Ai\SuggestionParser;
use App\Support\Ai\UnreadableResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private Entity $morel;

    private Entity $manor;

    private PlayerCharacter $harvey;

    private PlaySession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);

        $this->morel = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Morel', 'summary' => 'Antiquaire', 'gm_notes' => 'Membre du Culte', 'world_id' => $world->id, 'campaign_id' => null]);
        $this->manor = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Manoir Corbitt', 'world_id' => $world->id, 'campaign_id' => null]);
        $harveySheet = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Harvey', 'campaign_id' => $this->campaign->id, 'world_id' => null]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $harveySheet->id, 'user_id' => $this->alex->id]);

        $this->session = $this->campaign->playSessions()->create(['number' => 2, 'title' => 'Le manoir', 'started_at' => now()]);
        $note = new SessionNote(['body' => "Harvey fouille le [[Manoir Corbitt|{$this->manor->id}]] et y croise Morel."]);
        $note->playSession()->associate($this->session);
        $note->user_id = $this->gm->id;
        $note->save();
    }

    private function playerNote(string $body, string $visibility): void
    {
        $note = $this->harvey->notes()->make(['body' => $body, 'visibility' => $visibility]);
        $note->user_id = $this->alex->id;
        $note->play_session_id = $this->session->id;
        $note->save();
    }

    private function response(): string
    {
        $morel = $this->morel->id;
        $manor = $this->manor->id;
        $harvey = $this->harvey->id;

        return <<<TXT
        Voici mes propositions :
        ```json
        {
          "summary": "Harvey explore le manoir et y surprend Morel.",
          "events": [{"title": "Harvey surprend Morel au manoir", "description": "Dans la bibliothèque.", "public": true}],
          "relations": [{"from": $morel, "to": $manor, "label": "fréquente en secret", "reverse_label": "reçoit en secret", "public": false}],
          "statuses": [{"entity": "#$morel", "status": "démasqué"}],
          "notes": [{"entity": $morel, "text": "Sait que Harvey l'a vu."}],
          "reveals": [{"entity": $manor, "characters": [$harvey]}, {"entity": 999999, "characters": [$harvey]}]
        }
        ```
        TXT;
    }

    /** Parcours : le MJ copie le texte, colle la réponse de son IA, puis décide de chaque proposition. */
    public function test_the_game_master_turns_an_ai_answer_into_accepted_changes(): void
    {
        $this->playerNote('Morel cache quelque chose.', 'gm');
        $this->playerNote('Mon journal intime.', 'private');

        $page = Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertSet('sessionId', (string) $this->session->id);

        // Le texte à coller : les fiches avec leur identifiant, le personnage, les notes lisibles par le MJ.
        $prompt = $page->get('prompt');
        $this->assertStringContainsString("#{$this->morel->id} · ", $prompt);
        $this->assertStringContainsString('Manoir Corbitt (#'.$this->manor->id.')', $prompt);
        $this->assertStringContainsString('Morel cache quelque chose.', $prompt);
        $this->assertStringNotContainsString('Mon journal intime.', $prompt);
        $this->assertStringContainsString('"reveals"', $prompt);

        $page->set('response', $this->response())->call('analyse')->assertHasNoErrors();

        // La fiche inconnue est écartée ; rien n'a encore changé dans la campagne.
        $this->assertSame(['summary', 'event', 'relation', 'status', 'note', 'reveal'], AiSuggestion::orderBy('id')->pluck('kind')->all());
        $this->assertSame(0, TimelineEvent::count());
        $this->assertSame(0, EntityRelation::count());
        $this->assertSame(0, $this->harvey->grants()->count());

        $id = fn (string $kind) => AiSuggestion::where('kind', $kind)->value('id');

        // Le MJ corrige l'événement avant de l'accepter, rejette la note, accepte le reste.
        $page->set("drafts.{$id('event')}.title", 'Harvey surprend Morel dans la bibliothèque')
            ->call('accept', $id('event'))
            ->call('reject', $id('note'))
            ->call('acceptAll', AiSuggestion::first()->ai_analysis_id)
            ->assertHasNoErrors();

        $events = TimelineEvent::orderBy('position')->get();
        $this->assertSame(['Harvey surprend Morel dans la bibliothèque', 'Résumé · Session 2 · Le manoir'], $events->pluck('title')->all());
        $this->assertSame([Zone::Public, Zone::GameMaster], $events->pluck('zone')->all());
        $this->assertSame([$this->session->id, $this->session->id], $events->pluck('play_session_id')->all());

        // La relation ne vaut que pour la campagne, l'état de la fiche aussi : le monde n'est pas touché.
        $relation = EntityRelation::sole();
        $this->assertSame($this->campaign->id, $relation->campaign_id);
        $this->assertSame(Zone::GameMaster, $relation->zone);
        $this->assertSame('reçoit en secret', $relation->reverse_label);
        $this->assertSame('démasqué', $this->morel->stateIn($this->campaign)->status);
        $this->assertNull($this->morel->stateIn($this->campaign)->gm_notes);
        $this->assertSame('Membre du Culte', $this->morel->fresh()->gm_notes);

        $this->assertTrue($this->harvey->grants()->where('entity_id', $this->manor->id)->exists());
        $this->assertSame(['accepted' => 5, 'rejected' => 1], AiSuggestion::pluck('status')->countBy()->sortKeys()->all());
    }

    public function test_players_never_reach_the_assistant(): void
    {
        $this->actingAs($this->alex)->get(route('ai.index', $this->campaign))->assertForbidden();
        $this->actingAs($this->gm)->get(route('ai.index', $this->campaign))->assertOk()->assertSee('Assistant IA');
    }

    public function test_an_unreadable_answer_is_explained_and_nothing_is_saved(): void
    {
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('response', 'Désolé, je ne peux pas faire ça.')
            ->call('analyse')
            ->assertHasErrors('response')
            ->set('response', '{"summary": "", "events": []}')
            ->call('analyse')
            ->assertHasErrors('response');

        $this->assertSame(0, AiSuggestion::count());
    }

    public function test_the_parser_only_keeps_what_belongs_to_the_campaign(): void
    {
        $elsewhere = Entity::factory()->create();
        $parser = new SuggestionParser($this->campaign);

        $suggestions = $parser->parse(json_encode([
            'relations' => [
                ['from' => $this->morel->id, 'to' => $elsewhere->id, 'label' => 'connaît'],
                ['from' => $this->morel->id, 'to' => $this->morel->id, 'label' => 'lui-même'],
                ['from' => $this->morel->id, 'to' => $this->manor->id, 'label' => ''],
            ],
            'statuses' => [['entity' => $this->morel->id, 'status' => str_repeat('a', 200)]],
            'reveals' => [['entity' => $this->manor->id, 'characters' => [$this->harvey->id, 424242]]],
            'events' => [['title' => '<b>Fuite</b>']],
            'notes' => 'pas une liste',
        ]));

        $this->assertSame(['event', 'status', 'reveal'], array_column($suggestions, 'kind'));
        $this->assertSame(['title' => 'Fuite', 'description' => '', 'public' => false], $suggestions[0]['payload']);
        $this->assertSame(61, mb_strlen($suggestions[1]['payload']['status']));
        $this->assertSame([$this->harvey->id], $suggestions[2]['payload']['characters']);
        $this->assertSame(4, $parser->ignored);

        $this->expectException(UnreadableResponse::class);
        $parser->parse('[1, 2, 3]');
    }

    public function test_an_edited_suggestion_is_checked_again_before_it_is_applied(): void
    {
        $page = Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('response', json_encode(['statuses' => [['entity' => $this->morel->id, 'status' => 'en fuite']]]))
            ->call('analyse');

        $id = AiSuggestion::sole()->id;

        $page->set("drafts.$id.status", '   ')->call('accept', $id)->assertHasErrors("drafts.$id");
        $this->assertNull($this->morel->stateIn($this->campaign)->status);

        // Une fiche substituée en douce par le navigateur reste interdite.
        $page->set("drafts.$id.status", 'en fuite')->set("drafts.$id.entity", Entity::factory()->create()->id)
            ->call('accept', $id)->assertHasErrors("drafts.$id");

        $page->set("drafts.$id.entity", $this->morel->id)->call('accept', $id)->assertHasNoErrors();
        $this->assertSame('en fuite', $this->morel->stateIn($this->campaign)->status);

        // Une proposition traitée ne s'applique pas deux fois.
        $page->call('accept', $id)->assertNotFound();
    }

    public function test_the_prompt_can_be_built_without_a_session(): void
    {
        $prompt = (new SessionPrompt($this->campaign, $this->gm, null, 'Les PJ ont brûlé la grange.'))->build();

        $this->assertStringContainsString('Les PJ ont brûlé la grange.', $prompt);
        $this->assertStringNotContainsString('Harvey fouille', $prompt);
        $this->assertStringContainsString(__('personnage :id', ['id' => $this->harvey->id]), $prompt);
    }
}
