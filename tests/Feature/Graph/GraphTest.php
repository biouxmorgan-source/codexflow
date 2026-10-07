<?php

namespace Tests\Feature\Graph;

use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Livewire\Graph\Index;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\RelationGraph;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GraphTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    /** @var array<string, Entity> */
    private array $e = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);

        foreach (['Harvey', 'Morel', 'Boutique', 'Culte', 'Arkham', 'Musée'] as $name) {
            $this->e[$name] = Entity::factory()->for($this->gm, 'owner')->create(['name' => $name, 'campaign_id' => $this->campaign->id, 'world_id' => null]);
        }
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $this->e['Harvey']->id, 'user_id' => $this->alex->id]);

        $this->relate('Morel', 'tient', 'Boutique', Zone::Public);
        $this->relate('Morel', 'sert', 'Culte', Zone::GameMaster);
        $this->relate('Boutique', 'se trouve à', 'Arkham', Zone::Public);
        $this->relate('Musée', 'se trouve à', 'Arkham', Zone::Public);
        $this->relate('Harvey', 'client de', 'Morel', Zone::Public);
    }

    private function relate(string $from, string $label, string $to, Zone $zone): void
    {
        $relation = new EntityRelation(['label' => $label, 'zone' => $zone]);
        $relation->from_entity_id = $this->e[$from]->id;
        $relation->to_entity_id = $this->e[$to]->id;
        $relation->campaign_id = $this->campaign->id;
        $relation->user_id = $this->gm->id;
        $relation->save();
    }

    private function know(string ...$names): void
    {
        foreach ($names as $name) {
            $this->harvey->grants()->create(['kind' => 'entity', 'entity_id' => $this->e[$name]->id]);
        }
    }

    /** @return list<string> */
    private function names(RelationGraph $graph): array
    {
        return $graph->nodes->pluck('name')->sort()->values()->all();
    }

    public function test_the_game_master_sees_every_relation_and_can_focus_with_a_depth(): void
    {
        $graph = RelationGraph::build($this->campaign, null);
        $this->assertSame(['Arkham', 'Boutique', 'Culte', 'Harvey', 'Morel', 'Musée'], $this->names($graph));
        $this->assertCount(5, $graph->edges);
        $this->assertTrue($graph->edges->firstWhere('label', 'sert')['gm']);

        $around = RelationGraph::build($this->campaign, null, $this->e['Morel']->id, 1);
        $this->assertSame(['Boutique', 'Culte', 'Harvey', 'Morel'], $this->names($around));

        $deeper = RelationGraph::build($this->campaign, null, $this->e['Morel']->id, 2);
        $this->assertContains('Arkham', $this->names($deeper));
        $this->assertNotContains('Musée', $this->names($deeper));

        $capped = RelationGraph::build($this->campaign, null, $this->e['Morel']->id, 3, limit: 3);
        $this->assertTrue($capped->truncated);
        $this->assertContains('Morel', $this->names($capped));
        $this->assertCount(3, $capped->nodes);

        $this->actingAs($this->gm)->get(route('graph.index', $this->campaign))->assertOk()->assertSee('Culte');
    }

    public function test_the_type_filter_keeps_the_focused_entry(): void
    {
        $place = $this->e['Arkham']->entity_type_id;
        $this->assertNotNull($place);

        $graph = RelationGraph::build($this->campaign, null, null, 2, $place);
        $this->assertTrue($graph->nodes->every(fn ($node) => $node['type_id'] === $place));
        $this->assertNotEmpty($graph->types);
    }

    /** Parcours : le graphe d'un personnage ne montre que ce qu'il connaît, et jamais la zone MJ. */
    public function test_a_character_sees_only_known_entries_and_public_relations_between_them(): void
    {
        $this->know('Morel', 'Culte', 'Arkham');

        $graph = RelationGraph::build($this->campaign, $this->harvey);
        // « Morel sert le Culte » est en zone MJ ; la Boutique n'est pas connue, donc ni « tient » ni « se trouve à ».
        $this->assertSame(['Harvey', 'Morel'], $this->names($graph));
        $this->assertSame(['client de'], $graph->edges->pluck('label')->all());
        $this->assertSame(route('characters.entity', [$this->campaign, $this->harvey, $this->e['Morel']]), $graph->nodes->firstWhere('name', 'Morel')['url']);

        $this->actingAs($this->alex)->get(route('graph.index', $this->campaign))
            ->assertOk()->assertSee('Morel')->assertDontSee('Culte')->assertDontSee('Boutique')->assertDontSee('sert');

        // Centrer sur une fiche inconnue ne révèle rien.
        $this->actingAs($this->alex)->get(route('graph.index', [$this->campaign, 'fiche' => $this->e['Boutique']->id]))
            ->assertOk()->assertDontSee('Boutique');

        $this->know('Boutique');
        $this->assertContains('tient', RelationGraph::build($this->campaign, $this->harvey)->edges->pluck('label')->all());
    }

    public function test_view_as_is_for_the_game_master_and_players_only_use_their_own_character(): void
    {
        $other = $this->campaign->playerCharacters()->create(['entity_id' => $this->e['Musée']->id]);
        $this->know('Morel');

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('asCharacterId', (string) $this->harvey->id)
            ->assertSee('client de')
            ->assertDontSee('sert');

        $this->actingAs($this->alex)->get(route('graph.index', [$this->campaign, 'comme' => $other->id]))->assertNotFound();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('graph.index', $this->campaign))->assertForbidden();
    }
}
