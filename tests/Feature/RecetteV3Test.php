<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Http\Middleware\EnsureFeature;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\Sessions\Live;
use App\Livewire\Sessions\Show as SessionShow;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\Recette;
use App\Models\Rule;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\World;
use App\Support\CampaignFeatures;
use App\Support\RecetteImport;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bugs relevés par la recette finale v0.34.0 (cahier database/data/recettes/v3.json), un test par bug.
 */
class RecetteV3Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    private function sheet(string $name): Entity
    {
        return Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $name]);
    }

    private function history(Entity $entity)
    {
        return $this->actingAs($this->gm)->get(route('journal.index', [$this->campaign, 'sujet' => 'entity:'.$entity->id]))->assertOk();
    }

    public function test_relations_attachments_tags_and_campaign_status_appear_in_the_sheet_history(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $vigil = $this->sheet('Vigie Orme');
        $guild = $this->sheet('La Guilde');

        $show = Livewire::actingAs($this->gm)->test(EntityShow::class, ['campaign' => $this->campaign, 'entity' => $vigil])
            ->set('relationLabel', 'surveille')
            ->set('relationTargetId', $guild->id)
            ->call('addRelation')
            ->assertHasNoErrors()
            ->set('uploads', [UploadedFile::fake()->image('plan-du-port.png')])
            ->call('saveUploads')
            ->assertHasNoErrors();
        $show->call('deleteRelation', $vigil->relationsIn($this->campaign)->sole()->id);

        Livewire::actingAs($this->gm)->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $vigil])
            ->set('tags', 'port, guet')
            ->call('save')
            ->assertHasNoErrors();

        $state = $vigil->stateIn($this->campaign);
        $state->status = 'prisonnière';
        $state->save();

        $this->history($vigil)
            ->assertSee(['Relation', 'surveille La Guilde', 'Fichier joint', 'plan-du-port.png', 'Tags', 'guet, port', 'prisonnière']);
    }

    public function test_an_unanswered_yes_no_field_stays_empty_when_the_sheet_is_saved(): void
    {
        $stealthy = $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'Furtif', 'type' => FieldType::Boolean, 'zone' => Zone::Public, 'position' => FieldDefinition::count(),
        ]);
        $sheet = $this->sheet('Odon');

        Livewire::actingAs($this->gm)->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $sheet])
            ->set('summary', 'Passeur')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertNull($sheet->fresh()->fieldValue($stealthy));

        Livewire::actingAs($this->gm)->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $sheet->fresh()])
            ->set("fields.{$stealthy->id}", true)
            ->call('save');
        Livewire::actingAs($this->gm)->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $sheet->fresh()])
            ->set("fields.{$stealthy->id}", false)
            ->call('save');
        $this->assertFalse($sheet->fresh()->fieldValue($stealthy), 'Une case décochée après avoir été cochée reste « Non ».');
    }

    public function test_the_world_page_shows_its_history_and_the_game_page_its_sheet_types(): void
    {
        $this->campaign->world()->associate(World::factory()->for($this->gm, 'owner')->create())->save();
        $event = new TimelineEvent(['kind' => 'world', 'date_label' => 'An 300', 'title' => 'La grande crue', 'zone' => Zone::GameMaster]);
        $event->campaign()->associate($this->campaign);
        $event->user_id = $this->gm->id;
        $event->position = 1;
        $event->save();

        $this->actingAs($this->gm)->get(route('worlds.show', $this->campaign->world))
            ->assertOk()->assertSee(['Histoire du monde', 'An 300', 'La grande crue']);
        $this->actingAs($this->gm)->get(route('games.show', $this->campaign->gameSystem))
            ->assertOk()->assertSee(['Types de fiche', 'Personnage', 'Créature']);
    }

    public function test_the_journal_shows_links_by_their_name(): void
    {
        $vigil = $this->sheet('Vigie Orme');
        $this->actingAs($this->gm);
        $rule = new Rule(['title' => 'Guet', 'procedure' => 'Lancer 2 dés.']);
        $rule->owner()->associate($this->gm);
        $rule->gameSystem()->associate($this->campaign->gameSystem);
        $rule->save();
        $rule->update(['procedure' => "Lancer 2 dés. Voir [[Vigie Orme|{$vigil->id}]]"]);

        $this->get(route('journal.index', $this->campaign))->assertSee('Voir Vigie Orme')->assertDontSee('[[Vigie Orme|');
    }

    public function test_the_session_page_shows_its_summary_events_and_reveals(): void
    {
        $session = $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'information', 'title' => 'Le code du coffre']);
        $event = new TimelineEvent(['kind' => 'played', 'title' => 'Harvey fuit le manoir', 'zone' => Zone::Public]);
        $event->campaign()->associate($this->campaign);
        $event->user_id = $this->gm->id;
        $event->position = 1;
        $event->play_session_id = $session->id;
        $event->save();

        Livewire::actingAs($this->gm)->test(SessionShow::class, ['campaign' => $this->campaign, 'playSession' => $session])
            ->assertSee(['Pas encore de résumé', 'Harvey fuit le manoir', 'Harvey', 'Le code du coffre'])
            ->set('editingSummary', true)
            ->set('summary', 'La table a ouvert le coffre.')
            ->call('saveSummary')
            ->assertHasNoErrors()
            ->assertSee('La table a ouvert le coffre.');
        $this->assertSame('La table a ouvert le coffre.', $session->fresh()->summary);

        Livewire::actingAs($this->alex)->test(SessionShow::class, ['campaign' => $this->campaign, 'playSession' => $session])->assertForbidden();
    }

    public function test_session_mode_opens_when_maps_are_switched_off(): void
    {
        CampaignFeatures::toggle($this->campaign, 'maps');

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign->fresh()])->assertOk();
    }

    public function test_an_object_joining_a_stack_is_announced_with_the_quantity_given(): void
    {
        $this->campaign->update(['exchanges_need_approval' => false]);
        $sam = User::factory()->create(['name' => 'Sam']);
        $this->campaign->members()->attach($sam, ['role' => CampaignRole::Player->value]);
        $jack = $this->campaign->playerCharacters()->create(['entity_id' => $this->sheet('Jack')->id, 'user_id' => $sam->id]);
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Corde']);
        app(GiveToCharacters::class)->handle($this->campaign, [$jack->id], ['kind' => 'possession', 'title' => 'Corde', 'quantity' => 2]);

        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey])
            ->call('startExchange', $this->harvey->grants()->sole()->id)
            ->set('exchangeTo', (string) $jack->id)
            ->call('exchange')
            ->assertHasNoErrors();

        $this->assertSame(3, $jack->grants()->sole()->quantity);
        $this->assertContains('Harvey vous a donné « Corde ».', $sam->fresh()->notifications->pluck('data.text'));
    }

    public function test_a_game_master_asked_to_approve_an_object_reads_give_and_the_quantity(): void
    {
        $sam = User::factory()->create(['name' => 'Sam']);
        $this->campaign->members()->attach($sam, ['role' => CampaignRole::Player->value]);
        $jack = $this->campaign->playerCharacters()->create(['entity_id' => $this->sheet('Jack')->id, 'user_id' => $sam->id]);
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Lanterne', 'quantity' => 2]);

        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey])
            ->call('startExchange', $this->harvey->grants()->sole()->id)
            ->set('exchangeTo', (string) $jack->id)
            ->set('exchangeQuantity', 1)
            ->call('exchange')
            ->assertHasNoErrors();

        $this->assertContains('Harvey propose de donner « Lanterne » à Jack : à valider.', $this->gm->fresh()->notifications->pluck('data.text'));
    }

    public function test_a_switched_off_feature_is_checked_again_on_every_livewire_action(): void
    {
        $this->assertContains(EnsureFeature::class, Livewire::getPersistentMiddleware());
    }

    public function test_a_missing_item_page_is_translated(): void
    {
        $this->gm->forceFill(['preferences' => ['locale' => 'en']])->save();

        $this->actingAs($this->gm)->get('/campagnes/999999')->assertNotFound()->assertSee('Page not found')->assertDontSee('Page introuvable');
    }

    public function test_a_remembered_login_from_parallel_requests_counts_once(): void
    {
        event(new Login('web', $this->gm, true));
        event(new Login('web', $this->gm, true));

        $this->assertSame(1, UserLogin::where('user_id', $this->gm->id)->count());
    }

    public function test_help_explains_the_complete_backup_and_campaign_features(): void
    {
        $this->get(route('help'))->assertOk()->assertSee(['Télécharger la sauvegarde complète', 'Fonctions de la campagne']);
    }

    public function test_a_spectator_is_not_sent_to_a_switched_off_table_screen(): void
    {
        $viewer = User::factory()->create();
        $this->campaign->members()->attach($viewer, ['role' => CampaignRole::Spectator->value]);
        CampaignFeatures::toggle($this->campaign, 'table');

        $this->actingAs($viewer)->get(route('campaigns.index'))->assertOk()->assertDontSee(route('table.screen', $this->campaign));
    }

    public function test_recettes_are_named_by_tested_version_and_imported_once_per_version(): void
    {
        Recette::create(['title' => 'Cahier de recette V1', 'version' => '0.13.1', 'tested_on' => '2026-10-08']);

        RecetteImport::all();

        $this->assertSame(1, Recette::where('version', '0.13.1')->count(), 'Un cahier renommé n’est pas ajouté une seconde fois.');
        $this->assertSame(['Recette v0.25.0', 'Recette v0.34.0'], Recette::where('version', '!=', '0.13.1')->orderBy('version')->pluck('title')->all());
    }
}
