<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Actions\Characters\TransferGrants;
use App\Actions\Demo\LoadDemoCampaign;
use App\Actions\Messages\SendMessage;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Campaigns\Index;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Characters\Index as CharactersIndex;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\Fields\Manage as FieldsManage;
use App\Livewire\Members\Index as Members;
use App\Livewire\Messages\Index as Messages;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\ExchangeRequest;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Notifications\Channels\PushChannel;
use App\Support\Archive\CampaignExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Livewire\Livewire;
use NotificationChannels\WebPush\WebPushChannel;
use Tests\TestCase;

/**
 * Bugs relevés par la recette v0.25.0 (cahier database/data/recettes/v2.json), un test par bug.
 */
class RecetteV2Test extends TestCase
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
        $this->harvey = $this->character('Harvey', $this->alex);
    }

    private function character(string $name, ?User $player): PlayerCharacter
    {
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $name, 'entity_type_id' => EntityType::standard('character')->id]);

        return $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $player?->id]);
    }

    public function test_an_open_page_stops_answering_once_the_player_is_removed(): void
    {
        $sheet = Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey]);
        $sheet->call('$refresh')->assertOk();

        $this->campaign->members()->updateExistingPivot($this->alex->id, ['role' => CampaignRole::Spectator->value]);
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'information', 'title' => 'Après le départ', 'body' => 'Secret']);

        $this->actingAs($this->alex);
        $sheet->call('$refresh')->assertForbidden();
    }

    public function test_an_open_game_master_page_stops_answering_once_the_co_gm_is_demoted(): void
    {
        $coGm = User::factory()->create();
        $this->campaign->members()->attach($coGm, ['role' => CampaignRole::GameMaster->value]);
        $morel = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Morel']);

        $page = Livewire::actingAs($coGm)->test(EntityShow::class, ['campaign' => $this->campaign, 'entity' => $morel]);
        $page->call('$refresh')->assertOk();

        $this->campaign->members()->updateExistingPivot($coGm->id, ['role' => CampaignRole::Player->value]);
        $page->call('$refresh')->assertForbidden();
    }

    public function test_a_push_service_that_cannot_start_never_breaks_the_action(): void
    {
        $this->app->bind(WebPushChannel::class, fn () => throw new \ErrorException('Install the GMP or BCMath extension.'));

        $this->assertSame([], app(PushChannel::class)->send($this->alex, new class extends Notification {}));
    }

    public function test_the_received_popup_opens_the_sheet_through_alpine(): void
    {
        $view = file_get_contents(resource_path('views/livewire/received-popup.blade.php'));

        // wire:click ne connaît pas « window » : le bouton ne faisait rien.
        $this->assertStringContainsString('x-on:click="$wire.open(', $view);
        $this->assertStringNotContainsString('wire:click="open(', $view);
    }

    public function test_demo_pregenerated_characters_are_offered_to_players(): void
    {
        $campaign = app(LoadDemoCampaign::class)->handle($this->gm, 'fr');
        $names = Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $campaign])->get('candidates')->pluck('name');

        foreach (['teska', 'oriel', 'dorn', 'lisenn'] as $key) {
            $this->assertContains(LoadDemoCampaign::text('fr')['entities'][$key]['name'], $names);
        }
    }

    public function test_a_new_player_does_not_read_the_previous_players_private_conversation(): void
    {
        app(SendMessage::class)->handle($this->campaign, $this->alex, [$this->harvey->id], 'Confidence d’Alex');
        $this->travel(1)->minutes();

        $sam = User::factory()->create();
        $this->campaign->members()->attach($sam, ['role' => CampaignRole::Player->value]);
        Livewire::actingAs($this->gm)->test(Members::class, ['campaign' => $this->campaign])->call('remove', $this->alex->id);
        $this->harvey->fresh()->update(['user_id' => $sam->id]);
        $this->travel(1)->minutes();
        app(SendMessage::class)->handle($this->campaign, $this->gm, [$this->harvey->id], 'Bienvenue Sam');

        Livewire::actingAs($sam)->test(Messages::class, ['campaign' => $this->campaign])
            ->assertSee('Bienvenue Sam')
            ->assertDontSee('Confidence d’Alex');
        Livewire::actingAs($this->gm)->test(Messages::class, ['campaign' => $this->campaign])
            ->set('conversation', (string) $this->harvey->id)
            ->assertSee(['Bienvenue Sam', 'Confidence d’Alex']);
    }

    public function test_an_exchange_shows_the_quantity_offered_not_the_whole_stack(): void
    {
        $jack = $this->character('Jack', User::factory()->create());
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'possession', 'title' => 'Lampe', 'quantity' => 2]);
        $lamp = $this->harvey->grants()->sole();

        $request = new ExchangeRequest(['quantity' => 1]);
        $request->setRelation('grant', $lamp);
        $this->assertSame('Lampe', $request->label());
        $request->quantity = 2;
        $this->assertSame('Lampe ×2', $request->label());
        $this->assertNotNull($jack);
    }

    public function test_a_player_reference_field_does_not_reveal_unknown_sheets(): void
    {
        $ally = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Allié', 'type' => FieldType::EntityRef, 'zone' => Zone::Public, 'position' => 1, 'player_editable' => true, 'entity_type_id' => EntityType::standard('character')->id]);
        $ysane = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Dame Ysane Korr']);

        $sheet = fn () => Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey]);
        $sheet()->set("values.{$ally->id}", '[[dame ysane korr]]')->call('save')->assertHasNoErrors();
        $this->assertSame('[[dame ysane korr]]', $this->harvey->entity->fresh()->fieldValue($ally));

        // Une fois la fiche connue du personnage, le lien se fait.
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $ysane->id]);
        $sheet()->set("values.{$ally->id}", 'dame ysane korr')->call('save')->assertHasNoErrors();
        $this->assertSame("[[Dame Ysane Korr|{$ysane->id}]]", $this->harvey->entity->fresh()->fieldValue($ally));
    }

    public function test_notes_of_a_previous_character_have_no_edit_buttons(): void
    {
        $note = $this->harvey->notes()->make(['body' => 'Note de Harvey', 'visibility' => 'gm']);
        $note->author()->associate($this->alex)->save();
        $this->harvey->update(['is_active' => false]);
        $jack = $this->character('Jack', $this->alex);

        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $jack])
            ->assertSee('Note de Harvey')
            ->assertDontSee('wire:click="editNote(', false);
    }

    public function test_unknown_roles_and_hidden_files_are_refused_cleanly(): void
    {
        Livewire::actingAs($this->gm)->test(Members::class, ['campaign' => $this->campaign])
            ->call('changeRole', $this->alex->id, 'admin')
            ->assertStatus(422);

        // Un compte étranger reçoit 403 que la feuille existe ou non.
        $this->actingAs(User::factory()->create())
            ->get(route('characters.sheet', [$this->campaign, $this->harvey]))->assertForbidden();
        $this->get(route('characters.portrait', [$this->campaign, $this->harvey]))->assertForbidden();
    }

    public function test_a_player_without_character_is_sent_to_their_campaigns(): void
    {
        $sam = User::factory()->create();
        $this->campaign->members()->attach($sam, ['role' => CampaignRole::Player->value]);

        Livewire::actingAs($sam)->test(CampaignShow::class, ['campaign' => $this->campaign])
            ->assertRedirect(route('campaigns.index'));
    }

    public function test_taking_over_from_an_old_character_is_logged_as_a_transfer(): void
    {
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'information', 'title' => 'Le mot de passe']);
        $jack = $this->character('Jack', null);
        ActivityLog::query()->delete();

        app(TransferGrants::class)->handle($this->harvey, $jack, $this->harvey->grants()->pluck('id')->all());

        $log = ActivityLog::sole();
        $this->assertTrue($log->isExchange());
        $this->assertSame('a transmis', $log->verb('fr'));
    }

    public function test_recovery_codes_work_without_javascript_and_fortify_speaks_french(): void
    {
        $this->alex->forceFill(['two_factor_secret' => encrypt('SECRET'), 'two_factor_recovery_codes' => encrypt(json_encode(['code-1'])), 'two_factor_confirmed_at' => now()])->save();

        $this->post(route('login'), ['email' => $this->alex->email, 'password' => 'password']);
        $page = $this->get(route('two-factor.login'))->assertOk();
        $page->assertSee('name="recovery_code"', false)->assertDontSee('x-show="recovery"', false);

        $this->post(route('two-factor.login.store'), ['recovery_code' => 'faux'])
            ->assertSessionHasErrors(['recovery_code' => 'Ce code de secours n’est pas valide (chaque code ne sert qu’une fois).']);
    }

    public function test_an_administrator_removes_two_factor_from_a_locked_out_account(): void
    {
        $this->gm->forceFill(['is_admin' => true])->save();
        $this->alex->forceFill(['two_factor_secret' => encrypt('SECRET'), 'two_factor_confirmed_at' => now()])->save();

        Livewire::actingAs($this->gm)->test(AdminUsers::class)
            ->call('edit', $this->alex->id)
            ->assertSee('Retirer la double authentification')
            ->call('disableTwoFactor', $this->alex->id);

        $this->assertNull($this->alex->fresh()->two_factor_confirmed_at);
        Livewire::actingAs($this->alex)->test(AdminUsers::class)->assertForbidden();
    }

    public function test_icons_carry_the_sagawyn_name(): void
    {
        foreach (['favicon.ico', 'icons/icon-192.png', 'icons/icon-512.png', 'icons/apple-touch-icon.png', 'icons/badge-96.png'] as $file) {
            $this->assertNotSame(
                ['favicon.ico' => '6590', 'icons/icon-192.png' => '2712', 'icons/icon-512.png' => '7726', 'icons/apple-touch-icon.png' => '2352', 'icons/badge-96.png' => '1124'][$file],
                (string) filesize(public_path($file)),
                "{$file} est encore l’icône CodexFlow."
            );
        }
    }

    public function test_a_reference_survives_a_rename_and_a_new_save(): void
    {
        $mentor = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Mentor', 'type' => FieldType::EntityRef, 'zone' => Zone::Public, 'position' => 1]);
        $guild = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Guilde des Passeurs']);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Garn']);
        $sheet->setFieldValues([$mentor->id => "[[Guilde des Passeurs|{$guild->id}]]"]);
        $sheet->save();
        $guild->update(['name' => 'Guilde des Bateliers']);

        Livewire::actingAs($this->gm)->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $sheet])
            ->assertSet("fields.{$mentor->id}", 'Guilde des Bateliers')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame("[[Guilde des Bateliers|{$guild->id}]]", $sheet->fresh()->fieldValue($mentor));
    }

    public function test_field_arrows_move_within_the_group(): void
    {
        $fields = $this->campaign->gameSystem->fieldDefinitions();
        $strength = $fields->create(['name' => 'Force', 'group' => 'Carac', 'type' => FieldType::Number, 'zone' => Zone::Public, 'position' => 0]);
        $fields->create(['name' => 'Allégeance', 'group' => 'Profil', 'type' => FieldType::Text, 'zone' => Zone::Public, 'position' => 1]);
        $hp = $fields->create(['name' => 'PV', 'group' => 'Carac', 'type' => FieldType::Number, 'zone' => Zone::Public, 'position' => 2]);

        Livewire::actingAs($this->gm)->test(FieldsManage::class, ['campaign' => $this->campaign])->call('move', $hp->id, -1);

        $this->assertLessThan($strength->fresh()->position, $hp->fresh()->position);
    }

    public function test_campaign_creation_errors_are_readable(): void
    {
        Livewire::actingAs(User::factory()->create())->test(Index::class)
            ->set('creating', true)
            ->set('name', 'Nouvelle')
            ->set('gameChoice', 'new')
            ->call('create')
            ->assertSee('Donnez un nom au nouveau jeu.')
            ->assertDontSee('vaut new');
    }

    public function test_a_shared_game_template_keeps_link_names_but_not_campaign_ids(): void
    {
        $garn = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Garn']);
        $rule = $this->campaign->gameSystem->rules()->make(['title' => 'Marchander', 'procedure' => "Voir [[Garn|{$garn->id}]].", 'zone' => Zone::Public]);
        $rule->forceFill(['user_id' => $this->gm->id])->save();

        $template = json_encode(CampaignExport::template($this->campaign, withRules: true), JSON_UNESCAPED_UNICODE);

        $this->assertStringContainsString('Voir [[Garn]].', $template);
        $this->assertStringNotContainsString("|{$garn->id}]]", $template);
    }
}
