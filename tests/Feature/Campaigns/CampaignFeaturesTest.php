<?php

namespace Tests\Feature\Campaigns;

use App\Actions\Characters\ExchangeGrant;
use App\Actions\Characters\GiveToCharacters;
use App\Actions\Demo\LoadDemoCampaign;
use App\Actions\Duplication\DuplicateCampaign;
use App\Enums\CampaignRole;
use App\Livewire\Campaigns\Show;
use App\Livewire\Characters\Show as CharacterShow;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\TableMap;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Support\Archive\CampaignExport;
use App\Support\CampaignFeatures;
use App\Support\TableDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Le MJ coupe, campagne par campagne, les fonctions dont sa table n'a pas besoin :
 * elles disparaissent pour tous, sont refusées par le serveur, et rien n'est effacé.
 */
class CampaignFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->campaign = app(LoadDemoCampaign::class)->handle($this->gm, 'fr');
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);
    }

    public function test_everything_is_on_by_default_and_switching_off_hides_and_refuses_without_deleting(): void
    {
        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))
            ->assertSee([route('graph.index', $this->campaign), route('timeline.index', $this->campaign), route('maps.index', $this->campaign), route('table.remote', $this->campaign)])
            ->assertSee('Fonctions de la campagne');

        $before = [TableMap::count(), TimelineEvent::count()];
        $page = Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign]);
        foreach (['graph', 'timeline', 'maps', 'table'] as $feature) {
            $page->call('toggleFeature', $feature);
        }
        $this->campaign->refresh();
        $this->assertSame(['graph', 'timeline', 'maps', 'table'], $this->campaign->disabled_features);
        $this->assertSame($before, [TableMap::count(), TimelineEvent::count()], 'Couper une fonction n’efface rien.');

        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))
            ->assertOk()
            ->assertDontSee([route('graph.index', $this->campaign), route('timeline.index', $this->campaign), route('maps.index', $this->campaign), route('table.remote', $this->campaign)]);

        foreach (['graph.index', 'timeline.index', 'maps.index', 'table.screen', 'table.remote'] as $route) {
            $this->actingAs($this->gm)->get(route($route, $this->campaign))->assertForbidden()->assertSee('Fonction désactivée');
        }
        $this->actingAs($this->player)->get(route('graph.index', $this->campaign))->assertForbidden();

        // Le serveur refuse aussi d'afficher quoi que ce soit à la table ; vider reste possible.
        try {
            TableDisplay::showText($this->campaign, 'Trois jours plus tard…');
            $this->fail('L’écran de table coupé ne doit rien afficher.');
        } catch (HttpException $e) {
            $this->assertSame(CampaignFeatures::DISABLED, $e->getMessage());
        }
        TableDisplay::clear($this->campaign);

        // Les autres fonctions restent là.
        $this->actingAs($this->gm)->get(route('secrets.index', $this->campaign))->assertOk();

        // Tout revient en recochant.
        $page->call('toggleFeature', 'graph')->call('toggleFeature', 'maps');
        $this->assertSame(['timeline', 'table'], $this->campaign->fresh()->disabled_features);
        $this->actingAs($this->gm)->get(route('graph.index', $this->campaign))->assertOk();
        $this->actingAs($this->gm)->get(route('maps.index', $this->campaign))->assertOk();
    }

    public function test_only_known_features_can_be_switched(): void
    {
        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign])->call('toggleFeature', 'messages')->assertStatus(422);
        $this->assertSame([], $this->campaign->fresh()->disabled_features);
    }

    public function test_switching_exchanges_off_stops_players_giving_to_each_other(): void
    {
        $other = User::factory()->create();
        $this->campaign->members()->attach($other, ['role' => CampaignRole::Player->value]);
        $character = EntityType::standard('character')->id;
        [$harvey, $jack] = collect([[$this->player, 'Harvey'], [$other, 'Jack']])->map(function ($pair) use ($character) {
            $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $pair[1], 'entity_type_id' => $character]);

            return $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $pair[0]->id]);
        })->all();
        app(GiveToCharacters::class)->handle($this->campaign, [$harvey->id], ['kind' => 'possession', 'title' => 'Lampe torche']);

        CampaignFeatures::toggle($this->campaign, 'exchanges');

        Livewire::actingAs($this->player)->test(CharacterShow::class, ['campaign' => $this->campaign->fresh(), 'character' => $harvey])
            ->assertDontSee('Transmettre')
            ->assertDontSeeHtml('wire:click="startExchange');

        $this->actingAs($this->player);
        $this->expectException(HttpException::class);
        app(ExchangeGrant::class)->handle($harvey->grants()->sole(), $jack);
    }

    public function test_the_choice_follows_the_campaign_into_copies_and_archives(): void
    {
        CampaignFeatures::toggle($this->campaign, 'ai');
        CampaignFeatures::toggle($this->campaign, 'timeline');

        $this->actingAs($this->gm);
        $copy = app(DuplicateCampaign::class)->handle($this->campaign->fresh(), $this->gm);
        $this->assertSame(['timeline', 'ai'], $copy->fresh()->disabled_features);

        $zip = new \ZipArchive;
        $zip->open((new CampaignExport($this->campaign->fresh()))->write());
        $this->assertSame(['ai', 'timeline'], json_decode($zip->getFromName('campagne.json'), true)['campaign']['disabled_features']);
        $zip->close();
        $this->assertSame(['ai'], CampaignFeatures::clean(['ai', 'inconnue', 42]));
    }
}
