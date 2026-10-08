<?php

namespace Tests\Feature\Support;

use App\Livewire\Admin\Backlog;
use App\Livewire\Support\ReportBug;
use App\Models\BugReport;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HelpAndBugReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_help_page_answers_common_questions(): void
    {
        $this->actingAs(User::factory()->create())->get(route('help'))
            ->assertOk()
            ->assertSee('Comment inviter mes joueurs ?')
            ->assertSee('Puis-je consulter ma fiche sans réseau ?')
            ->assertSee(route('bugs.create'));
    }

    public function test_the_first_account_administers_the_installation(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertTrue($first->fresh()->is_admin);
        $this->assertFalse($second->fresh()->is_admin);

        $this->artisan('codexflow:admin', ['email' => $second->email])->assertSuccessful();
        $this->assertTrue($second->fresh()->is_admin);
        $this->artisan('codexflow:admin', ['email' => $second->email, '--remove' => true])->assertSuccessful();
        $this->assertFalse($second->fresh()->is_admin);
    }

    public function test_a_user_reports_a_problem_and_the_admin_handles_it(): void
    {
        $admin = User::factory()->create(['name' => 'Morgan']);
        $player = User::factory()->create(['name' => 'Camille', 'email' => 'camille@example.com']);

        // Le lien du pied de page emporte la page où l'on se trouve.
        $this->actingAs($player)->get(route('notifications.index'))
            ->assertSee(route('bugs.create', ['page' => '/notifications']), false)
            ->assertDontSee('Problèmes signalés');

        Livewire::actingAs($player)->withQueryParams(['page' => '/notifications'])->test(ReportBug::class)
            ->call('send')
            ->assertSee('Décrivez ce qui s’est passé.')
            ->set('message', 'court')
            ->call('send')
            ->assertHasErrors('message')
            ->assertSee('Décrivez le problème en au moins 10 caractères.')
            ->set('message', 'La cloche ne se met pas à jour après une révélation.')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee('Merci ! Votre signalement a bien été envoyé.')
            ->assertSet('message', '');

        $report = BugReport::sole();
        $this->assertSame($player->id, $report->user_id);
        $this->assertSame('/notifications', $report->url);
        $this->assertSame(Changelog::version(), $report->version);
        $this->assertSame('fr', $report->locale);

        // Le signalement arrive au backlog de l'administrateur, et lui seul le lit.
        $this->assertSame('new', $report->status);
        $this->assertSame('bug', $report->kind);
        $this->actingAs($player)->get(route('admin.backlog'))->assertForbidden();
        $this->actingAs($player)->get(route('bugs.index'))->assertRedirect(route('admin.backlog'));
        $this->actingAs($admin)->get(route('campaigns.index'))->assertSee('Administration (1 nouveau signalement)');

        Livewire::actingAs($admin)->test(Backlog::class)
            ->assertSee('La cloche ne se met pas à jour')
            ->assertSee('camille@example.com')
            ->assertSee('/notifications')
            ->call('setStatus', $report->id, 'fixed')
            ->assertDontSee('La cloche ne se met pas à jour')
            ->set('status', 'fixed')
            ->assertSee('La cloche ne se met pas à jour')
            ->assertSee('corrigé en '.Changelog::version());

        $this->assertSame(Changelog::version(), $report->fresh()->fixed_in);

        Livewire::actingAs($player)->test(Backlog::class)->assertForbidden();
    }

    public function test_a_page_from_another_site_is_not_kept(): void
    {
        Livewire::actingAs(User::factory()->create())->withQueryParams(['page' => '//exemple.com/piege'])->test(ReportBug::class)
            ->set('message', 'Le bouton Enregistrer ne répond pas.')
            ->call('send');

        $this->assertNull(BugReport::sole()->url);
    }
}
