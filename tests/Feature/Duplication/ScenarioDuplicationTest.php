<?php

namespace Tests\Feature\Duplication;

use App\Enums\CampaignRole;
use App\Enums\SceneStatus;
use App\Livewire\Scenarios\Index;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Rule;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\ToPlayItem;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScenarioDuplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Scenario $scenario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->scenario = $this->campaign->scenarios()->create(['name' => 'Le Manoir', 'summary' => 'Une nuit hantée', 'position' => 0]);
    }

    public function test_gm_duplicates_a_scenario_with_its_scenes_and_links_and_statuses_are_reset(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Le majordome']);
        $rule = new Rule(['title' => 'Peur']);
        $rule->owner()->associate($this->gm);
        $rule->campaign()->associate($this->campaign);
        $rule->save();
        $document = new Document(['title' => 'Plan', 'disk' => 'local', 'path' => 'documents/plan.pdf', 'original_name' => 'plan.pdf', 'mime_type' => 'application/pdf', 'size' => 1]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        $hall = $this->scenario->scenes()->create(['chapter' => 'Acte I', 'name' => 'Le hall', 'description' => 'Poussière', 'status' => SceneStatus::Played, 'position' => 0]);
        $cellar = $this->scenario->scenes()->create(['chapter' => 'Acte II', 'name' => 'La cave', 'status' => SceneStatus::InProgress, 'position' => 1]);
        $hall->entities()->attach($npc, ['note' => 'caché derrière la porte', 'position' => 3]);
        $cellar->rules()->attach($rule, ['position' => 1]);
        $cellar->documents()->attach($document, ['position' => 2]);
        $hall->tags()->sync(\App\Models\Tag::idsFromInput($this->gm, 'exploration'));

        $pending = new ToPlayItem(['body' => 'Un cri dans la nuit', 'position' => 0]);
        $pending->campaign()->associate($this->campaign);
        $pending->scene()->associate($cellar);
        $pending->save();
        $done = new ToPlayItem(['body' => 'Déjà joué', 'position' => 1, 'done_at' => now()]);
        $done->campaign()->associate($this->campaign);
        $done->scene()->associate($cellar);
        $done->save();

        Livewire::actingAs($this->gm)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->assertSee(__('Dupliquer'))
            ->call('duplicate', $this->scenario->id)
            ->assertSee('Le Manoir (copie)');

        $copy = Scenario::where('name', 'Le Manoir (copie)')->sole();
        $this->assertSame($this->campaign->id, $copy->campaign_id);
        $this->assertSame('Une nuit hantée', $copy->summary);
        $this->assertSame(1, $copy->position);

        $scenes = $copy->scenes;
        $this->assertSame(['Le hall', 'La cave'], $scenes->pluck('name')->all());
        $this->assertSame(['Acte I', 'Acte II'], $scenes->pluck('chapter')->all());
        $this->assertSame('Poussière', $scenes[0]->description);
        $this->assertEquals([SceneStatus::Planned, SceneStatus::Planned], $scenes->pluck('status')->all());

        $this->assertSame('caché derrière la porte', $scenes[0]->entities->sole()->pivot->note);
        $this->assertTrue($scenes[0]->entities->sole()->is($npc));
        $this->assertTrue($scenes[1]->rules->sole()->is($rule));
        $this->assertTrue($scenes[1]->documents->sole()->is($document));
        $this->assertSame(['exploration'], $scenes[0]->tags->pluck('name')->all());
        $this->assertSame(['Un cri dans la nuit'], $scenes[1]->toPlayItems->pluck('body')->all());

        // L'original garde sa progression.
        $this->assertSame(SceneStatus::Played, $hall->fresh()->status);
        $this->assertSame(2, $this->scenario->scenes()->count());

        // Les créations sont regroupées en une seule opération du journal.
        $entries = ActivityLog::where('event', 'created')
            ->where(fn ($q) => $q->where(['subject_type' => 'scenario', 'subject_id' => $copy->id])
                ->orWhere(fn ($q) => $q->where('subject_type', 'scene')->whereIn('subject_id', $scenes->modelKeys())))
            ->get();
        $this->assertCount(3, $entries);
        $this->assertNotNull($entries->first()->batch);
        $this->assertCount(1, $entries->pluck('batch')->unique());
    }

    public function test_a_player_cannot_duplicate_a_scenario(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        Livewire::actingAs($player)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->assertForbidden();

        $this->assertSame(1, Scenario::count());
        $this->assertSame(0, Scene::count());
    }
}
