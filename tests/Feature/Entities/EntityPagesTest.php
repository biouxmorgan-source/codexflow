<?php

namespace Tests\Feature\Entities;

use App\Enums\CampaignRole;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Entities\Form;
use App\Livewire\Entities\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EntityPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private World $world;

    private Campaign $campaignA;

    private Campaign $campaignB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaignA = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->world->id]);
        $this->campaignB = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->world->id]);
    }

    public function test_gm_creates_an_inn_and_three_npcs_in_the_world_and_both_campaigns_see_them(): void
    {
        // Parcours de recette n° 2.
        $place = EntityType::standard('place');
        $character = EntityType::standard('character');

        $this->createEntity($this->campaignA, 'Le Poney fringant', $place, 'world');
        foreach (['Aldric', 'Mira', 'Anselme'] as $name) {
            $this->createEntity($this->campaignA, $name, $character, 'world');
        }

        $this->assertSame(4, Entity::count());
        $this->assertSame(4, $this->world->entities()->count());

        Livewire::actingAs($this->gm)
            ->test(CampaignShow::class, ['campaign' => $this->campaignB])
            ->assertSee(['Le Poney fringant', 'Aldric', 'Mira', 'Anselme']);
    }

    public function test_editing_a_sheet_returns_to_the_page_it_was_opened_from(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaignA)->create(['name' => 'Aldric']);
        $characters = route('characters.index', $this->campaignA, false);

        // Depuis la page Personnages : on y revient, après l'enregistrement comme après l'annulation.
        Livewire::actingAs($this->gm)->withQueryParams(['retour' => $characters])
            ->test(Form::class, ['campaign' => $this->campaignA, 'entity' => $npc])
            ->assertSeeHtml('href="'.$characters.'"')
            ->set('name', 'Aldric le Vieux')
            ->call('save')
            ->assertRedirect($characters);

        // Une adresse vers un autre site est ignorée.
        Livewire::actingAs($this->gm)->withQueryParams(['retour' => '//exemple.com/piege'])
            ->test(Form::class, ['campaign' => $this->campaignA, 'entity' => $npc])
            ->call('save')
            ->assertRedirect(route('entities.show', [$this->campaignA, $npc]));
    }

    public function test_campaign_only_entities_stay_in_their_campaign(): void
    {
        $this->createEntity($this->campaignA, 'Secret de A', EntityType::standard('item'), 'campaign');

        Livewire::actingAs($this->gm)
            ->test(CampaignShow::class, ['campaign' => $this->campaignB])
            ->assertDontSee('Secret de A');

        $this->assertSame($this->campaignA->id, Entity::sole()->campaign_id);
    }

    public function test_campaign_list_can_be_filtered_by_name_and_type(): void
    {
        $place = EntityType::standard('place');
        Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Taverne du Sanglier', 'entity_type_id' => $place->id]);
        Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Aldric']);

        Livewire::actingAs($this->gm)
            ->test(CampaignShow::class, ['campaign' => $this->campaignA])
            ->set('search', 'sanglier')
            ->assertSee('Taverne du Sanglier')
            ->assertDontSee('Aldric')
            ->set('search', '')
            ->set('type', (string) $place->id)
            ->assertDontSee('Aldric');
    }

    public function test_editing_a_world_entity_updates_it_everywhere(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Aldric']);

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaignA, 'entity' => $npc])
            ->assertSet('name', 'Aldric')
            ->set('name', 'Aldric le Borgne')
            ->set('gmNotes', 'Espion de la guilde.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('entities.show', [$this->campaignA, $npc]));

        $this->assertSame('Aldric le Borgne', $npc->fresh()->name);
        $this->assertSame('Espion de la guilde.', $npc->fresh()->gm_notes);
    }

    public function test_marking_an_npc_dead_in_one_campaign_keeps_it_alive_in_the_other(): void
    {
        // Parcours de recette n° 11.
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Aldric']);

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaignA, 'entity' => $npc])
            ->set('status', 'mort')
            ->set('stateNotes', 'Tombé au combat.')
            ->call('saveState')
            ->assertHasNoErrors();

        $this->assertSame('mort', $npc->stateIn($this->campaignA)->status);
        $this->assertNull($npc->stateIn($this->campaignB)->status);
        $this->assertSame('Aldric', $npc->fresh()->name);

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaignB, 'entity' => $npc])
            ->assertSet('status', '');
    }

    public function test_saving_an_empty_state_does_not_create_a_row(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create();

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaignA, 'entity' => $npc])
            ->call('saveState');

        $this->assertSame(0, $npc->campaignStates()->count());
    }

    public function test_entity_from_another_campaign_is_not_reachable_through_this_campaign(): void
    {
        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        $foreign = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $other->id, 'world_id' => null]);

        $this->actingAs($this->gm)
            ->get(route('entities.show', [$this->campaignA, $foreign]))
            ->assertNotFound();
    }

    public function test_players_and_strangers_cannot_open_gm_pages(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['gm_notes' => 'Secret absolu']);
        $player = User::factory()->create();
        $this->campaignA->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $stranger = User::factory()->create();

        foreach ([$player, $stranger] as $user) {
            // Le joueur sans personnage est renvoyé vers ses campagnes ; l'étranger est refusé.
            $campaignPage = $this->actingAs($user)->get(route('campaigns.show', $this->campaignA));
            $user->is($player) ? $campaignPage->assertRedirect(route('campaigns.index'))->assertDontSee('Secret absolu') : $campaignPage->assertForbidden();
            $this->actingAs($user)->get(route('entities.show', [$this->campaignA, $npc]))->assertForbidden();
            $this->actingAs($user)->get(route('entities.edit', [$this->campaignA, $npc]))->assertForbidden();
        }
    }

    public function test_deleting_an_entity(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create();

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaignA, 'entity' => $npc])
            ->call('delete')
            ->assertRedirect(route('campaigns.show', $this->campaignA));

        $this->assertModelMissing($npc);
    }

    public function test_world_scope_is_refused_when_the_campaign_has_no_world(): void
    {
        $solo = Campaign::factory()->for($this->gm, 'owner')->create();

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $solo])
            ->assertSet('scope', 'campaign')
            ->set('name', 'Intrus')
            ->set('scope', 'world')
            ->call('save')
            ->assertHasErrors('scope');
    }

    public function test_pages_render(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Aldric', 'gm_notes' => 'Espion']);

        $this->actingAs($this->gm);
        $this->get(route('campaigns.show', $this->campaignA))->assertOk()->assertSee('Aldric');
        $this->get(route('entities.create', $this->campaignA))->assertOk()->assertSee('Zone MJ');
        $this->get(route('entities.show', [$this->campaignA, $npc]))->assertOk()->assertSee(['Zone publique', 'Espion']);
        $this->get(route('entities.edit', [$this->campaignA, $npc]))->assertOk()->assertSee('Modifier Aldric');
    }

    private function createEntity(Campaign $campaign, string $name, EntityType $type, string $scope): void
    {
        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $campaign])
            ->set('name', $name)
            ->set('entityTypeId', (string) $type->id)
            ->set('scope', $scope)
            ->set('summary', 'Résumé de '.$name)
            ->call('save')
            ->assertHasNoErrors();
    }
}
