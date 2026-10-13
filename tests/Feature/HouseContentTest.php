<?php

namespace Tests\Feature;

use App\Enums\RuleOrigin;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\Rules\Form as RuleForm;
use App\Livewire\Rules\Index as RuleIndex;
use App\Livewire\Rules\Show as RuleShow;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Rule;
use App\Models\User;
use App\Models\World;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Contenu maison (v0.60.0) : coche sur les fiches, version officielle remplacée, en zone MJ. */
class HouseContentTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
    }

    public function test_a_sheet_is_official_by_default_and_can_be_marked_as_house_content(): void
    {
        $official = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Gobelin']);
        $this->assertFalse($official->fresh()->is_house);

        Livewire::actingAs($this->gm)
            ->test(EntityForm::class, ['campaign' => $this->campaign])
            ->set('entityTypeId', (string) EntityType::standard('character')->id)
            ->set('name', 'Boule de feu adoucie')
            ->set('scope', 'campaign')
            ->set('isHouse', true)
            ->set('officialVersion', 'Boule de feu : 8d6 dégâts.')
            ->call('save')
            ->assertHasNoErrors();

        $house = Entity::where('name', 'Boule de feu adoucie')->sole();
        $this->assertTrue($house->is_house);
        $this->assertSame('Boule de feu : 8d6 dégâts.', $house->official_version);

        // La zone MJ ne sort jamais par la sérialisation.
        $this->assertArrayNotHasKey('official_version', $house->toArray());
        $this->assertArrayNotHasKey('is_house', $house->toArray());

        Livewire::actingAs($this->gm)->test(EntityShow::class, ['campaign' => $this->campaign, 'entity' => $house])
            ->assertSee('Maison')
            ->assertSee('Version officielle remplacée')
            ->assertSee('8d6 dégâts');

        Livewire::actingAs($this->gm)->test(CampaignShow::class, ['campaign' => $this->campaign])
            ->set('origin', 'maison')
            ->assertSee('Boule de feu adoucie')
            ->assertDontSee('Gobelin')
            ->set('origin', 'officiel')
            ->assertSee('Gobelin')
            ->assertDontSee('Boule de feu adoucie');

        // Décocher : plus rien n'est remplacé.
        Livewire::actingAs($this->gm)
            ->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $house])
            ->assertSet('isHouse', true)
            ->set('isHouse', false)
            ->call('save')
            ->assertHasNoErrors();

        $house->refresh();
        $this->assertFalse($house->is_house);
        $this->assertNull($house->official_version);
    }

    public function test_a_house_rule_keeps_the_official_version_it_replaces(): void
    {
        Livewire::actingAs($this->gm)
            ->test(RuleForm::class, ['campaign' => $this->campaign])
            ->set('title', 'Initiative au D10')
            ->set('origin', 'house')
            ->set('procedure', 'Chacun lance 1d10 + Dextérité.')
            ->set('officialVersion', 'Initiative : 1d20 + modificateur de Dextérité.')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($this->gm)
            ->test(RuleForm::class, ['campaign' => $this->campaign])
            ->set('title', 'Jet de sauvegarde')
            ->set('officialVersion', 'Ignorée : la règle est officielle.')
            ->call('save')
            ->assertHasNoErrors();

        $house = Rule::where('title', 'Initiative au D10')->sole();
        $this->assertSame(RuleOrigin::House, $house->origin);
        $this->assertSame('Initiative : 1d20 + modificateur de Dextérité.', $house->official_version);
        $this->assertArrayNotHasKey('official_version', $house->toArray());
        $this->assertNull(Rule::where('title', 'Jet de sauvegarde')->sole()->official_version);

        Livewire::actingAs($this->gm)->test(RuleShow::class, ['campaign' => $this->campaign, 'rule' => $house])
            ->assertSee('Version officielle remplacée')
            ->assertSee('1d20 + modificateur');

        Livewire::actingAs($this->gm)->test(RuleIndex::class, ['campaign' => $this->campaign])
            ->set('origin', 'house')
            ->assertSee('Initiative au D10')
            ->assertDontSee('Jet de sauvegarde');
    }

    public function test_the_campaign_archive_keeps_house_content(): void
    {
        $other = User::factory()->create();
        Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'name' => 'Sort maison', 'is_house' => true, 'official_version' => 'Version du livre.',
        ]);
        $rule = new Rule(['title' => 'Initiative au D10', 'origin' => 'house', 'official_version' => 'Au D20.']);
        $rule->owner()->associate($this->gm);
        $rule->campaign_id = $this->campaign->id;
        $rule->save();

        $copy = (new CampaignImport($other))->handle((new CampaignExport($this->campaign->fresh()))->write());

        $spell = $copy->availableEntities()->where('name', 'Sort maison')->sole();
        $this->assertTrue($spell->is_house);
        $this->assertSame('Version du livre.', $spell->official_version);
        $this->assertSame('Au D20.', $copy->availableRules()->where('title', 'Initiative au D10')->sole()->official_version);
    }

    public function test_the_request_is_recorded_as_a_done_evolution(): void
    {
        $this->assertSame('done', DB::table('evolutions')->where('title', 'Contenu maison et version officielle')->value('status'));
    }
}
