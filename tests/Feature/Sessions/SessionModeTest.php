<?php

namespace Tests\Feature\Sessions;

use App\Enums\CampaignRole;
use App\Enums\SceneStatus;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\Sessions\Live;
use App\Livewire\Sessions\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\PlaySession;
use App\Models\Scene;
use App\Models\User;
use App\Models\World;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SessionModeTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Entity $aldric;

    private Entity $mira;

    private Entity $guild;

    private Scene $arrival;

    private Scene $night;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->aldric = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Aldric', 'gm_notes' => 'Indicateur de la Guilde']);
        $this->mira = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Mira']);
        $this->guild = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'La Guilde']);

        $scenario = $this->campaign->scenarios()->create(['name' => 'Une nuit']);
        $this->arrival = $scenario->scenes()->create([
            'name' => 'Arrivée', 'position' => 1, 'status' => SceneStatus::Available,
            'description' => 'On parle de [[La Guilde|'.$this->guild->id.']].',
        ]);
        $this->night = $scenario->scenes()->create(['name' => 'La nuit', 'position' => 2]);
        $this->arrival->entities()->attach([
            $this->aldric->id => ['note' => 'derrière le comptoir', 'position' => 0],
            $this->mira->id => ['note' => null, 'position' => 1],
        ]);
    }

    /** Parcours de recette 3 (partie MJ) : la scène préparée alimente le contexte de session. */
    public function test_starting_a_session_shows_the_prepared_scene_and_its_entities(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->assertSee('Démarrer la session 1')
            ->call('start');

        // Nouveau montage : après un appel, la réponse est du JSON où les accents sont échappés.
        Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->assertSee(['Session 1', 'Maintenant', 'Arrivée'])
            ->assertSeeInOrder(['Fiches utiles', 'Aldric', 'derrière le comptoir', 'Indicateur de la Guilde', 'Mira', 'La Guilde']);

        $session = PlaySession::sole();
        $this->assertTrue($session->currentScene->is($this->arrival));
        $this->assertSame(SceneStatus::InProgress, $this->arrival->fresh()->status);
    }

    public function test_gm_moves_through_scenes_and_takes_timestamped_notes(): void
    {
        $component = Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->call('start')
            ->set('noteBody', 'Les joueurs promettent d\'aider [[Mira|'.$this->mira->id.']].')
            ->call('addNote')
            ->assertSeeHtml('href="'.route('entities.show', [$this->campaign, $this->mira]).'"')
            ->call('nextScene')
            ->assertSee('La nuit')
            ->set('noteBody', 'Bagarre à la cave')
            ->call('addNote');

        $this->assertSame(SceneStatus::Played, $this->arrival->fresh()->status);
        $this->assertSame(SceneStatus::InProgress, $this->night->fresh()->status);

        $component->call('setScene', $this->arrival->id);
        $this->assertSame(SceneStatus::Available, $this->night->fresh()->status);

        $component->call('end')->assertSee('Démarrer la session 2');

        $session = PlaySession::sole();
        $this->assertNotNull($session->ended_at);

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'playSession' => $session])
            ->assertSeeInOrder(['Arrivée', 'Les joueurs promettent', 'La nuit', 'Bagarre à la cave']);
    }

    public function test_to_play_items_follow_the_current_scene_and_can_be_marked_played(): void
    {
        $this->campaign->toPlayItems()->create(['body' => 'Un orage éclate']);
        $item = $this->campaign->toPlayItems()->make(['body' => 'Mira glisse une lettre']);
        $item->scene()->associate($this->arrival)->save();
        $later = $this->campaign->toPlayItems()->make(['body' => 'Des pas dans la cave']);
        $later->scene()->associate($this->night)->save();

        $component = Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->call('start')
            ->assertSee(['Un orage éclate', 'Mira glisse une lettre'])
            ->assertDontSee('Des pas dans la cave')
            ->call('markPlayed', $item->id)
            ->assertDontSee('Mira glisse une lettre')
            ->set('toPlayBody', 'Le tonnerre gronde')
            ->call('addToPlay')
            ->assertSee('Le tonnerre gronde');

        $this->assertNotNull($item->fresh()->done_at);
        $this->assertSame($this->arrival->id, $this->campaign->toPlayItems()->where('body', 'Le tonnerre gronde')->value('scene_id'));

        $component->call('nextScene')->assertSee('Des pas dans la cave');
    }

    public function test_pins_stay_from_one_session_to_the_next(): void
    {
        Livewire::actingAs($this->gm)
            ->test(EntityShow::class, ['campaign' => $this->campaign, 'entity' => $this->guild])
            ->call('togglePin')
            ->assertSee('Désépingler');

        Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->call('start');

        Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->assertSeeInOrder(['Épinglé', 'La Guilde'])
            ->set('pickedPinId', $this->mira->id)
            ->call('pin');

        Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->assertSeeInOrder(['Épinglé', 'La Guilde', 'Mira'])
            ->call('unpin', $this->guild->id);

        $this->assertSame([$this->mira->id], $this->campaign->pins()->pluck('entities.id')->all());
    }

    public function test_only_one_session_can_be_open_per_campaign(): void
    {
        $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);

        $this->expectException(QueryException::class);
        $this->campaign->playSessions()->create(['number' => 2, 'started_at' => now()]);
    }

    public function test_session_screens_are_reserved_to_game_masters(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $session = $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now(), 'ended_at' => now()]);
        $other = Campaign::factory()->for($this->gm, 'owner')->create();

        foreach ([$player, User::factory()->create()] as $user) {
            $this->actingAs($user)->get(route('sessions.live', $this->campaign))->assertForbidden();
            $this->actingAs($user)->get(route('sessions.show', [$this->campaign, $session]))->assertForbidden();
        }

        $this->actingAs($this->gm)->get(route('sessions.show', [$other, $session]))->assertNotFound();
        $this->actingAs($this->gm)->get(route('sessions.live', $this->campaign))->assertOk();
    }
}
