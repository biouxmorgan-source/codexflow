<?php

namespace Tests\Feature\Scenes;

use App\Enums\CampaignRole;
use App\Enums\SceneStatus;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\Scenarios\Index;
use App\Livewire\Scenes\Form;
use App\Livewire\Scenes\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScenesTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Entity $inn;

    private Entity $aldric;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->inn = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Le Poney fringant']);
        $this->aldric = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Aldric']);
    }

    public function test_gm_builds_a_scenario_with_ordered_scenes(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->set('name', 'L\'incendie')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('L\'incendie');

        $scenario = Scenario::sole();

        foreach (['Accueil', 'Le feu'] as $name) {
            Livewire::actingAs($this->gm)
                ->test(Form::class, ['campaign' => $this->campaign])
                ->assertSet('scenarioId', (string) $scenario->id)
                ->set('name', $name)
                ->set('chapter', 'Acte I')
                ->call('save')
                ->assertHasNoErrors();
        }

        $fire = Scene::where('name', 'Le feu')->sole();

        Livewire::actingAs($this->gm)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->assertSeeInOrder(['Acte I', 'Accueil', 'Le feu'])
            ->call('moveScene', $fire->id, -1)
            ->assertSeeInOrder(['Le feu', 'Accueil'])
            ->call('setStatus', $fire->id, 'played');

        $this->assertSame(SceneStatus::Played, $fire->fresh()->status);
    }

    public function test_scene_links_entities_with_notes_and_shows_them(): void
    {
        $scene = $this->scene('Arrivée');

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'scene' => $scene])
            ->set('description', 'Les joueurs entrent. [[Aldric|'.$this->aldric->id.']] les accueille.')
            ->set('pickedEntityId', $this->inn->id)
            ->call('addEntity')
            ->set('pickedEntityId', $this->aldric->id)
            ->call('addEntity')
            ->set('pickedEntityId', $this->inn->id)
            ->call('addEntity')
            ->set('linked.1.note', 'derrière le comptoir')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([$this->inn->id, $this->aldric->id], $scene->entities()->pluck('entities.id')->all());

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'scene' => $scene->fresh()])
            ->assertSeeInOrder(['Préparation', 'Les joueurs entrent.', 'Aldric', 'Dans cette scène', 'Le Poney fringant', 'Aldric', 'derrière le comptoir'])
            ->assertSeeHtml('href="'.route('entities.show', [$this->campaign, $this->aldric]).'"');

        Livewire::actingAs($this->gm)
            ->test(EntityShow::class, ['campaign' => $this->campaign, 'entity' => $this->aldric])
            ->assertSeeInOrder(['Scènes', 'Arrivée', 'Prévue']);
    }

    public function test_scenes_only_link_entities_of_the_campaign(): void
    {
        $elsewhere = Campaign::factory()->for($this->gm, 'owner')->create();
        $outsider = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $elsewhere->id, 'world_id' => null]);
        $scene = $this->scene('Arrivée');

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'scene' => $scene])
            ->set('pickedEntityId', $outsider->id)
            ->call('addEntity')
            ->assertHasErrors('pickedEntityId')
            ->set('linked', [['id' => $outsider->id, 'name' => 'x', 'type' => 'x', 'note' => '']])
            ->call('save');

        $this->assertSame(0, $scene->entities()->count());
    }

    public function test_scenes_are_reserved_to_the_campaign_game_masters(): void
    {
        $scene = $this->scene('Arrivée');
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $other = Campaign::factory()->for($this->gm, 'owner')->create();

        foreach ([$player, User::factory()->create()] as $user) {
            $this->actingAs($user)->get(route('scenes.show', [$this->campaign, $scene]))->assertForbidden();
            $this->actingAs($user)->get(route('scenarios.index', $this->campaign))->assertForbidden();
        }

        $this->actingAs($this->gm)->get(route('scenes.show', [$other, $scene]))->assertNotFound();
        $this->actingAs($this->gm)->get(route('scenes.show', [$this->campaign, $scene]))->assertOk();
    }

    public function test_deleting_a_scenario_removes_its_scenes_but_keeps_entities(): void
    {
        $scene = $this->scene('Arrivée');
        $scene->entities()->attach($this->inn);

        Livewire::actingAs($this->gm)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->call('delete', $scene->scenario_id);

        $this->assertModelMissing($scene);
        $this->assertModelExists($this->inn);
    }

    private function scene(string $name): Scene
    {
        $scenario = $this->campaign->scenarios()->create(['name' => 'Scénario']);

        return $scenario->scenes()->create(['name' => $name]);
    }
}
