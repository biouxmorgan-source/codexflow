<?php

namespace Tests\Feature\Timeline;

use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Livewire\Sessions\Live;
use App\Livewire\Timeline\Index;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
    }

    private function add(string $kind, string $date, string $title, bool $public = false, string $description = ''): void
    {
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->call('create', $kind)
            ->set('dateLabel', $date)
            ->set('title', $title)
            ->set('description', $description)
            ->set('public', $public)
            ->call('save')
            ->assertHasNoErrors();
    }

    /** Parcours : le MJ tient la chronologie, les joueurs n'en lisent que la partie visible. */
    public function test_the_game_master_keeps_a_timeline_and_players_read_only_visible_events(): void
    {
        $morel = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Morel', 'campaign_id' => $this->campaign->id, 'world_id' => null]);
        $harvey = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Harvey', 'campaign_id' => $this->campaign->id, 'world_id' => null]);
        $character = $this->campaign->playerCharacters()->create(['entity_id' => $harvey->id, 'user_id' => $this->alex->id]);

        $this->add('world', '1890', 'Fondation du Culte');
        $this->add('played', 'Jour 1', 'Arrivée à Arkham', true, "Rencontre avec [[Morel|{$morel->id}]].");
        $this->add('planned', 'Jour 3', 'Le Culte attaque');

        $this->assertSame(['gm', 'public', 'gm'], TimelineEvent::ordered()->get()->map(fn ($e) => $e->zone->value)->all());

        $this->actingAs($this->gm)->get(route('timeline.index', $this->campaign))
            ->assertOk()->assertSeeInOrder(['Fondation du Culte', 'Arrivée à Arkham', 'Le Culte attaque']);
        $this->actingAs($this->gm)->get(route('timeline.index', [$this->campaign, 'type' => 'planned']))
            ->assertSee('Le Culte attaque')->assertDontSee('Arrivée à Arkham');

        // Le joueur ne voit que l'événement visible ; Morel n'est pas un lien tant qu'il ne le connaît pas.
        $page = $this->actingAs($this->alex)->get(route('timeline.index', $this->campaign))->assertOk();
        $page->assertSee('Arrivée à Arkham')->assertSee('Morel')->assertDontSee('Fondation du Culte')->assertDontSee('Le Culte attaque');
        $page->assertDontSee(route('characters.entity', [$this->campaign, $character, $morel]));

        $character->grants()->create(['kind' => 'entity', 'entity_id' => $morel->id]);
        $this->actingAs($this->alex)->get(route('timeline.index', $this->campaign))
            ->assertSee(route('characters.entity', [$this->campaign, $character, $morel]));

        // Le joueur ne peut rien modifier.
        Livewire::actingAs($this->alex)->test(Index::class, ['campaign' => $this->campaign])->call('create', 'world')->assertForbidden();
        $event = TimelineEvent::first();
        Livewire::actingAs($this->alex)->test(Index::class, ['campaign' => $this->campaign])->call('delete', $event->id)->assertForbidden();

        $this->actingAs(User::factory()->create())->get(route('timeline.index', $this->campaign))->assertForbidden();
    }

    public function test_events_are_edited_reordered_and_deleted(): void
    {
        $this->add('world', 'An 1', 'Premier');
        $this->add('world', 'An 2', 'Deuxième');
        [$first, $second] = TimelineEvent::ordered()->get()->all();

        $component = Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->call('move', $second->id, -1);
        $this->assertSame(['Deuxième', 'Premier'], TimelineEvent::ordered()->pluck('title')->all());

        $component->call('move', $second->id, -1);
        $this->assertSame(['Deuxième', 'Premier'], TimelineEvent::ordered()->pluck('title')->all());

        $component->call('edit', $first->id)
            ->assertSet('title', 'Premier')
            ->set('title', '')
            ->call('save')
            ->assertHasErrors('title')
            ->set('title', 'Le tout premier')
            ->set('public', true)
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame(Zone::Public, $first->fresh()->zone);
        $this->assertSame('Le tout premier', $first->fresh()->title);

        $component->call('delete', $second->id);
        $this->assertSame(1, TimelineEvent::count());
    }

    public function test_a_played_event_noted_during_a_session_is_attached_to_the_current_scene(): void
    {
        $scene = $this->campaign->scenarios()->create(['name' => 'Acte I'])->scenes()->create(['name' => 'La boutique', 'position' => 1]);
        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])->call('start');
        $session = $this->campaign->openSession();
        $this->assertSame($scene->id, $session->current_scene_id);

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->call('create', 'played')
            ->assertSet('sessionId', (string) $session->id)
            ->assertSet('public', true)
            ->set('title', 'Morel s’enfuit')
            ->call('save')
            ->assertHasNoErrors();

        $event = TimelineEvent::sole();
        $this->assertSame([$session->id, $scene->id], [$event->play_session_id, $event->scene_id]);
        $this->actingAs($this->gm)->get(route('timeline.index', $this->campaign))->assertSee('La boutique');

        // Une séance d'une autre campagne est refusée.
        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        $foreign = $other->playSessions()->create(['number' => 1, 'started_at' => now()]);
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->call('create', 'played')
            ->set('title', 'Intrus')
            ->set('sessionId', (string) $foreign->id)
            ->call('save')
            ->assertHasErrors('sessionId');
    }
}
