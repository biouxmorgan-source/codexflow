<?php

namespace Tests\Feature;

use App\Actions\Imports\ImportFieldDefinitions;
use App\Enums\FieldType;
use App\Livewire\Entities\Form;
use App\Livewire\Fields\Manage;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\User;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use App\Support\Import\TabularFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Backlog après la recette v0.34.0 : un champ pour plusieurs types de fiche.
 */
class BacklogV10Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
    }

    private function typeId(string $key): int
    {
        return EntityType::standard($key)->id;
    }

    public function test_a_field_can_concern_several_entity_types(): void
    {
        $component = Livewire::actingAs($this->gm)
            ->test(Manage::class, ['campaign' => $this->campaign])
            ->set('name', 'Points de vie')
            ->set('type', 'number')
            ->set('entityTypeIds', [(string) $this->typeId('creature'), (string) $this->typeId('character')])
            ->call('save')
            ->assertHasNoErrors();

        $field = FieldDefinition::sole();
        $this->assertSame(collect([$this->typeId('character'), $this->typeId('creature')])->sort()->values()->all(), $field->typeIds());
        $component->assertSee($field->typeLabel());

        // Un autre champ du même nom ne peut pas viser un type déjà couvert, ni tous les types.
        $component->set('name', 'points de vie')->set('entityTypeIds', [(string) $this->typeId('creature')])->call('save')->assertHasErrors('name');
        $component->set('entityTypeIds', [])->call('save')->assertHasErrors('name');
        $component->set('entityTypeIds', [(string) $this->typeId('place')])->call('save')->assertHasNoErrors();

        $form = fn (string $key) => Livewire::actingAs($this->gm)->test(Form::class, ['campaign' => $this->campaign])->set('entityTypeId', (string) $this->typeId($key));
        $this->assertTrue($form('character')->instance()->fieldDefinitions->contains($field));
        $this->assertTrue($form('creature')->instance()->fieldDefinitions->contains($field));
        $this->assertFalse($form('item')->instance()->fieldDefinitions->contains($field));

        // Modifier le champ retrouve ses types cochés.
        $component->call('edit', $field->id)->assertSet('entityTypeIds', array_map('strval', $field->typeIds()));
    }

    public function test_deleting_one_of_its_types_keeps_the_field_for_the_others(): void
    {
        $custom = EntityType::forceCreate(['user_id' => $this->gm->id, 'key' => null, 'name' => 'Vaisseau']);
        $field = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Coque', 'type' => FieldType::Number, 'zone' => 'public', 'position' => 1]);
        $field->assignTypes([$custom->id, $this->typeId('item')])->save();

        $custom->delete();

        $field->refresh();
        $this->assertSame([$this->typeId('item')], $field->typeIds());
        $this->assertNull($field->entity_type_ids);
    }

    public function test_several_types_travel_in_templates_and_field_files(): void
    {
        $field = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Santé mentale', 'type' => FieldType::Number, 'zone' => 'public', 'position' => 1]);
        $field->assignTypes([$this->typeId('character'), $this->typeId('creature')])->save();

        $theirs = Campaign::factory()->for($other = User::factory()->create(), 'owner')->create();
        (new CampaignImport($other))->template(json_encode(CampaignExport::template($this->campaign, false)), $theirs);
        $this->assertSame($field->typeIds(), $theirs->gameSystem->fieldDefinitions()->sole()->typeIds());

        $csv = $this->actingAs($this->gm)->get(route('exports.download', [$this->campaign, 'champs']))->streamedContent();
        $this->assertStringContainsString('Personnage|Créature', $csv);

        $game = Campaign::factory()->for($this->gm, 'owner')->create()->gameSystem;
        (new ImportFieldDefinitions($game, $this->gm, new TabularFile(['Nom', 'Type', 'Zone', 'Type de fiche'], [['line' => 2, 'cells' => ['Santé mentale', 'nombre', 'publique', 'Personnage|Créature']]])))->run();
        $this->assertSame($field->typeIds(), $game->fieldDefinitions()->sole()->typeIds());
    }

    public function test_a_shared_field_shows_on_each_sheet_of_its_types(): void
    {
        $field = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Initiative', 'type' => FieldType::Number, 'zone' => 'public', 'position' => 1]);
        $field->assignTypes([$this->typeId('character'), $this->typeId('creature')])->save();

        foreach (['character' => 12, 'creature' => 7] as $key => $value) {
            $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['entity_type_id' => $this->typeId($key), 'field_values' => [(string) $field->id => $value]]);
            $this->actingAs($this->gm)->get(route('entities.show', [$this->campaign, $entity]))->assertSee(['Initiative', (string) $value]);
        }

        $item = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['entity_type_id' => $this->typeId('item')]);
        $this->get(route('entities.show', [$this->campaign, $item]))->assertDontSee('Initiative');
    }
}
