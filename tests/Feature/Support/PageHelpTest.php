<?php

namespace Tests\Feature\Support;

use App\Enums\CampaignRole;
use App\Livewire\Dice\Tray;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\User;
use App\Support\PageHelp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Mode d'emploi des écrans délicats : un « ? » à côté du titre, quelques lignes,
 * et un lien vers la bonne question de la page « Aide ».
 */
class PageHelpTest extends TestCase
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
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['table_shared' => true]);
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);
    }

    public function test_tricky_screens_carry_their_how_to(): void
    {
        $pages = [
            'Types de fiche' => route('entity-types.index'),
            'Champs du jeu' => route('fields.index', $this->campaign),
            'Tags' => route('tags.index'),
            'Cartes' => route('maps.index', $this->campaign),
            'Écran de table et télécommande' => route('table.remote', $this->campaign),
            'Révéler, Donner et Indices' => route('secrets.index', $this->campaign),
        ];

        foreach ($pages as $title => $url) {
            $this->actingAs($this->gm)->get($url)->assertOk()
                ->assertSee('Mode d’emploi : '.$title)
                ->assertSee(route('help').'#', false);
        }

        $this->actingAs($this->gm)->get(route('reveals.index', $this->campaign))->assertOk()
            ->assertSee('Mode d’emploi : Révéler, Donner et Indices');
    }

    public function test_preparation_and_table_screens_carry_their_how_to(): void
    {
        $scenario = $this->campaign->scenarios()->create(['name' => 'Prologue']);
        $scene = $scenario->scenes()->create(['name' => 'Le quai']);
        $sheet = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'Mira']);

        $pages = [
            'La fiche' => route('entities.show', [$this->campaign, $sheet]),
            'Mode Session' => route('sessions.live', $this->campaign),
            'Scénarios et scènes' => route('scenarios.index', $this->campaign),
            'Importer depuis un fichier' => route('imports.create', $this->campaign),
            'Membres' => route('members.index', $this->campaign),
        ];

        foreach ($pages as $title => $url) {
            $this->actingAs($this->gm)->get($url)->assertOk()->assertSee('Mode d’emploi : '.$title);
        }

        $this->actingAs($this->gm)->get(route('scenes.show', [$this->campaign, $scene]))->assertOk()
            ->assertSee('Mode d’emploi : Scénarios et scènes');
    }

    public function test_each_how_to_points_to_an_existing_help_question(): void
    {
        $help = $this->actingAs($this->gm)->get(route('help'))->assertOk();

        foreach (PageHelp::topics() as $topic) {
            $help->assertSee('id="'.PageHelp::anchor(PageHelp::topic($topic)['faq']).'"', false);
        }
    }

    public function test_the_dice_how_to_tells_only_the_gm_about_requests(): void
    {
        $request = '« Demander » envoie un lancer';

        Livewire::actingAs($this->gm)->test(Tray::class, ['campaign' => $this->campaign])->call('toggle')
            ->assertSee('Mode d’emploi : Dés')->assertSee($request);
        session()->flush(); // Le plateau ouvert se retient dans la session : le joueur part de zéro.

        Livewire::actingAs($this->player)->test(Tray::class, ['campaign' => $this->campaign])->call('toggle')
            ->assertSee('Mode d’emploi : Dés')->assertDontSee($request);
    }
}
