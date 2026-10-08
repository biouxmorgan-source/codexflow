<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Actions\Characters\PlayerAdditions;
use App\Actions\Duplication\DuplicateCampaign;
use App\Actions\Duplication\DuplicateScenario;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\EntityTypes\Manage as EntityTypes;
use App\Livewire\ReceivedPopup;
use App\Livewire\Sessions\Live;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\Secret;
use App\Models\Tag;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Support\Search\GlobalSearch;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Corrections issues de la campagne de tests V1 (cahier de recette).
 */
class RecetteV1Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private Entity $morel;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $this->morel = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Morel']);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    private function secret(string $title, string $body = ''): Secret
    {
        $secret = new Secret(['title' => $title, 'body' => $body]);
        $secret->campaign()->associate($this->campaign);
        $secret->owner()->associate($this->gm);
        $secret->save();

        return $secret;
    }

    private function note(string $body, string $visibility): void
    {
        $note = $this->harvey->notes()->make(['body' => $body, 'visibility' => $visibility]);
        $note->user_id = $this->alex->id;
        $note->save();
    }

    /** @return list<string> titres trouvés par le MJ, toutes catégories confondues */
    private function gmFinds(string $query): array
    {
        return collect((new GlobalSearch($this->campaign, $this->gm, $query))->run())
            ->flatten(1)->map->title->all();
    }

    public function test_the_game_master_search_covers_secrets_given_items_player_notes_and_scene_tags(): void
    {
        $this->secret('Le pacte d’Ambre', 'Morel sert le culte ambrecult.');
        $this->harvey->grants()->create(['kind' => 'possession', 'title' => 'Lampe tempête']);
        $this->note('Morel cache un grimoire', 'gm');
        $this->note('Mon journal intime grimoire', 'private');
        $scene = $this->campaign->scenarios()->create(['name' => 'Acte I', 'position' => 1])->scenes()->create(['name' => 'Le quai', 'position' => 1]);
        $scene->tags()->sync(Tag::idsFromInput($this->gm, 'ouverture'));

        $this->assertSame(['Le pacte d’Ambre'], $this->gmFinds('ambrecult'));
        $this->assertSame(['Lampe tempête'], $this->gmFinds('lampe'));
        $this->assertSame(['Note de Harvey'], $this->gmFinds('grimoire'), 'Une note « Moi seul » reste privée, même pour le MJ.');
        $this->assertSame(['Le quai'], $this->gmFinds('ouverture'));

        // Le joueur ne trouve pas le secret : son personnage ne le connaît pas.
        $this->assertSame([], collect((new GlobalSearch($this->campaign, $this->alex, 'ambrecult'))->run())->flatten(1)->all());
    }

    public function test_duplicating_a_campaign_copies_its_secrets_and_prepared_timeline(): void
    {
        $this->actingAs($this->gm);
        $scene = $this->campaign->scenarios()->create(['name' => 'Acte I', 'position' => 1])->scenes()->create(['name' => 'Le quai', 'position' => 1]);
        $secret = $this->secret('Le pacte d’Ambre');
        $secret->entities()->attach($this->morel);
        $secret->scenes()->attach($scene);
        $this->harvey->grants()->create(['kind' => 'information', 'title' => 'Le pacte d’Ambre', 'secret_id' => $secret->id]);

        foreach (['world' => 'La grande crue', 'planned' => 'Le bal', 'played' => 'Harvey fuit'] as $kind => $title) {
            $event = new TimelineEvent(['kind' => $kind, 'title' => $title, 'zone' => Zone::GameMaster]);
            $event->campaign()->associate($this->campaign);
            $event->user_id = $this->gm->id;
            $event->position = TimelineEvent::count() + 1;
            $event->scene_id = $kind === 'planned' ? $scene->id : null;
            $event->save();
        }

        $copy = app(DuplicateCampaign::class)->handle($this->campaign, $this->gm);

        $copied = $copy->secrets()->with(['entities', 'scenes'])->sole();
        $this->assertSame('Le pacte d’Ambre', $copied->title);
        $this->assertSame(['Morel'], $copied->entities->pluck('name')->all());
        $this->assertNotSame($this->morel->id, $copied->entities->first()->id, 'La fiche de campagne copiée, pas l’originale.');
        $this->assertSame(['Le quai'], $copied->scenes->pluck('name')->all());
        $this->assertSame(0, $copied->grants()->count(), 'Personne ne connaît encore le secret dans la copie.');

        $events = $copy->timelineEvents()->orderBy('position')->get();
        $this->assertSame(['La grande crue', 'Le bal'], $events->pluck('title')->all());
        $this->assertSame($copied->scenes->first()->id, $events[1]->scene_id);

        // Dupliquer un scénario garde les secrets de ses scènes.
        $scenarioCopy = app(DuplicateScenario::class)->handle($scene->scenario);
        $this->assertSame([$secret->id], $scenarioCopy->scenes()->sole()->secrets()->pluck('secrets.id')->all());
    }

    public function test_error_pages_are_translated_and_players_land_on_their_own_page(): void
    {
        $this->get('/page-qui-n-existe-pas', ['Accept-Language' => 'de-DE'])->assertNotFound()->assertSee('Seite nicht gefunden');

        $stranger = User::factory()->create(['preferences' => ['locale' => 'fr']]);
        $this->actingAs($stranger)->get(route('campaigns.show', $this->campaign))
            ->assertForbidden()->assertSee('Accès refusé')->assertSee('Mes campagnes')->assertDontSee('This action is unauthorized');

        // Un joueur qui ouvre la page MJ de la campagne arrive sur la fiche de son personnage.
        $this->actingAs($this->alex)->get(route('campaigns.show', $this->campaign))
            ->assertRedirect(route('characters.show', [$this->campaign, $this->harvey]));

        $spectator = User::factory()->create();
        $this->campaign->members()->attach($spectator, ['role' => CampaignRole::Spectator->value]);
        $this->actingAs($spectator)->get(route('campaigns.show', $this->campaign))->assertRedirect(route('table.screen', $this->campaign));
    }

    public function test_forgotten_password_does_not_tell_whether_an_account_exists_and_the_mail_is_in_french(): void
    {
        Notification::fake();

        $unknown = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'personne@exemple.test']);
        $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => $this->alex->email]);

        $unknown->assertRedirect('/forgot-password')->assertSessionHasNoErrors();
        $this->assertSame(session('status'), __('passwords.sent'));
        $known->assertSessionHasNoErrors();

        Notification::assertSentTo($this->alex, ResetPassword::class, function (ResetPassword $notification) {
            app()->setLocale('fr');
            $mail = $notification->toMail($this->alex);

            return $mail->subject === 'Réinitialisez votre mot de passe' && $mail->actionText === 'Réinitialiser le mot de passe';
        });
    }

    public function test_unchanged_counters_are_not_written_to_the_journal(): void
    {
        $hp = $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'PV', 'type' => FieldType::Counter, 'zone' => Zone::Public, 'player_editable' => true,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
        $money = $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => 'Argent', 'type' => FieldType::Text, 'zone' => Zone::Public, 'player_editable' => true,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
        $this->harvey->entity->setFieldValues([$hp->id => ['value' => 10, 'max' => 12]]);
        $this->harvey->entity->save();

        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey])
            ->call('edit')->set('values.'.$money->id, '12 pièces')->call('save')->assertHasNoErrors();

        $entry = ActivityLog::where('subject_type', 'entity')->where('event', 'updated')->where('user_id', $this->alex->id)->sole();
        $this->assertSame(['field_values.'.$money->id], array_keys($entry->diff));
    }

    public function test_a_resting_character_and_validated_objects_are_read_only_for_the_player(): void
    {
        $dagger = app(PlayerAdditions::class);
        $this->actingAs($this->alex);
        $dagger->add($this->harvey, 'possession', 'Dague', null, 1);
        $grant = $this->harvey->grants()->sole();
        $this->actingAs($this->gm);
        $dagger->validate($grant);

        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey])
            ->assertDontSee('Effacer « Dague »')
            ->call('removeOwn', $grant->id)->assertForbidden();
        $this->assertModelExists($grant);

        $this->harvey->update(['is_active' => false]);
        $this->assertFalse($this->alex->can('play', $this->harvey->fresh()));
        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey->fresh()])
            ->call('openAdd', 'information')->assertForbidden();
        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey->fresh()])
            ->set('noteBody', 'Encore une note')->call('saveNote')->assertForbidden();
    }

    public function test_entity_type_counts_only_include_my_own_sheets(): void
    {
        $other = User::factory()->create();
        Entity::factory()->count(3)->for($other, 'owner')->create(['entity_type_id' => EntityType::standard('character')->id]);

        // Morel et Harvey seulement, pas les trois fiches de l'autre compte.
        Livewire::actingAs($this->gm)->test(EntityTypes::class)->assertSee('2 fiches')->assertDontSee('5 fiches');
    }

    public function test_a_relation_without_reverse_label_is_not_read_backwards(): void
    {
        $inn = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Auberge']);
        $relation = new EntityRelation(['label' => 'surveille', 'zone' => Zone::Public]);
        $relation->from()->associate($this->morel);
        $relation->to()->associate($inn);
        $relation->user_id = $this->gm->id;
        $relation->save();

        $this->assertSame('surveille', $relation->labelFrom($this->morel));
        $this->assertSame('« surveille » par', $relation->labelFrom($inn));

        $relation->update(['reverse_label' => 'est surveillée par']);
        $this->assertSame('est surveillée par', $relation->fresh()->labelFrom($inn));
    }

    public function test_a_sheet_linked_in_the_session_opens_in_a_side_panel(): void
    {
        $elsewhere = Entity::factory()->create(['name' => 'Ailleurs']);

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('openPreview', $this->morel->id)
            ->assertSet('previewId', $this->morel->id)
            ->assertSee('Ouvrir la fiche')
            ->call('openPreview', $elsewhere->id)
            ->assertDontSee('Ailleurs');

        Livewire::actingAs($this->alex)->test(Live::class, ['campaign' => $this->campaign])->assertForbidden();
    }

    public function test_the_received_popup_closes_when_its_target_is_the_current_page(): void
    {
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'information', 'title' => 'Morel ment', 'body' => 'Il était au manoir.']);
        $notification = $this->alex->unreadNotifications()->sole();
        $page = route('characters.show', [$this->campaign, $this->harvey]);

        Livewire::actingAs($this->alex)
            ->test(ReceivedPopup::class)
            ->assertSee('Morel')
            ->call('open', $notification->id, parse_url($page, PHP_URL_PATH))
            ->assertNoRedirect()
            ->assertDontSee('Morel');

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
