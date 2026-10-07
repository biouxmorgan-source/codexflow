<?php

namespace Tests\Feature\Journal;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\SceneStatus;
use App\Enums\Zone;
use App\Livewire\Imports\Create;
use App\Livewire\Journal\Index;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->actingAs($this->gm);
    }

    public function test_changes_keep_who_when_and_the_old_value(): void
    {
        $strength = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Force', 'type' => FieldType::Number, 'zone' => Zone::Public]);

        $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign->world)->create([
            'entity_type_id' => EntityType::standard('character')->id,
            'name' => 'Aldric', 'summary' => 'Tavernier', 'gm_notes' => 'Espion',
            'field_values' => [(string) $strength->id => 2],
        ]);
        $entity->fill(['summary' => 'Ancien soldat', 'gm_notes' => 'Agent double'])->forceFill(['field_values' => [(string) $strength->id => 3.5]])->save();
        $entity->update(['summary' => 'Ancien soldat']); // aucun changement : aucune entrée

        $updates = ActivityLog::where('subject_type', 'entity')->where('event', 'updated')->get();
        $this->assertCount(1, $updates);
        $this->assertSame($this->gm->id, $updates[0]->user_id);
        $this->assertSame($this->campaign->world_id, $updates[0]->world_id);
        $this->assertEquals([
            'summary' => ['old' => 'Tavernier', 'new' => 'Ancien soldat'],
            'gm_notes' => ['old' => 'Espion', 'new' => 'Agent double'],
            'field_values.'.$strength->id => ['old' => 2, 'new' => 3.5],
        ], $updates[0]->diff);

        Livewire::test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(['Morgane', 'a modifié', 'Aldric', 'Résumé', 'Tavernier', 'Ancien soldat', 'Notes MJ', 'Espion', 'Force', '3,5', 'a créé']);
    }

    public function test_a_deleted_item_keeps_its_history_and_imports_are_grouped(): void
    {
        $scenario = $this->campaign->scenarios()->create(['name' => '1. La cité', 'position' => 1]);
        $scene = $scenario->scenes()->create(['name' => 'Conférence', 'position' => 1]);
        $scene->update(['status' => SceneStatus::Played]);
        $scene->delete();

        $deleted = ActivityLog::where('subject_type', 'scene')->where('event', 'deleted')->sole();
        $this->assertSame('Conférence', $deleted->subject_label);
        $this->assertSame($this->campaign->id, $deleted->campaign_id);
        $this->assertSame('played', $deleted->diff['status']['old']);

        Livewire::test(Create::class, ['campaign' => $this->campaign, 'mode' => 'scenes'])
            ->set('file', UploadedFile::fake()->createWithContent('s.csv', "Scénario;Scène\n1. La cité;Départ\n1. La cité;Arrivée"))
            ->call('import');

        $batches = ActivityLog::whereNotNull('batch')->pluck('batch')->unique();
        $this->assertCount(1, $batches, 'Les deux scènes importées partagent un même lot.');

        Livewire::test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(['a importé 2 éléments', '2 scène', 'a supprimé', 'Prévue', 'Jouée'])
            ->set('subject', 'scene:'.$scene->id)
            ->assertSee('Conférence')
            ->assertDontSee('Départ');
    }

    public function test_the_journal_is_for_the_game_master_of_the_campaign_only(): void
    {
        $other = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->campaign->world_id]);
        Entity::factory()->for($this->gm, 'owner')->for($other)->create(['name' => 'Secret ailleurs']);
        Entity::factory()->for($this->gm, 'owner')->for($this->campaign->world)->create(['name' => 'Partagé dans le monde']);

        Livewire::test(Index::class, ['campaign' => $this->campaign])
            ->assertSee('Partagé dans le monde')
            ->assertDontSee('Secret ailleurs');

        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $this->actingAs($player)->get(route('journal.index', $this->campaign))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('journal.index', $this->campaign))->assertForbidden();

        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))->assertSee(route('journal.index', $this->campaign));
    }
}
