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
            ->assertSee('SagaWyn '.Changelog::version())
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

    public function test_players_get_the_short_version_of_notable_releases_and_the_admin_the_detail(): void
    {
        // Données figées sur la 0.95.0 : une version suivante n'a pas forcément de phrase courte.
        $latestVersion = '0.95.0';
        config(['codexflow.version' => $latestVersion]);
        $latest = Changelog::all()[$latestVersion];

        // Version notable (0.89.0) et petite correction (0.87.1, sans phrase courte).
        $player = User::factory()->create();
        $player->forceFill(['is_admin' => false, 'last_seen_version' => '0.87.0'])->save();

        Livewire::actingAs($player)->test(WhatsNew::class)
            ->assertSee('Quoi de neuf ?')
            ->assertSee($latest['short'])
            ->assertDontSee($latest['items'][0])
            ->assertSee('Déclaration de droits à chaque import')
            ->assertDontSee('Noms des points lisibles sur la carte')
            ->assertDontSee('Repérer les accès offerts');

        $this->actingAs($player)->get(route('changelog'))
            ->assertOk()
            ->assertSee($latest['short'])
            ->assertDontSee($latest['items'][0])
            ->assertDontSee('Version 0.87.1')
            ->assertDontSee('Version courte');

        $unseen = array_keys(Changelog::unseenSince('0.86.2', false));
        $this->assertContains('0.89.0', $unseen);
        $this->assertContains('0.87.0', $unseen);
        $this->assertNotContains('0.88.0', $unseen);
        $this->assertNotContains('0.87.1', $unseen);

        // Seule une petite correction depuis la dernière vue : pas de fenêtre pour le joueur.
        config(['codexflow.version' => '0.87.1']);
        $this->assertSame([], Changelog::unseenSince('0.87.0', false));
        $this->assertSame(['0.87.1'], array_keys(Changelog::unseenSince('0.87.0')));
        config(['codexflow.version' => $latestVersion]);

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true, 'last_seen_version' => '0.87.0'])->save();

        Livewire::actingAs($admin)->test(WhatsNew::class)
            ->assertSee($latest['items'][0])
            ->assertSee('Noms des points lisibles sur la carte')
            ->assertSee('Repérer les accès offerts');

        $this->actingAs($admin)->get(route('changelog'))
            ->assertOk()
            ->assertSee($latest['items'][0])
            ->assertSee('Version courte : '.$latest['short'])
            ->assertSee('Version 0.87.1')
            ->assertSee('Version non montrée aux autres comptes.');
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
