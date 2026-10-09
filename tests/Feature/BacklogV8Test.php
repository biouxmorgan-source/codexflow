<?php

namespace Tests\Feature;

use App\Livewire\Account\Preferences;
use App\Livewire\Admin\Backlog;
use App\Livewire\Admin\Users;
use App\Livewire\Support\ReportBug;
use App\Models\BugReport;
use App\Models\User;
use App\Models\UserLogin;
use App\Notifications\Channels\PushChannel;
use App\Support\SystemHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

/**
 * Backlog après la recette v0.34.0, lot « compte et administration » : un test par évolution.
 */
class BacklogV8Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_admin' => true])->save();
    }

    public function test_preferences_say_premium_opens_soon_and_show_the_subscription_start(): void
    {
        config(['services.stripe.secret' => null]);
        $gm = User::factory()->create();

        Livewire::actingAs($gm)->test(Preferences::class)->assertSee('Le passage à Premium ouvrira bientôt.');

        $gm->forceFill(['plan' => 'premium', 'plan_started_at' => '2026-09-01', 'plan_ends_at' => now()->addYear()])->save();
        Livewire::actingAs($gm->fresh())->test(Preferences::class)
            ->assertSee(['Début de l’abonnement', now()->setDate(2026, 9, 1)->isoFormat('LL')])
            ->assertDontSee('Le passage à Premium ouvrira bientôt.');
    }

    public function test_the_console_counts_login_days_and_shows_the_last_login(): void
    {
        $gm = User::factory()->create(['email' => 'camille@example.com']);
        foreach ([0, 1, 2] as $hour) {
            UserLogin::create(['user_id' => $gm->id, 'logged_in_at' => now()->startOfDay()->addHours(8 + $hour)]);
        }
        UserLogin::create(['user_id' => $gm->id, 'logged_in_at' => now()->subDays(3)]);

        $console = Livewire::actingAs($this->admin)->test(Users::class)->assertSee(['Jours de connexion', 'dernière : '.now()->isoFormat('L')]);
        $this->assertSame(2, $console->instance()->rows->firstWhere('user.id', $gm->id)['logins']);
        $this->assertSame(2, $console->instance()->totals['logins']);
    }

    public function test_a_visitor_reports_a_problem_without_an_account(): void
    {
        $this->get(route('login'))->assertSee(route('bugs.create', ['page' => '/login']), false);
        $this->get(route('bugs.create'))->assertOk()->assertSee('Votre adresse e-mail');

        Livewire::test(ReportBug::class)
            ->set('message', 'Le bouton de connexion ne répond pas.')
            ->call('send')
            ->assertHasErrors('contact')
            ->set('contact', 'Visiteur@Example.com')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee('Merci !');

        $report = BugReport::sole();
        $this->assertNull($report->user_id);
        $this->assertSame('visiteur@example.com', $report->contact_email);
        Livewire::actingAs($this->admin)->test(Backlog::class)->assertSee('Sans compte : visiteur@example.com');

        auth()->forgetGuards();

        // Un robot qui remplit le champ caché n'envoie rien ; au-delà de trois envois, il faut attendre.
        Livewire::test(ReportBug::class)->set(['message' => 'Achetez nos montres pas chères', 'contact' => 'spam@example.com', 'website' => 'http://spam'])->call('send');
        $this->assertSame(1, BugReport::count());
        foreach (range(1, 3) as $attempt) {
            Livewire::test(ReportBug::class)->set(['message' => 'Encore un problème de connexion.', 'contact' => 'visiteur@example.com'])->call('send');
        }
        $this->assertSame(3, BugReport::count());
    }

    public function test_the_console_warns_when_push_cannot_be_sent(): void
    {
        config(['webpush.vapid.public_key' => null]);
        Livewire::actingAs($this->admin)->test(Users::class)->assertSee('Clés VAPID absentes');

        config(['webpush.vapid.public_key' => 'public', 'webpush.vapid.private_key' => 'private']);
        Cache::flush();
        SystemHealth::pushFailed(new RuntimeException('Service push injoignable'));
        SystemHealth::pushFailed(new RuntimeException('Service push injoignable'));
        $this->assertTrue(PushChannel::enabled());

        Livewire::actingAs($this->admin)->test(Users::class)
            ->assertSee(['Envoi push en échec : 2 fois', 'Service push injoignable']);
        $this->assertSame(SystemHealth::hasBigMath(), ! collect(SystemHealth::warnings())->contains(fn ($warning) => str_contains($warning, 'GMP')));
    }
}
