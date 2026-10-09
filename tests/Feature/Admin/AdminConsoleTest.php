<?php

namespace Tests\Feature\Admin;

use App\Enums\CampaignRole;
use App\Livewire\Admin\Backlog;
use App\Livewire\Admin\Evolutions;
use App\Livewire\Admin\PlanSettings;
use App\Livewire\Admin\Recettes;
use App\Livewire\Admin\Users;
use App\Livewire\Campaigns\Index as CampaignIndex;
use App\Livewire\Documents\Index as DocumentIndex;
use App\Models\BugReport;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Evolution;
use App\Models\Recette;
use App\Models\User;
use App\Models\UserLogin;
use App\Support\Plans\Plans;
use App\Support\RecetteImport;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminConsoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Le premier compte de l'installation l'administre.
        $this->admin = User::factory()->create(['name' => 'Morgan', 'email' => 'morgan@example.com']);
        // Ces tests portent sur la formule gratuite elle-même, hors essai.
        Plans::save(['trial_weeks' => 0]);
    }

    public function test_only_the_administrator_opens_the_console(): void
    {
        $user = User::factory()->create();

        // La console redemande le mot de passe de l'administrateur.
        $this->actingAs($this->admin)->get(route('admin.users'))->assertRedirect(route('password.confirm'));
        $this->withSession(['auth.password_confirmed_at' => time()]);

        foreach (['admin.users', 'admin.plans', 'admin.backlog', 'admin.recettes'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
            $this->actingAs($this->admin)->get(route($route))->assertOk()->assertSee('Administration');
        }

        $this->actingAs($user)->get(route('campaigns.index'))->assertDontSee(route('admin.users'));
        $this->actingAs($this->admin)->get(route('campaigns.index'))->assertSee(route('admin.users'));
    }

    public function test_the_console_lists_accounts_with_indicators_and_nothing_personal(): void
    {
        $gm = User::factory()->create(['name' => 'Camille Secret', 'email' => 'camille@example.com']);
        $gm->forceFill(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-ant-secret-1234567890'])->save();
        $campaign = Campaign::factory()->for($gm, 'owner')->create();
        $player = User::factory()->create(['email' => 'joueur@example.com']);
        $campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        // Connexion : seule la date est gardée.
        $this->post(route('login'), ['email' => 'camille@example.com', 'password' => 'password']);
        $this->post(route('logout'));
        UserLogin::create(['user_id' => $gm->id, 'logged_in_at' => now()->subDays(60)]);
        $this->assertSame(['id', 'user_id', 'logged_in_at'], array_keys(UserLogin::where('user_id', $gm->id)->first()->getAttributes()));

        $console = Livewire::actingAs($this->admin)->test(Users::class)
            ->assertSee(['camille@example.com', 'joueur@example.com', 'Gratuit', 'Administrateur'])
            ->assertDontSee('Camille Secret')
            ->assertDontSee('sk-ant-secret');

        $row = $console->instance()->rows->firstWhere('user.id', $gm->id);
        $this->assertTrue($row['ai']);
        $this->assertSame(1, $row['campaigns']);
        $this->assertSame(1, $row['logins']);
        $this->assertSame(250 * 1024 * 1024, $row['limit']);
        $this->assertSame(1, $console->instance()->rows->firstWhere('user.id', $player->id)['memberships']);

        $console->set('period', 90);
        $this->assertSame(2, $console->instance()->rows->firstWhere('user.id', $gm->id)['logins']);

        $console->set('search', 'joueur')->assertSee('joueur@example.com')->assertDontSee('camille@example.com');
    }

    public function test_the_administrator_sets_a_plan_and_sends_a_reset_link_without_seeing_the_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'camille@example.com']);

        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $user->id)
            ->set('plan', 'premium')
            ->set('planStartedAt', '2026-10-01')
            ->set('planEndsAt', '2027-09-30')
            ->set('storageQuotaMb', '5000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Compte mis à jour : camille@example.com.')
            ->call('sendResetLink', $user->id)
            ->assertSee('Lien de réinitialisation envoyé à camille@example.com.')
            ->assertDontSee('password');

        $user->refresh();
        $this->assertSame('premium', Plans::effective($user));
        $this->assertSame('2027-09-30', $user->plan_ends_at->toDateString());
        $this->assertSame(5000 * 1024 * 1024, Plans::storageLimit($user));
        Notification::assertSentTo($user, ResetPassword::class);

        // L'administrateur ne se retire pas lui-même l'administration.
        Livewire::actingAs($this->admin)->test(Users::class)->call('edit', $this->admin->id)->set('isAdmin', false)->call('save');
        $this->assertTrue($this->admin->fresh()->is_admin);

        // Une formule premium échue redevient gratuite.
        $user->forceFill(['plan_ends_at' => now()->subDay()])->save();
        $this->assertSame('free', Plans::effective($user->fresh()));
    }

    public function test_a_free_account_owns_one_campaign_but_can_play_in_others(): void
    {
        $free = User::factory()->create();
        $other = Campaign::factory()->for($this->admin, 'owner')->create();
        $other->members()->attach($free, ['role' => CampaignRole::Player->value]);

        $create = fn () => Livewire::actingAs($free)->test(CampaignIndex::class)
            ->set('creating', true)->set('name', 'Ma campagne')->set('gameChoice', 'new')->set('newGameName', 'Mon jeu')
            ->call('create');

        $create()->assertHasNoErrors();
        $create()->assertHasErrors('plan')->assertSee('Votre formule permet 1 campagne en tant que MJ.');
        $this->assertSame(1, $free->ownedCampaigns()->count());

        Livewire::actingAs($this->admin)->test(PlanSettings::class)->set('freeMaxCampaigns', 2)->call('save')->assertSee('Formules enregistrées.');
        $create()->assertHasNoErrors();

        $free->forceFill(['plan' => 'premium'])->save();
        $create()->assertHasNoErrors();
        $this->assertSame(3, $free->ownedCampaigns()->count());
    }

    public function test_uploads_stop_at_the_storage_quota_of_the_campaign_owner(): void
    {
        Storage::fake(Document::DISK);
        $gm = User::factory()->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create();
        $gm->forceFill(['storage_quota_mb' => 1])->save();

        $upload = fn (int $kilobytes) => Livewire::actingAs($gm)->test(DocumentIndex::class, ['campaign' => $campaign])
            ->set('uploads', [UploadedFile::fake()->create('plan.pdf', $kilobytes, 'application/pdf')])
            ->call('saveUploads');

        $upload(600)->assertHasNoErrors();
        $upload(600)->assertHasErrors('uploads')->assertSee('Espace de stockage insuffisant');
        $this->assertSame(1, Document::count());

        $this->actingAs($gm)->get(route('preferences'))->assertSee('Votre formule : Gratuit')->assertSee('600 Ko / 1,0 Mo');
    }

    public function test_a_feature_removed_from_the_free_plan_disappears_for_the_gm_and_the_players(): void
    {
        $gm = User::factory()->create();
        $player = User::factory()->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create();
        $campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $campaign->forceFill(['table_shared' => true])->save();

        $this->actingAs($gm)->get(route('maps.index', $campaign))->assertOk();

        Livewire::actingAs($this->admin)->test(PlanSettings::class)
            ->set('freeFeatures', ['ai', 'duplication', 'archive'])
            ->call('save');

        $this->actingAs($gm)->get(route('maps.index', $campaign))->assertForbidden()->assertSee('Cette fonction n’est pas comprise dans la formule de cette campagne.');
        $this->actingAs($gm)->get(route('table.remote', $campaign))->assertForbidden();
        $this->actingAs($player)->get(route('table.screen', $campaign))->assertForbidden();
        $this->actingAs($gm)->get(route('campaigns.show', $campaign))->assertDontSee(route('maps.index', $campaign))->assertSee(route('ai.index', $campaign));

        // Le MJ passe premium : ses joueurs en profitent aussi.
        $gm->forceFill(['plan' => 'premium'])->save();
        $this->actingAs($gm)->get(route('maps.index', $campaign))->assertOk();
        $this->actingAs($player)->get(route('table.screen', $campaign))->assertOk();
    }

    public function test_the_recettes_are_kept_and_their_follow_ups_feed_the_backlog(): void
    {
        RecetteImport::all();
        $this->assertSame([], RecetteImport::all(), 'Un cahier déjà là n’est pas ajouté deux fois.');

        $this->assertSame(['0.13.1', '0.25.0', '0.34.0'], Recette::orderBy('id')->pluck('version')->all());
        $recette = Recette::where('version', '0.13.1')->sole();
        $this->assertSame(100, $recette->items()->count());
        $this->assertSame(127, Recette::where('version', '0.25.0')->sole()->items()->count());
        $this->assertSame(127, Recette::where('version', '0.34.0')->sole()->items()->count());

        Livewire::actingAs($this->admin)->test(Recettes::class)
            ->assertSee(['Recette v0.25.0', 'Recette v0.34.0', 'Recette v0.13.1', '97,6'])
            ->assertDontSee('Recette V1')
            ->set('recetteId', $recette->id)
            ->assertSee(['Recette v0.13.1', 'Parcours de recette prioritaires', 'Invitations (lien, rôle, acceptation)', '95,1'])
            ->set('belowTarget', true)
            ->assertDontSee('Invitations (lien, rôle, acceptation)')
            ->assertSee('Échanges entre joueurs');

        $this->assertSame(35 + 64 + 56, BugReport::where('source', 'recette')->count());
        Livewire::actingAs($this->admin)->test(Backlog::class)
            ->assertSee('Pas d\'écran pour choisir ce qui passe à un nouveau personnage')
            ->assertDontSee('La recherche du MJ ne trouvait ni les secrets')
            ->set('status', 'fixed')
            ->assertSee('La recherche du MJ ne trouvait ni les secrets')
            ->set('status', 'open')
            ->set('adding', true)
            ->set('newTitle', 'Éditeur Tiptap')
            ->set('newKind', 'evolution')
            ->set('newPriority', 'high')
            ->call('add')
            ->assertSee('Éditeur Tiptap');

        $this->assertSame('admin', BugReport::where('title', 'Éditeur Tiptap')->sole()->source);
    }

    public function test_the_administrator_keeps_the_planned_evolutions_of_the_platform(): void
    {
        $this->get(route('admin.evolutions'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.evolutions'))->assertForbidden();

        $this->actingAs($this->admin)->withSession(['auth.password_confirmed_at' => time()])->get(route('admin.evolutions'))->assertOk()->assertSee('Évolutions');

        $page = Livewire::actingAs($this->admin)->test(Evolutions::class)
            ->set('newTitle', 'Créer le compte Stripe')
            ->set('newTarget', 'v1.0')
            ->set('newPriority', 'high')
            ->call('add')
            ->assertHasNoErrors()
            ->assertSee('Créer le compte Stripe')
            ->assertSee('v1.0');

        $evolution = Evolution::where('title', 'Créer le compte Stripe')->firstOrFail();
        $this->assertSame('planned', $evolution->status);

        $page->call('saveDetails', $evolution->id, 'Compte Stripe', 'décembre', 'Mode test d’abord')
            ->call('setStatus', $evolution->id, 'done')
            ->assertDontSee('Compte Stripe')
            ->set('status', 'done')
            ->assertSee('Compte Stripe');
        $this->assertSame(['décembre', 'Mode test d’abord'], [$evolution->fresh()->target, $evolution->fresh()->detail]);

        $page->call('delete', $evolution->id);
        $this->assertModelMissing($evolution);
    }
}
