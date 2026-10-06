<?php

namespace Tests\Feature\Entities;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\User;
use App\Models\World;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorldEntityTest extends TestCase
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

    public function test_world_entities_are_shared_between_campaigns_without_duplication(): void
    {
        $inn = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Le Poney fringant']);
        $local = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaignA->id, 'world_id' => null]);

        $this->assertEqualsCanonicalizing([$inn->id, $local->id], $this->campaignA->availableEntities()->pluck('id')->all());
        $this->assertSame([$inn->id], $this->campaignB->availableEntities()->pluck('id')->all());
        $this->assertSame(2, Entity::count());
    }

    public function test_campaign_state_overrides_do_not_touch_the_world_sheet(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Aldric']);

        $state = $npc->stateIn($this->campaignA);
        $state->fill(['status' => 'mort', 'gm_notes' => 'Tué par les joueurs à la session 3.'])->save();

        $this->assertSame('mort', $npc->stateIn($this->campaignA)->status);
        $this->assertNull($npc->stateIn($this->campaignB)->status);
        $this->assertFalse($npc->stateIn($this->campaignB)->exists);
        $this->assertSame('Aldric', $npc->fresh()->name);
    }

    public function test_an_entity_belongs_to_exactly_one_scope(): void
    {
        $this->expectException(QueryException::class);

        Entity::factory()->for($this->gm, 'owner')->create([
            'world_id' => $this->world->id,
            'campaign_id' => $this->campaignA->id,
        ]);
    }

    public function test_gm_notes_are_hidden_from_serialization(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['gm_notes' => 'Secret']);

        $this->assertArrayNotHasKey('gm_notes', $npc->toArray());
    }
}
