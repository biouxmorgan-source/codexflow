<?php

namespace Tests\Feature\Entities;

use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Entities\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\FieldDefinition;
use App\Models\User;
use App\Models\World;
use App\Support\Search\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignOverridesTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaignA;

    private Campaign $campaignB;

    private Entity $npc;

    private FieldDefinition $strength;

    private FieldDefinition $title;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaignA = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->campaignB = Campaign::factory()->for($this->gm, 'owner')->create([
            'world_id' => $world->id,
            'game_system_id' => $this->campaignA->game_system_id,
        ]);

        $game = $this->campaignA->gameSystem;
        $this->strength = $game->fieldDefinitions()->create([
            'name' => 'Force', 'type' => FieldType::Select, 'options' => ['d4', 'd6', 'd8'], 'zone' => Zone::Public, 'position' => 1,
        ]);
        $this->title = $game->fieldDefinitions()->create([
            'name' => 'Titre', 'type' => FieldType::Text, 'zone' => Zone::Public, 'position' => 2,
        ]);

        $this->npc = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Aldric']);
        $this->npc->setFieldValues([$this->strength->id => 'd6', $this->title->id => 'Tavernier']);
        $this->npc->save();
    }

    /** Parcours de recette 11 : mort dans une campagne, vivant dans l'autre, fiche mondiale inchangée. */
    public function test_a_world_npc_changes_in_one_campaign_only(): void
    {
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaignA, 'entity' => $this->npc])
            ->set('status', 'mort')
            ->call('saveState')
            ->set('overrideFieldId', (string) $this->strength->id)
            ->set('overrideValue', 'd8')
            ->call('saveOverride')
            ->assertHasNoErrors()
            ->set('overrideFieldId', (string) $this->title->id)
            ->set('overrideValue', '')
            ->call('saveOverride')
            ->assertHasNoErrors();

        $npc = $this->npc->fresh();
        $this->assertSame('d6', $npc->fieldValue($this->strength));
        $this->assertSame('Tavernier', $npc->fieldValue($this->title));
        $this->assertSame('d8', $npc->fieldValueIn($this->strength, $this->campaignA));
        $this->assertNull($npc->fresh()->fieldValueIn($this->title, $this->campaignA));
        $this->assertSame('d6', $npc->fresh()->fieldValueIn($this->strength, $this->campaignB));

        $this->actingAs($this->gm)->get(route('entities.show', [$this->campaignA, $this->npc]))
            ->assertSeeInOrder(['Force', 'campagne', 'd8'])
            ->assertSee('Valeur du monde : d6')
            ->assertSee('Monde : Tavernier');

        $this->actingAs($this->gm)->get(route('entities.show', [$this->campaignB, $this->npc]))
            ->assertSeeInOrder(['Force', 'd6', 'Titre', 'Tavernier'])
            ->assertDontSee('>campagne</span>', false);

        $this->assertSame('mort', $npc->stateIn($this->campaignA)->status);
        $this->assertNull($npc->stateIn($this->campaignB)->status);
    }

    public function test_world_changes_reach_every_campaign_except_overridden_fields(): void
    {
        $state = $this->npc->stateIn($this->campaignA);
        $state->setOverride($this->strength, 'd8');
        $state->save();

        $this->npc->setFieldValues([$this->strength->id => 'd4', $this->title->id => 'Ancien tavernier']);
        $this->npc->save();

        $this->assertSame('d8', $this->npc->fresh()->fieldValueIn($this->strength, $this->campaignA));
        $this->assertSame('Ancien tavernier', $this->npc->fresh()->fieldValueIn($this->title, $this->campaignA));
        $this->assertSame('d4', $this->npc->fresh()->fieldValueIn($this->strength, $this->campaignB));

        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaignA, 'entity' => $this->npc])
            ->call('removeOverride', $this->strength->id);

        $this->assertSame('d4', $this->npc->fresh()->fieldValueIn($this->strength, $this->campaignA));
    }

    public function test_invalid_override_is_refused(): void
    {
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaignA, 'entity' => $this->npc])
            ->set('overrideFieldId', (string) $this->strength->id)
            ->set('overrideValue', 'd20')
            ->call('saveOverride')
            ->assertHasErrors('overrideValue');

        $this->assertFalse($this->npc->fresh()->overridesFieldIn($this->strength, $this->campaignA));
    }

    public function test_deleting_a_field_also_removes_its_overrides(): void
    {
        $state = $this->npc->stateIn($this->campaignA);
        $state->setOverride($this->strength, 'd8');
        $state->setOverride($this->title, 'Traître');
        $state->save();

        $this->strength->delete();

        $this->assertSame([(string) $this->title->id => 'Traître'], $state->fresh()->fieldOverrides());
    }

    public function test_search_finds_the_campaign_value_only_in_its_campaign(): void
    {
        $state = $this->npc->stateIn($this->campaignA);
        $state->setOverride($this->title, 'Traître démasqué');
        $state->save();

        $this->assertNotEmpty((new GlobalSearch($this->campaignA, $this->gm, 'demasque'))->run(['entities']));
        $this->assertSame([], (new GlobalSearch($this->campaignB, $this->gm, 'demasque'))->run(['entities']));
    }

    public function test_campaign_entities_have_no_overrides(): void
    {
        $local = Entity::factory()->for($this->gm, 'owner')->for($this->campaignA)->create(['name' => 'Local']);

        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaignA, 'entity' => $local])
            ->assertDontSee('Champs dans cette campagne')
            ->set('overrideFieldId', (string) $this->strength->id)
            ->set('overrideValue', 'd8')
            ->call('saveOverride')
            ->assertNotFound();
    }
}
