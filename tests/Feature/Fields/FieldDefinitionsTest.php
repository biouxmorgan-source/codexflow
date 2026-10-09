<?php

namespace Tests\Feature\Fields;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Entities\Form;
use App\Livewire\Entities\Show;
use App\Livewire\Fields\Manage;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FieldDefinitionsTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Entity $npc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->npc = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Shen Chu']);
    }

    public function test_gm_names_fields_for_a_game(): void
    {
        Livewire::actingAs($this->gm)
            ->test(Manage::class, ['campaign' => $this->campaign])
            ->set('name', 'Dé de vie')
            ->set('group', 'Caractéristiques')
            ->set('type', 'select')
            ->set('options', "d4\nd6\n d8 \nd6")
            ->set('entityTypeIds', [(string) EntityType::standard('character')->id])
            ->call('save')
            ->assertHasNoErrors()
            ->set('name', 'Dé de vie')
            ->set('type', 'text')
            ->set('entityTypeIds', [(string) EntityType::standard('character')->id])
            ->call('save')
            ->assertHasErrors('name')
            ->assertSee(['Caractéristiques', 'Dé de vie', 'd4, d6, d8']);

        $definition = FieldDefinition::sole();
        $this->assertSame($this->campaign->game_system_id, $definition->game_system_id);
        $this->assertSame(FieldType::Select, $definition->type);
        $this->assertSame(['d4', 'd6', 'd8'], $definition->options);
    }

    public function test_only_the_game_owner_manages_its_fields(): void
    {
        $coGm = User::factory()->create();
        $this->campaign->members()->attach($coGm, ['role' => CampaignRole::GameMaster->value]);

        $this->actingAs($coGm)->get(route('fields.index', $this->campaign))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('fields.index', $this->campaign))->assertForbidden();
        $this->actingAs($this->gm)->get(route('fields.index', $this->campaign))->assertOk();
    }

    public function test_values_are_typed_validated_and_shown_by_zone(): void
    {
        $strength = $this->field('Force', FieldType::Number, Zone::Public, 'Caractéristiques');
        $stealth = $this->field('Discrétion', FieldType::Boolean, Zone::Public, 'Compétences');
        $secret = $this->field('Pouvoir caché', FieldType::LongText, Zone::GameMaster);
        $placeOnly = $this->field('Population', FieldType::Number, Zone::Public, entityTypeId: EntityType::standard('place')->id);

        $form = Livewire::actingAs($this->gm)
            ->test(Form::class, ['campaign' => $this->campaign, 'entity' => $this->npc])
            ->assertSee(['Force', 'Discrétion', 'Pouvoir caché'])
            ->assertDontSee('Population')
            ->set("fields.{$strength->id}", 'beaucoup')
            ->call('save')
            ->assertHasErrors("fields.{$strength->id}");

        $form->set("fields.{$strength->id}", '3,5')
            ->set("fields.{$stealth->id}", true)
            ->set("fields.{$secret->id}", 'Parle aux morts.')
            ->call('save')
            ->assertHasNoErrors();

        $npc = $this->npc->fresh();
        $this->assertSame(3.5, $npc->fieldValue($strength));
        $this->assertTrue($npc->fieldValue($stealth));
        $this->assertNull($npc->fieldValue($placeOnly));
        $this->assertArrayNotHasKey('field_values', $npc->toArray());

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $npc])
            ->assertSeeInOrder(['Zone publique', 'Caractéristiques', 'Force', '3,5', 'Compétences', 'Discrétion', 'Oui', 'Zone MJ', 'Pouvoir caché', 'Parle aux morts.']);
    }

    public function test_deleting_a_field_removes_its_values(): void
    {
        $kept = $this->field('Force', FieldType::Number);
        $removed = $this->field('Agilité', FieldType::Number);
        $this->npc->setFieldValues([$kept->id => 2, $removed->id => 4]);
        $this->npc->save();

        Livewire::actingAs($this->gm)
            ->test(Manage::class, ['campaign' => $this->campaign])
            ->call('delete', $removed->id);

        $this->assertModelMissing($removed);
        $this->assertSame([(string) $kept->id => 2], $this->npc->fresh()->field_values);
    }

    public function test_fields_can_be_reordered(): void
    {
        $first = $this->field('A', FieldType::Text);
        $second = $this->field('B', FieldType::Text);

        Livewire::actingAs($this->gm)
            ->test(Manage::class, ['campaign' => $this->campaign])
            ->call('move', $second->id, -1)
            ->assertSeeInOrder(['B', 'A']);

        $this->assertLessThan($first->fresh()->position, $second->fresh()->position);
    }

    private function field(string $name, FieldType $type, Zone $zone = Zone::Public, ?string $group = null, ?int $entityTypeId = null): FieldDefinition
    {
        return $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => $name,
            'type' => $type,
            'zone' => $zone,
            'group' => $group,
            'entity_type_id' => $entityTypeId,
            'position' => FieldDefinition::count(),
        ]);
    }
}
