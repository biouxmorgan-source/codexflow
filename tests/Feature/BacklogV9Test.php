<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Backlog après la recette v0.34.0, lot « application installée et sécurité » : un test par évolution.
 */
class BacklogV9Test extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_is_translated_and_offers_maskable_icons(): void
    {
        $response = $this->withHeader('Accept-Language', 'en-GB,en;q=0.9')->get('/manifest.webmanifest');

        $response->assertOk()->assertHeader('Content-Type', 'application/manifest+json');
        $this->assertSame('en', $response->json('lang'));
        $this->assertSame('The assistant for role-playing game masters and their players.', $response->json('description'));

        $maskable = collect($response->json('icons'))->where('purpose', 'maskable');
        $this->assertCount(2, $maskable);
        foreach ($maskable as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_offline_mode_greys_out_editing_controls(): void
    {
        $this->actingAs(User::factory()->create())->get(route('campaigns.index'))
            ->assertOk()
            ->assertSee("classList.toggle('is-offline', ! online)", false)
            ->assertSee('Les boutons et champs sont désactivés jusqu’au retour de la connexion.');

        $this->assertStringContainsString('.is-offline', file_get_contents(resource_path('css/app.css')));
    }

    public function test_content_security_policy_only_allows_the_site_and_its_realtime_server(): void
    {
        config(['broadcasting.connections.reverb.options' => ['host' => 'temps-reel.example.test', 'port' => 443, 'scheme' => 'https']]);

        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[^']+' 'unsafe-eval'/", $csp);
        $this->assertStringContainsString("connect-src 'self' wss://temps-reel.example.test:443", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringNotContainsString('https://fonts', $csp);
    }
}
