<?php

namespace Tests\Feature\Entities;

use App\Enums\Zone;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Entities\Form;
use App\Livewire\Entities\Show;
use App\Livewire\EntityTypes\Manage as EntityTypesManage;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\Tag;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RelationsTagsTypesTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Entity $aldric;

    private Entity $guild;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->aldric = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Aldric']);
        $this->guild = Entity::factory()->for($this->gm, 'owner')->for($world)->create([
            'name' => 'La Guilde',
            'entity_type_id' => EntityType::standard('organization')->id,
        ]);
    }

    public function test_gm_adds_a_custom_entity_type_and_uses_it(): void
    {
        Livewire::actingAs($this->gm)
            ->test(EntityTypesManage::class)
            ->set('name', 'Ordre noir')
            ->call('save')
            ->assertHasNoErrors()
            ->set('name', 'personnage')
            ->call('save')
            ->assertHasErrors('name');

        $faction = EntityType::where('name', 'Ordre noir')->sole();
        $this->assertSame($this->gm->id, $faction->user_id);

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign])
            ->assertSee('Ordre noir')
            ->set('name', 'Les Cendres')
            ->set('entityTypeId', (string) $faction->id)
            ->call('save')
            ->assertHasNoErrors();

        // Le type d'un autre compte n'est ni visible ni utilisable.
        Livewire::actingAs(User::factory()->create())->test(EntityTypesManage::class)->assertDontSee('Ordre noir');

        Livewire::actingAs($this->gm)
            ->test(EntityTypesManage::class)
            ->call('delete', $faction->id)
            ->assertHasErrors('delete');

        $this->assertModelExists($faction);
    }

    public function test_standard_and_foreign_types_cannot_be_changed(): void
    {
        $other = User::factory()->create();
        $foreign = new EntityType(['name' => 'Secret']);
        $foreign->user_id = $other->id;
        $foreign->save();

        foreach ([EntityType::standard('place')->id, $foreign->id] as $id) {
            Livewire::actingAs($this->gm)->test(EntityTypesManage::class)->call('delete', $id)->assertNotFound();
        }

        $this->assertModelExists($foreign);
    }

    public function test_tags_are_saved_reused_and_filter_the_campaign(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'entity' => $this->aldric])
            ->set('tags', 'Taverne, intrigue,  taverne , ')
            ->call('save');

        Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'entity' => $this->guild])
            ->assertSee(['intrigue', 'Taverne'])
            ->set('tags', 'TAVERNE')
            ->call('save');

        $this->assertSame(['intrigue', 'Taverne'], Tag::orderByRaw('lower(name)')->pluck('name')->all());
        $tavern = Tag::where('name', 'Taverne')->sole();

        Livewire::actingAs($this->gm)
            ->test(CampaignShow::class, ['campaign' => $this->campaign])
            ->set('tag', (string) Tag::where('name', 'intrigue')->value('id'))
            ->assertSee('Aldric')
            ->assertDontSee('La Guilde')
            ->set('tag', (string) $tavern->id)
            ->assertSee(['Aldric', 'La Guilde']);
    }

    public function test_relations_show_on_both_sheets_with_their_reverse_label(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->aldric])
            ->set('relationLabel', 'travaille pour')
            ->set('relationReverse', 'emploie')
            ->set('relationTargetId', $this->guild->id)
            ->call('addRelation')
            ->assertHasNoErrors()
            ->assertSeeInOrder(['Zone publique', 'travaille pour', 'La Guilde', 'Zone MJ']);

        $relation = EntityRelation::sole();
        $this->assertNull($relation->campaign_id, 'Entre deux fiches du monde, la relation vaut pour tout le monde.');

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->guild])
            ->assertSeeInOrder(['emploie', 'Aldric']);

        $other = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->campaign->world_id]);
        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $other, 'entity' => $this->guild])
            ->assertSee('emploie');
    }

    public function test_campaign_only_and_secret_relations_stay_where_they_belong(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->aldric])
            ->set('relationLabel', 'espionne')
            ->set('relationTargetId', $this->guild->id)
            ->set('relationZone', 'gm')
            ->set('relationCampaignOnly', true)
            ->call('addRelation')
            ->assertSeeInOrder(['Zone MJ', 'espionne', 'La Guilde']);

        $relation = EntityRelation::sole();
        $this->assertSame($this->campaign->id, $relation->campaign_id);
        $this->assertSame(Zone::GameMaster, $relation->zone);

        $other = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->campaign->world_id]);
        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $other, 'entity' => $this->aldric])
            ->assertDontSeeHtml('wire:key="relation-');
    }

    public function test_relations_refuse_self_and_entities_outside_the_campaign(): void
    {
        $elsewhere = Campaign::factory()->for($this->gm, 'owner')->create();
        $outsider = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $elsewhere->id, 'world_id' => null]);

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->aldric])
            ->set('relationLabel', 'connaît')
            ->set('relationTargetId', $this->aldric->id)
            ->call('addRelation')
            ->assertHasErrors('relationTargetId')
            ->set('relationTargetId', $outsider->id)
            ->call('addRelation')
            ->assertHasErrors('relationTargetId');

        $this->assertSame(0, EntityRelation::count());
    }

    public function test_relations_are_deleted_with_the_entity_or_on_demand(): void
    {
        $relation = new EntityRelation(['label' => 'connaît', 'zone' => Zone::Public]);
        $relation->owner()->associate($this->gm);
        $relation->from()->associate($this->aldric);
        $relation->to()->associate($this->guild);
        $relation->save();

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->guild])
            ->call('deleteRelation', $relation->id);

        $this->assertModelMissing($relation);

        $relation = $relation->replicate();
        $relation->save();
        $this->guild->delete();

        $this->assertModelMissing($relation);
    }
}
