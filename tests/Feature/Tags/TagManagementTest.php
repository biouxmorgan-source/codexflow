<?php

namespace Tests\Feature\Tags;

use App\Livewire\Scenarios\Index as ScenarioIndex;
use App\Livewire\Scenes\Form as SceneForm;
use App\Livewire\Tags\Manage;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagManagementTest extends TestCase
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

    private function tag(string $name, ?User $owner = null): Tag
    {
        $tag = new Tag(['name' => $name]);
        $tag->owner()->associate($owner ?? $this->gm);
        $tag->save();

        return $tag;
    }

    private function entity(string $name): Entity
    {
        return Entity::factory()->for($this->gm, 'owner')->create(['name' => $name, 'campaign_id' => $this->campaign->id]);
    }

    private function scene(string $name): Scene
    {
        $scenario = $this->campaign->scenarios()->firstOrCreate(['name' => 'Scénario']);

        return $scenario->scenes()->create(['name' => $name]);
    }

    public function test_the_gm_renames_and_colours_a_tag(): void
    {
        $tag = $this->tag('taverne');
        $this->tag('Intrigue');
        $this->entity('Le Chien noir')->tags()->attach($tag);

        $this->actingAs($this->gm)->get(route('tags.index'))->assertOk()->assertSee('taverne')->assertSee('1 fiche');

        Livewire::actingAs($this->gm)
            ->test(Manage::class)
            ->call('edit', $tag->id)
            ->set('name', 'intrigue')
            ->call('save')
            ->assertHasErrors('name')
            ->set('name', 'Auberges')
            ->set('color', 'amber')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['Auberges', 'amber'], [$tag->fresh()->name, $tag->fresh()->color]);

        Livewire::actingAs($this->gm)->test(Manage::class)->call('edit', $tag->id)->set('color', 'fuchsia')->call('save')->assertHasErrors('color');

        $this->actingAs($this->gm)->get(route('entities.show', [$this->campaign, Entity::where('name', 'Le Chien noir')->sole()]))
            ->assertSee('Auberges')
            ->assertSee(Tag::COLORS['amber']);
    }

    public function test_merging_moves_everything_to_the_other_tag(): void
    {
        $old = $this->tag('PNJ');
        $kept = $this->tag('pnj important');
        $entity = $this->entity('Aldric');
        $both = $this->entity('Mira');
        $scene = $this->scene('Le bal');
        $entity->tags()->attach($old);
        $both->tags()->attach([$old->id, $kept->id]);
        $scene->tags()->attach($old);

        Livewire::actingAs($this->gm)
            ->test(Manage::class)
            ->call('startMerge', $old->id)
            ->call('merge')
            ->assertHasErrors('mergeTargetId')
            ->set('mergeTargetId', $kept->id)
            ->call('merge')
            ->assertHasNoErrors();

        $this->assertModelMissing($old);
        $this->assertEqualsCanonicalizing([$entity->id, $both->id], $kept->entities()->pluck('entities.id')->all());
        $this->assertSame([$scene->id], $kept->scenes()->pluck('scenes.id')->all());
    }

    public function test_deleting_a_tag_keeps_the_tagged_items(): void
    {
        $tag = $this->tag('Brouillon');
        $entity = $this->entity('Aldric');
        $entity->tags()->attach($tag);

        Livewire::actingAs($this->gm)->test(Manage::class)->call('delete', $tag->id);

        $this->assertModelMissing($tag);
        $this->assertModelExists($entity);
        $this->assertCount(0, $entity->tags()->get());
    }

    public function test_tags_of_another_account_are_out_of_reach(): void
    {
        $other = User::factory()->create();
        $foreign = $this->tag('Secret', $other);
        $mine = $this->tag('À moi');

        Livewire::actingAs($this->gm)->test(Manage::class)->assertDontSee('Secret');

        foreach (['edit', 'startMerge', 'delete'] as $method) {
            Livewire::actingAs($this->gm)->test(Manage::class)->call($method, $foreign->id)->assertNotFound();
        }

        Livewire::actingAs($this->gm)->test(Manage::class)->call('startMerge', $mine->id)->set('mergeTargetId', $foreign->id)->call('merge')->assertNotFound();

        $this->assertModelExists($foreign);
        $this->assertModelExists($mine);
    }

    public function test_scenes_carry_tags_and_the_plan_filters_on_them(): void
    {
        $scenario = $this->campaign->scenarios()->create(['name' => 'Acte I']);
        $quiet = $scenario->scenes()->create(['name' => 'Le marché']);

        Livewire::actingAs($this->gm)
            ->test(SceneForm::class, ['campaign' => $this->campaign])
            ->set('scenarioId', (string) $scenario->id)
            ->set('name', "L'embuscade")
            ->set('tags', 'combat, Nuit')
            ->call('save')
            ->assertHasNoErrors();

        $scene = Scene::where('name', "L'embuscade")->sole();
        $this->assertSame(['combat', 'Nuit'], $scene->tags->pluck('name')->all());

        $combat = Tag::where('name', 'combat')->sole();

        Livewire::actingAs($this->gm)
            ->test(ScenarioIndex::class, ['campaign' => $this->campaign])
            ->assertSee('Le marché')
            ->set('tag', $combat->id)
            ->assertSee("L'embuscade")
            ->assertDontSee('Le marché');

        $this->actingAs($this->gm)->get(route('scenes.show', [$this->campaign, $scene]))->assertSee('combat')->assertSee('Nuit');
        $this->assertNotNull($quiet);
    }
}
