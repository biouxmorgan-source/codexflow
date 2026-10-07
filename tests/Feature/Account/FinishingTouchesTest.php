<?php

namespace Tests\Feature\Account;

use App\Livewire\Account\Preferences;
use App\Livewire\WhatsNew;
use App\Models\Campaign;
use App\Models\User;
use App\Support\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinishingTouchesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_version_is_shown_and_described(): void
    {
        $this->assertArrayHasKey(Changelog::version(), Changelog::all());

        $this->actingAs(User::factory()->create())->get(route('campaigns.index'))
            ->assertOk()
            ->assertSee('CodexFlow '.Changelog::version())
            ->assertSee('Quoi de neuf');

        $this->get(route('changelog'))->assertOk()->assertSee('Version '.Changelog::version())->assertSee('Le MJ seul');
        $this->get(route('recommended'))->assertOk()->assertSee('Pour les joueurs');
    }

    public function test_whats_new_shows_once_after_an_update_and_never_to_a_new_account(): void
    {
        $newcomer = User::factory()->create();
        $this->assertSame(Changelog::version(), $newcomer->last_seen_version);
        Livewire::actingAs($newcomer)->test(WhatsNew::class)->assertDontSee('Quoi de neuf ?');

        $veteran = User::factory()->create();
        $veteran->forceFill(['last_seen_version' => '0.1.0'])->save();

        Livewire::actingAs($veteran)->test(WhatsNew::class)
            ->assertSee('Quoi de neuf ?')
            ->assertSee('Les joueurs')
            ->assertSee('Le lien vivant')
            ->assertDontSee('Le MJ seul')
            ->call('dismiss')
            ->assertDontSee('Quoi de neuf ?');

        $this->assertSame(Changelog::version(), $veteran->fresh()->last_seen_version);

        // Compte d'avant « Quoi de neuf » : seulement la dernière version.
        $this->assertSame([Changelog::version()], array_keys(Changelog::unseenSince(null)));
    }

    public function test_display_preferences_are_saved_and_applied(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('campaigns.index'))
            ->assertSee('data-theme-choice="system"', false)
            ->assertDontSee('data-theme="', false);

        Livewire::actingAs($user)->test(Preferences::class)
            ->set('theme', 'dark')
            ->set('accent', 'violet')
            ->set('size', 'large')
            ->call('save')
            ->assertRedirect(route('preferences'));

        $this->get(route('campaigns.index'))
            ->assertSee('data-theme-choice="dark"', false)
            // Rendu côté serveur : la navigation Livewire garde le thème sombre.
            ->assertSee('data-theme="dark"', false)
            ->assertSee('data-accent="violet"', false)
            ->assertSee('data-size="large"', false);

        Livewire::actingAs($user)->test(Preferences::class)->set('theme', 'neon')->call('save')->assertHasErrors('theme');

        // L'écran de table garde son affichage propre.
        $campaign = Campaign::factory()->for($user, 'owner')->create();
        $this->get(route('table.screen', $campaign))->assertOk()->assertDontSee('data-theme-choice="', false);
    }
}
