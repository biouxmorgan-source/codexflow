<?php

namespace Tests\Feature\Table;

use App\Actions\Duplication\DuplicateCampaign;
use App\Enums\CampaignRole;
use App\Enums\SceneStatus;
use App\Livewire\Maps\Atlas;
use App\Livewire\Table\Screen;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\MapPoint;
use App\Models\Scene;
use App\Models\TableMap;
use App\Models\User;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use App\Support\TableDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Carte de campagne : des points d'intérêt qui ouvrent les sous-cartes, situent les scènes et restent au MJ. */
class CampaignMapTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    private TableMap $world;

    private TableMap $city;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['table_shared' => true]);
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);

        $this->world = $this->map('Le continent', 2000, 1000);
        $this->city = $this->map('Port-Brume', 800, 800);
    }

    private function map(string $name, int $width, int $height, ?Campaign $campaign = null): TableMap
    {
        $campaign ??= $this->campaign;
        $path = UploadedFile::fake()->image('map.png', 20, 10)->store('documents', 'local');
        $document = new Document(['title' => $name, 'disk' => 'local', 'path' => $path, 'original_name' => 'map.png', 'mime_type' => 'image/png', 'size' => 10]);
        $document->owner()->associate($campaign->owner);
        $document->campaign()->associate($campaign);
        $document->save();

        $map = new TableMap(['name' => $name, 'grid_size' => 50]);
        $map->forceFill(['width' => $width, 'height' => $height]);
        $map->campaign()->associate($campaign);
        $map->document()->associate($document);
        $map->save();

        return $map->refresh();
    }

    private function scene(string $name, SceneStatus $status): Scene
    {
        return $this->campaign->scenarios()->create(['name' => 'Acte 1', 'position' => 1])
            ->scenes()->create(['name' => $name, 'status' => $status, 'position' => 1]);
    }

    public function test_the_gm_chooses_the_campaign_map_which_opens_first(): void
    {
        $this->actingAs($this->gm);

        Livewire::test(Atlas::class, ['campaign' => $this->campaign])
            ->assertSee('Choisissez la carte qui servira de vue d’ensemble');

        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->world])->call('makeCampaignMap');
        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->city])->call('makeCampaignMap');

        // Une seule carte de campagne : la dernière choisie.
        $this->assertFalse($this->world->refresh()->is_campaign_map);
        $this->assertTrue($this->city->refresh()->is_campaign_map);

        $this->get(route('maps.atlas', $this->campaign))->assertOk()->assertSee('Port-Brume');
    }

    public function test_a_point_leads_to_a_sub_map_with_a_way_back_up(): void
    {
        $this->world->forceFill(['is_campaign_map' => true])->save();
        $this->actingAs($this->gm);

        $atlas = Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->world])
            ->call('addPoint', 3000, 400);

        $point = $this->world->points()->sole();
        $this->assertSame(2000.0, $point->x, 'Le point reste sur la carte.');
        $this->assertSame($point->id, $atlas->get('selectedId'));

        $atlas->set('pointLabel', 'Port-Brume')
            ->set('pointTarget', (string) $this->city->id)
            ->call('savePoint')
            ->assertHasNoErrors()
            ->call('movePoint', $point->id, 1200, 500);

        $point->refresh();
        $this->assertSame($this->city->id, $point->target_map_id);
        $this->assertSame([1200.0, 500.0], [$point->x, $point->y]);

        // Un point ne mène pas à sa propre carte, ni à la carte d'une autre campagne.
        $other = $this->map('Ailleurs', 100, 100, Campaign::factory()->for($this->gm, 'owner')->create());
        $atlas->set('pointTarget', (string) $this->world->id)->call('savePoint')->assertHasErrors('pointTarget');
        $atlas->set('pointTarget', (string) $other->id)->call('savePoint')->assertHasErrors('pointTarget');

        // Le clic ouvre la sous-carte ; le fil d'Ariane remonte au continent.
        $this->get(route('maps.atlas', [$this->campaign, $this->world]))->assertOk()
            ->assertSee(route('maps.atlas', [$this->campaign, $this->city]), false);
        $this->assertSame([$this->world->id], array_map(fn (TableMap $map) => $map->id, $this->city->parents()));
        $this->get(route('maps.atlas', [$this->campaign, $this->city]))->assertOk()->assertSee('← Remonter');
    }

    public function test_sub_maps_nest_at_any_depth_and_loops_stop(): void
    {
        $this->world->forceFill(['is_campaign_map' => true])->save();
        $tavern = $this->map('La taverne', 400, 300);

        $this->world->points()->make(['label' => 'Port-Brume', 'x' => 1, 'y' => 1])->forceFill(['target_map_id' => $this->city->id])->save();
        $this->city->points()->make(['label' => 'Taverne', 'x' => 1, 'y' => 1])->forceFill(['target_map_id' => $tavern->id])->save();
        // Une boucle : la taverne mène à la ville.
        $tavern->points()->make(['label' => 'Sortie', 'x' => 1, 'y' => 1])->forceFill(['target_map_id' => $this->city->id])->save();

        $this->assertSame(['Le continent', 'Port-Brume'], array_map(fn (TableMap $map) => $map->name, $tavern->parents()));
        $this->assertSame(['Le continent'], array_map(fn (TableMap $map) => $map->name, $this->city->fresh()->parents()));
    }

    public function test_a_point_links_several_scenes_and_sheets(): void
    {
        $played = $this->scene('L’arrivée', SceneStatus::Played);
        $later = $this->scene('Le retour', SceneStatus::Played);
        $upcoming = $this->scene('Le phare', SceneStatus::Planned);
        $place = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'Le quai']);
        $npc = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'La capitaine']);
        $elsewhere = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => Campaign::factory()->for($this->gm, 'owner')->create()->id, 'world_id' => null]);
        $this->actingAs($this->gm);

        $atlas = Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->world])
            ->call('addPoint', 100, 100)->set('pointLabel', 'Le quai')->call('savePoint')
            ->call('addScene', (string) $played->id)
            ->call('addScene', (string) $later->id)
            ->call('addScene', (string) $played->id)
            ->set('pointEntityId', $place->id)->call('addEntity')
            ->set('pointEntityId', $npc->id)->call('addEntity')
            ->set('pointEntityId', $elsewhere->id)->call('addEntity')->assertHasErrors('pointEntityId');

        $quay = $this->world->points()->sole();
        $this->assertSame(['L’arrivée', 'Le retour'], $quay->scenes->pluck('name')->all(), 'Dans l’ordre d’ajout, sans doublon.');
        $this->assertSame(['Le quai', 'La capitaine'], $quay->entities->pluck('name')->all());
        $this->assertTrue($quay->played());
        $atlas->assertSee('La capitaine')->assertSee('Le retour');

        // Une scène encore à jouer : le lieu redevient « à venir ».
        $atlas->call('addScene', (string) $upcoming->id);
        $this->assertTrue($quay->fresh()->upcoming());
        $this->get(route('scenes.show', [$this->campaign, $upcoming]))->assertOk()->assertSee('Le continent · Le quai');

        $atlas->call('unlink', 'scenes', $upcoming->id)->call('unlink', 'entities', $npc->id);
        $this->assertSame(['Le quai'], $quay->fresh()->entities->pluck('name')->all());
        $this->assertSame('played', $quay->fresh()->progress());

        // Une scène d'une autre campagne ne se relie pas.
        $other = Campaign::factory()->for($this->gm, 'owner')->create()->scenarios()->create(['name' => 'X', 'position' => 1])->scenes()->create(['name' => 'Ailleurs', 'position' => 1]);
        $atlas->call('addScene', (string) $other->id)->assertStatus(422);
    }

    public function test_the_table_screen_follows_the_gm_into_a_sub_map_and_back(): void
    {
        $this->world->forceFill(['is_campaign_map' => true])->save();
        $point = $this->world->points()->make(['label' => 'Port-Brume', 'x' => 1, 'y' => 1])->forceFill(['target_map_id' => $this->city->id]);
        $point->save();
        $tavern = $this->map('La taverne', 400, 300);
        $this->actingAs($this->gm);

        // Carte pas à la table : ouvrir la sous-carte ne change pas l'écran.
        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->world])->call('openMap', $this->city->id)
            ->assertRedirect(route('maps.atlas', [$this->campaign, $this->city]));
        $this->assertFalse(TableDisplay::isShowing($this->campaign->fresh(), 'map', $this->city->id));

        TableDisplay::show($this->campaign, 'map', $this->world->id);
        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->world])->call('openMap', $this->city->id);
        $this->assertTrue(TableDisplay::isShowing($this->campaign->fresh(), 'map', $this->city->id));

        // Dans la sous-carte, le lieu d'où l'on vient garde ses scènes et fiches à portée de clic.
        $point->scenes()->attach($this->scene('Le marché', SceneStatus::Planned));
        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->city])
            ->assertSee('Ce lieu : Port-Brume')->assertSee('Le marché')
            ->call('openMap', $this->world->id);
        $this->assertTrue(TableDisplay::isShowing($this->campaign->fresh(), 'map', $this->world->id), 'En remontant, la carte d’au-dessus revient à la table.');

        // Une carte qui n'est pas à la table ne prend pas la place de celle qui y est.
        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $tavern])->call('openMap', $this->city->id);
        $this->assertTrue(TableDisplay::isShowing($this->campaign->fresh(), 'map', $this->world->id));

        $other = $this->map('Ailleurs', 100, 100, Campaign::factory()->for($this->gm, 'owner')->create());
        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->world])->call('openMap', $other->id)->assertNotFound();
    }

    public function test_only_the_names_of_shown_points_reach_the_table_screen(): void
    {
        $this->world->points()->create(['label' => 'Ville connue', 'x' => 10, 'y' => 10, 'shown_at_table' => true, 'note' => 'Le culte s’y cache']);
        $this->world->points()->create(['label' => 'Repaire secret', 'x' => 20, 'y' => 20, 'shown_at_table' => false]);
        TableDisplay::show($this->campaign, 'map', $this->world->id);

        Livewire::actingAs($this->player)->test(Screen::class, ['campaign' => $this->campaign->fresh()])
            ->assertSee('Ville connue')
            ->assertDontSee('Repaire secret')
            ->assertDontSee('Le culte s’y cache');
    }

    public function test_players_and_outsiders_cannot_reach_the_campaign_map(): void
    {
        $point = $this->world->points()->create(['label' => 'Repaire', 'x' => 10, 'y' => 10]);

        $this->actingAs($this->player)->get(route('maps.atlas', [$this->campaign, $this->world]))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('maps.atlas', $this->campaign))->assertNotFound();

        Livewire::actingAs($this->player)->test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->world])->assertForbidden();

        // Le point d'une autre carte ne se modifie pas d'ici.
        $this->actingAs($this->gm);
        Livewire::test(Atlas::class, ['campaign' => $this->campaign, 'map' => $this->city])->call('deletePoint', $point->id)->assertNotFound();
        $this->assertModelExists($point);
    }

    public function test_duplicating_or_deleting_the_campaign_keeps_points_consistent(): void
    {
        $scene = $this->scene('L’arrivée', SceneStatus::Played);
        $this->world->forceFill(['is_campaign_map' => true])->save();
        $this->world->points()->make(['label' => 'Port-Brume', 'x' => 1, 'y' => 1])->forceFill(['target_map_id' => $this->city->id])->save();
        $this->world->points()->sole()->scenes()->attach($scene);
        $this->city->points()->make(['label' => 'Retour', 'x' => 1, 'y' => 1])->forceFill(['target_map_id' => $this->world->id])->save();

        $copy = app(DuplicateCampaign::class)->handle($this->campaign, $this->gm, true);

        $map = $copy->campaignMap()->firstOrFail();
        $point = $map->points()->sole();
        $this->assertSame('Le continent', $map->name);
        $this->assertSame($copy->id, $point->target->campaign_id);
        $this->assertSame('Port-Brume', $point->target->name);
        $this->assertSame($copy->id, $point->scenes->sole()->scenario->campaign_id);

        $this->campaign->delete();
        $this->assertSame(0, MapPoint::query()->whereIn('table_map_id', [$this->world->id, $this->city->id])->count());
        $this->assertSame(2, MapPoint::count());
    }

    public function test_points_travel_in_the_campaign_archive(): void
    {
        $scene = $this->scene('L’arrivée', SceneStatus::Played);
        $this->city->forceFill(['is_campaign_map' => true])->save();
        $this->city->points()->make(['label' => 'Vers le continent', 'note' => 'Route sûre', 'x' => 5, 'y' => 6, 'shown_at_table' => true])
            ->forceFill(['target_map_id' => $this->world->id])->save();
        $place = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'Le port']);
        $this->city->points()->sole()->scenes()->attach($scene);
        $this->city->points()->sole()->entities()->attach($place);

        $path = (new CampaignExport($this->campaign->fresh()))->write();
        $copy = (new CampaignImport(User::factory()->create()))->handle($path);

        $map = $copy->campaignMap()->firstOrFail();
        $point = $map->points()->sole();
        $this->assertSame('Port-Brume', $map->name);
        $this->assertSame(['Vers le continent', 'Route sûre', true], [$point->label, $point->note, $point->shown_at_table]);
        $this->assertSame('Le continent', $point->target->name);
        $this->assertSame($copy->id, $point->target->campaign_id);
        $this->assertSame($copy->id, $point->scenes->sole()->scenario->campaign_id);
        $this->assertSame($copy->id, $point->entities->sole()->campaign_id);
    }
}
