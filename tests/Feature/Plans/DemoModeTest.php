<?php

namespace Tests\Feature\Plans;

use App\Enums\CampaignRole;
use App\Livewire\Admin\PlanSettings;
use App\Livewire\Admin\Users;
use App\Livewire\Campaigns\Index as CampaignsIndex;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Members\Index as MembersIndex;
use App\Livewire\Sessions\Live;
use App\Models\Campaign;
use App\Models\CampaignInvitation;
use App\Models\Entity;
use App\Models\User;
use App\Support\Demo\DemoLockedException;
use App\Support\Plans\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Mode démo (avant le Kickstarter) : chaque nouveau compte visite sa propre démonstration,
 * verrouillée, sans entamer son essai ; une invitation de l'administrateur ouvre un compte normal.
 */
class DemoModeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(); // le premier compte administre
    }

    private function enableDemoMode(): void
    {
        $this->actingAs($this->admin);
        Livewire::test(PlanSettings::class)->set('demoMode', true)->call('save')->assertHasNoErrors();
        auth()->logout();
    }

    private function register(string $email): User
    {
        auth()->logout();
        $this->post(route('register'), [
            'name' => 'Visiteur',
            'email' => $email,
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ])->assertRedirect();

        return User::where('email', $email)->sole();
    }

    private function demoAccount(): array
    {
        $this->enableDemoMode();
        $this->get(route('register'))->assertSee('version de démonstration')->assertSee('financement participatif');
        $user = $this->register('visiteur@example.test');
        $this->actingAs($user);
        Livewire::test(CampaignsIndex::class);
        $demo = $user->ownedCampaigns()->where('is_demo', true)->sole();

        return [$user, $demo];
    }

    public function test_without_demo_mode_signups_are_normal_accounts(): void
    {
        $user = $this->register('normal@example.test');

        $this->assertSame(Plans::FREE, $user->plan);
        $this->actingAs($user)->get(route('campaigns.index'))->assertOk()->assertDontSee('Version de démonstration');
    }

    public function test_a_demo_signup_gets_a_banner_its_own_demo_campaign_and_no_trial(): void
    {
        [$user, $demo] = $this->demoAccount();

        $this->assertSame(Plans::DEMO, Plans::effective($user->fresh()));
        $this->assertNull($user->fresh()->trial_started_at, 'La démo n’entame pas l’essai.');
        $this->get(route('campaigns.index'))
            ->assertOk()
            ->assertSee('Version de démonstration')
            ->assertSee('financement participatif')
            ->assertSee($demo->name)
            ->assertDontSee('Charger la démonstration');
        $this->get(route('campaigns.show', $demo))->assertOk()->assertSee('Version de démonstration');

        // Pas d'IA, pas d'export, 50 Mo ; une seule copie de la démonstration.
        $this->assertFalse(Plans::allows($user, 'ai'));
        $this->assertFalse(Plans::allows($user, 'archive'));
        $this->assertFalse(Plans::allows($user, 'duplication'));
        $this->assertTrue(Plans::allows($user, 'maps'));
        $this->assertSame(50 * 1024 * 1024, Plans::storageLimit($user));
        Livewire::test(CampaignsIndex::class);
        $this->assertSame(1, $user->ownedCampaigns()->count());
        Livewire::test(CampaignsIndex::class)->call('loadDemo')->assertForbidden();
    }

    public function test_the_demo_campaign_is_read_only_except_for_the_sandbox(): void
    {
        [$user, $demo] = $this->demoAccount();
        $entity = Entity::where('campaign_id', $demo->id)->firstOrFail();

        $this->get(route('entities.show', [$demo, $entity]))->assertOk();

        // Modifier une fiche : refusé, le contenu ne change pas, un message s'affiche.
        Livewire::test(EntityForm::class, ['campaign' => $demo, 'entity' => $entity])
            ->set('name', 'Renommée')
            ->call('save')
            ->assertDispatched('demo-locked');
        $this->assertNotSame('Renommée', $entity->fresh()->name);

        // Créer une fiche, inviter un joueur, renommer ou supprimer la campagne : refusé.
        Livewire::test(EntityForm::class, ['campaign' => $demo])
            ->set('name', 'Nouvelle')
            ->set('entityTypeId', (string) $entity->entity_type_id)
            ->call('save')
            ->assertDispatched('demo-locked');
        $this->assertFalse(Entity::where('name', 'Nouvelle')->exists());
        Livewire::test(MembersIndex::class, ['campaign' => $demo])->call('invite')->assertDispatched('demo-locked');
        $this->assertSame(0, CampaignInvitation::count());

        foreach ([fn () => $demo->fresh()->update(['name' => 'Ma table']), fn () => $demo->fresh()->delete(), fn () => $entity->fresh()->delete()] as $write) {
            try {
                $write();
                $this->fail('Écriture permise dans la démonstration verrouillée.');
            } catch (DemoLockedException) {
            }
        }
        $this->assertTrue($demo->fresh()->exists);

        // Bac à sable : lancer une séance, noter, terminer.
        Livewire::test(Live::class, ['campaign' => $demo])
            ->call('start')
            ->set('noteBody', 'Les héros entrent dans la crypte.')
            ->call('addNote')
            ->assertNotDispatched('demo-locked')
            ->assertHasNoErrors();
        $this->assertSame(2, $demo->playSessions()->count());
    }

    public function test_a_demo_account_can_create_one_small_campaign_with_a_few_players(): void
    {
        [$user] = $this->demoAccount();

        Plans::ensureCanCreateCampaign($user);
        $own = Campaign::factory()->for($user, 'owner')->create();
        $this->assertNull($user->fresh()->trial_started_at);

        // Sa campagne se modifie librement ; une deuxième est refusée.
        $own->update(['name' => 'Ma table']);
        $this->assertSame('Ma table', $own->fresh()->name);
        $this->expectsValidation(fn () => Plans::ensureCanCreateCampaign($user->fresh()));

        // Trois membres au plus, invitations en attente comprises.
        $component = Livewire::test(MembersIndex::class, ['campaign' => $own]);
        foreach (range(1, 3) as $i) {
            $component->set('label', "Joueur $i")->call('invite')->assertHasNoErrors();
        }
        $component->set('label', 'Joueur 4')->call('invite')->assertHasErrors('label');
        $this->assertSame(3, $own->invitations()->count());
    }

    public function test_an_invitation_from_the_administrator_opens_a_normal_account(): void
    {
        $this->enableDemoMode();
        $campaign = Campaign::factory()->for($this->admin, 'owner')->create();
        $invitation = new CampaignInvitation(['role' => CampaignRole::Player]);
        $invitation->campaign()->associate($campaign);
        $invitation->inviter()->associate($this->admin);
        $invitation->save();

        // Inscription depuis le lien : pas de mention de la démo, compte normal une fois l'invitation acceptée.
        $this->get(route('invitations.show', $invitation->token))->assertOk();
        $this->get(route('register'))->assertOk()->assertDontSee('version de démonstration');
        $user = $this->register('invite@example.test');
        $this->post(route('invitations.accept', $invitation->token))->assertRedirect();
        $this->assertSame(Plans::FREE, $user->fresh()->plan);
        $this->get(route('campaigns.index'))->assertDontSee('Version de démonstration');
        $this->assertFalse($user->ownedCampaigns()->exists(), 'Pas de démonstration chargée pour un compte invité.');

        // Un compte démo déjà créé, invité ensuite par l'administrateur, sort de la démo.
        session()->forget('invitation_token');
        $other = $this->register('visiteur@example.test');
        $this->assertSame(Plans::DEMO, $other->plan);
        $second = new CampaignInvitation(['role' => CampaignRole::Player]);
        $second->campaign()->associate($campaign);
        $second->inviter()->associate($this->admin);
        $second->save();
        $this->actingAs($other)->post(route('invitations.accept', $second->token))->assertRedirect();
        $this->assertSame(Plans::FREE, $other->fresh()->plan);
    }

    public function test_the_administrator_unlocks_a_demo_account_and_its_trial_starts(): void
    {
        [$user, $demo] = $this->demoAccount();

        $this->actingAs($this->admin);
        Livewire::test(Users::class)->assertSee('Démo')->call('unlock', $user->id);

        $user->refresh();
        $this->assertSame(Plans::FREE, $user->plan);
        $this->assertNotNull($user->trial_started_at);
        $this->assertSame(Plans::TRIAL, Plans::effective($user));

        // Débloqué, sa démonstration se modifie comme une campagne ordinaire.
        $this->actingAs($user);
        $demo->fresh()->update(['name' => 'Ma démo']);
        $this->assertSame('Ma démo', $demo->fresh()->name);
        $this->get(route('campaigns.index'))->assertDontSee('Version de démonstration');
    }

    private function expectsValidation(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Une validation était attendue.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }
    }
}
