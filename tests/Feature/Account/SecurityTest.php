<?php

namespace Tests\Feature\Account;

use App\Enums\CampaignRole;
use App\Livewire\Account\Profile;
use App\Livewire\Account\Security;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Members\Index as Members;
use App\Livewire\Messages\Index as Messages;
use App\Livewire\Timeline\Index as Timeline;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Models\UserLogin;
use App\Notifications\ConfirmNewEmail;
use App\Notifications\EmailChanged;
use App\Support\Notify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Sécurité des données des utilisateurs : droits revérifiés côté serveur, mots de passe,
 * double authentification, en-têtes, et « Mes données » (téléchargement, suppression).
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Morel le traître']);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    public function test_a_player_cannot_list_the_campaign_sheets_through_link_suggestions(): void
    {
        foreach ([Timeline::class, Messages::class] as $component) {
            $page = Livewire::actingAs($this->alex)->test($component, ['campaign' => $this->campaign]);
            $this->assertSame([], $page->instance()->suggestEntities('Morel'));
        }

        $gm = Livewire::actingAs($this->gm)->test(Timeline::class, ['campaign' => $this->campaign]);
        $this->assertSame(['Morel le traître'], array_column($gm->instance()->suggestEntities('Morel'), 'name'));
    }

    public function test_a_game_master_cannot_read_attachments_of_another_campaign(): void
    {
        $other = Campaign::factory()->create();
        $secret = Entity::factory()->for($other->owner, 'owner')->for($other)->create(['name' => 'Le secret d’un autre']);
        $message = new Message(['body' => 'Regarde ça']);
        $message->forceFill(['campaign_id' => $other->id, 'sender_id' => $other->user_id, 'entity_id' => $secret->id])->save();

        $page = Livewire::actingAs($this->gm)->test(Messages::class, ['campaign' => $this->campaign]);

        try {
            $page->instance()->reference($message);
            $this->fail('La pièce jointe d’une autre campagne ne doit pas être lue.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_a_removed_or_demoted_player_no_longer_plays_their_character(): void
    {
        Livewire::actingAs($this->gm)->test(Members::class, ['campaign' => $this->campaign])
            ->call('changeRole', $this->alex->id, CampaignRole::Spectator->value);
        $this->assertNull($this->harvey->fresh()->user_id);

        // Personnage confié de nouveau, puis joueur retiré : il redevient libre.
        $this->campaign->members()->updateExistingPivot($this->alex->id, ['role' => CampaignRole::Player->value]);
        $this->harvey->update(['user_id' => $this->alex->id]);
        Livewire::actingAs($this->gm)->test(Members::class, ['campaign' => $this->campaign])->call('remove', $this->alex->id);
        $this->assertNull($this->harvey->fresh()->user_id);
        $this->assertTrue($this->harvey->fresh()->is_active);
    }

    public function test_only_a_player_of_the_campaign_is_notified_for_their_character(): void
    {
        $this->actingAs($this->gm);
        $this->campaign->members()->updateExistingPivot($this->alex->id, ['role' => CampaignRole::Spectator->value]);
        Notify::player($this->harvey->fresh(), 'grant', 'Secret révélé', '/');
        $this->assertSame(0, $this->alex->notifications()->count());

        $this->campaign->members()->updateExistingPivot($this->alex->id, ['role' => CampaignRole::Player->value]);
        Notify::player($this->harvey->fresh(), 'grant', 'Secret révélé', '/');
        $this->assertSame(1, $this->alex->notifications()->count());
    }

    public function test_an_open_entity_form_rechecks_rights_on_save(): void
    {
        $coGm = User::factory()->create();
        $this->campaign->members()->attach($coGm, ['role' => CampaignRole::GameMaster->value]);
        $form = Livewire::actingAs($coGm)->test(EntityForm::class, ['campaign' => $this->campaign])->set('name', 'Intrus');

        $this->campaign->members()->detach($coGm);
        $form->call('save')->assertForbidden();
        $this->assertFalse(Entity::where('name', 'Intrus')->exists());
    }

    public function test_passwords_need_ten_characters_with_letters_and_numbers(): void
    {
        $this->post(route('register'), ['name' => 'Sam', 'email' => 'sam@exemple.fr', 'password' => 'motdepasse', 'password_confirmation' => 'motdepasse'])
            ->assertSessionHasErrors('password');
        $this->assertFalse(User::where('email', 'sam@exemple.fr')->exists());
    }

    public function test_account_forms_are_throttled_and_pages_send_security_headers(): void
    {
        // Cinq essais par formulaire et par adresse e-mail ; la page 429 dit combien attendre.
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), ['email' => 'x@exemple.fr']);
        }
        $this->post(route('password.email'), ['email' => 'x@exemple.fr'])->assertStatus(429)->assertSee('Réessayez dans');
        $this->post(route('password.email'), ['email' => 'y@exemple.fr'])->assertStatus(302);
        $this->post(route('login'), ['email' => 'x@exemple.fr', 'password' => 'faux'])->assertStatus(302);

        // Au-delà de vingt essais par minute depuis la même adresse IP, même avec des adresses différentes.
        for ($i = 0; $i < 14; $i++) {
            $this->post(route('password.email'), ['email' => "z{$i}@exemple.fr"]);
        }
        $this->post(route('password.email'), ['email' => 'w@exemple.fr'])->assertStatus(429);

        $this->get(route('login'))
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Seuls les scripts du site et ceux qui portent le nonce de la page s'exécutent.
        $page = $this->get(route('login'));
        preg_match("/script-src 'self' 'nonce-([^']+)'/", $page->headers->get('Content-Security-Policy'), $nonce);
        $this->assertNotEmpty($nonce);
        $page->assertSee('<script nonce="'.$nonce[1].'">', false)->assertHeaderMissing('X-Powered-By');
    }

    public function test_two_factor_authentication_protects_the_login(): void
    {
        $page = Livewire::actingAs($this->alex)->test(Security::class)
            ->set('password', 'faux')->call('enable')->assertHasErrors('password')
            ->set('password', 'password')->call('enable')->assertHasNoErrors();

        $secret = decrypt($this->alex->fresh()->two_factor_secret);
        $page->set('code', '000000')->call('confirm')->assertHasErrors('code');
        $page->set('code', app(Google2FA::class)->getCurrentOtp($secret))->call('confirm')->assertHasNoErrors()->assertSee('Vos codes de secours');
        $this->assertNotNull($this->alex->fresh()->two_factor_confirmed_at);

        auth()->logout();
        $this->flushSession();
        $this->post(route('login'), ['email' => $this->alex->email, 'password' => 'password'])->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
        $this->get(route('two-factor.login'))->assertSee('application d’authentification');

        $recovery = $this->alex->fresh()->recoveryCodes()[0];
        $this->post(route('two-factor.login.store'), ['recovery_code' => $recovery])->assertRedirect(route('campaigns.index'));
        $this->assertAuthenticatedAs($this->alex);
    }

    public function test_a_user_downloads_their_data_without_secrets(): void
    {
        $this->alex->forceFill(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-ant-une-cle-tres-secrete-123'])->save();

        $response = $this->actingAs($this->alex)->get(route('account.data'))->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="sagawyn-mes-donnees.json"');
        $data = json_decode($response->getContent(), true);

        $this->assertSame($this->alex->email, $data['account']['email']);
        $this->assertTrue($data['account']['ai_api_key_saved']);
        $this->assertSame([['name' => 'Les Ombres', 'role' => 'player', 'owner' => false]], $data['campaigns']);
        $this->assertSame('Harvey', $data['characters'][0]['name']);
        $this->assertStringNotContainsString('sk-ant', $response->getContent());
        $this->assertStringNotContainsString($this->alex->password, $response->getContent());
    }

    public function test_a_user_deletes_their_account_and_their_campaigns(): void
    {
        $this->alex->forceFill(['is_admin' => true])->save();
        $page = Livewire::actingAs($this->gm)->test(Security::class)
            ->set('deletePassword', 'faux')->set('deleteConfirmed', true)->call('deleteAccount')->assertHasErrors('deletePassword');
        $this->assertModelExists($this->gm);

        $page->set('deletePassword', 'password')->set('deleteConfirmed', false)->call('deleteAccount')->assertHasErrors('deleteConfirmed');

        $page->set('deleteConfirmed', true)->call('deleteAccount')->assertRedirect(route('login'));
        $this->assertModelMissing($this->gm);
        $this->assertModelMissing($this->campaign);
        $this->assertSame(0, Entity::count());
        $this->assertModelExists($this->alex);
    }

    public function test_a_deleted_player_leaves_their_character_without_a_player(): void
    {
        Livewire::actingAs($this->alex)->test(Security::class)
            ->set('deletePassword', 'password')->set('deleteConfirmed', true)->call('deleteAccount')->assertHasNoErrors();

        $this->assertModelMissing($this->alex);
        $this->assertNull($this->harvey->fresh()->user_id);
    }

    public function test_the_only_administrator_or_a_paying_subscriber_cannot_delete_their_account(): void
    {
        $this->assertTrue($this->gm->fresh()->is_admin);
        $this->gm->forceFill(['is_admin' => true])->save();
        $this->alex->forceFill(['is_admin' => false])->save();
        Livewire::actingAs($this->gm)->test(Security::class)
            ->set('deletePassword', 'password')->set('deleteConfirmed', true)->call('deleteAccount')->assertHasErrors('deletePassword');
        $this->assertModelExists($this->gm);

        $this->alex->forceFill(['subscription_status' => 'active'])->save();
        Livewire::actingAs($this->alex)->test(Security::class)
            ->set('deletePassword', 'password')->set('deleteConfirmed', true)->call('deleteAccount')->assertHasErrors('deletePassword');
        $this->assertModelExists($this->alex);
    }

    public function test_privacy_page_is_public_and_old_logins_are_pruned(): void
    {
        config(['codexflow.legal.owner' => 'Morgan Bioux', 'codexflow.legal.email' => 'contact@exemple.fr']);
        $this->get(route('privacy'))->assertOk()->assertSee(['Autistic Intelligence', 'Morgan Bioux', 'contact@exemple.fr', 'CNIL']);

        UserLogin::create(['user_id' => $this->alex->id, 'logged_in_at' => now()->subMonths(13)]);
        UserLogin::create(['user_id' => $this->alex->id, 'logged_in_at' => now()->subMonth()]);
        $this->artisan('model:prune', ['--model' => [UserLogin::class]])->assertSuccessful();
        $this->assertSame(1, UserLogin::count());
    }

    public function test_a_user_changes_their_name_email_and_password(): void
    {
        Notification::fake();
        $old = $this->alex->email;

        // Le nom seul se change sans mot de passe.
        Livewire::actingAs($this->alex)->test(Profile::class)
            ->set('name', 'Alexandra')->call('saveProfile')->assertHasNoErrors();
        $this->assertSame('Alexandra', $this->alex->fresh()->name);

        // L'adresse demande le mot de passe actuel et reste unique ; elle ne change qu'après le lien
        // de confirmation reçu à la nouvelle adresse, puis l'ancienne adresse est prévenue.
        $page = Livewire::actingAs($this->alex)->test(Profile::class)
            ->set('email', $this->gm->email)->set('currentPassword', 'password')->call('saveProfile')->assertHasErrors('email')
            ->set('email', 'Alex.Nouvelle@Exemple.fr')->set('currentPassword', '')->call('saveProfile')->assertHasErrors('currentPassword')
            ->set('currentPassword', 'faux')->call('saveProfile')->assertHasErrors('currentPassword')
            ->set('currentPassword', 'password')->call('saveProfile')->assertHasNoErrors()
            ->assertSet('email', $old)->assertSee('Un lien de confirmation a été envoyé à alex.nouvelle@exemple.fr.');
        $this->assertSame($old, $this->alex->fresh()->email);
        Notification::assertSentOnDemandTimes(EmailChanged::class, 0);

        $url = null;
        Notification::assertSentOnDemand(ConfirmNewEmail::class, function ($notification, $channels, $notifiable) use (&$url) {
            $url = $notification->toMail($notifiable)->actionUrl;

            return $notifiable->routes['mail'] === 'alex.nouvelle@exemple.fr';
        });

        // Le lien est signé : changer l'adresse qu'il contient le rend invalide, et il ne sert qu'à son titulaire.
        $this->actingAs($this->alex)->get(str_replace('alex.nouvelle', 'pirate', $url))->assertForbidden();
        $this->actingAs($this->gm)->get($url)->assertForbidden();
        $this->actingAs($this->alex)->get($url)->assertRedirect(route('preferences'));
        $this->assertSame('alex.nouvelle@exemple.fr', $this->alex->fresh()->email);
        Notification::assertSentOnDemand(EmailChanged::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $old);

        // Le mot de passe suit la règle commune.
        $page->set('currentPassword', 'password')->set('password', 'court')->set('password_confirmation', 'court')->call('savePassword')->assertHasErrors('password')
            ->set('password', 'nouveau-secret-42')->set('password_confirmation', 'autre-chose-42')->call('savePassword')->assertHasErrors('password')
            ->set('password_confirmation', 'nouveau-secret-42')->call('savePassword')->assertHasNoErrors();
        $this->assertTrue(Hash::check('nouveau-secret-42', $this->alex->fresh()->password));

        $this->flushSession();
        $this->actingAs($this->alex->fresh())->get(route('preferences'))->assertSee(['Mon compte', 'Changer le mot de passe']);
    }
}
