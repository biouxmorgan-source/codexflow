<?php

namespace Tests\Feature\Public;

use App\Models\User;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_presents_sagawyn_to_search_engines(): void
    {
        $response = $this->get('/')->assertOk()
            ->assertSee('Préparez vos mondes, menez vos séances, faites vivre la campagne')
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical" href="'.url('/').'?lang=fr">', false)
            ->assertSee('"@type":"WebApplication"', false)
            ->assertSee(asset('images/og-sagawyn.png'));

        foreach (array_keys(Locale::available()) as $code) {
            $response->assertSee('hreflang="'.$code.'"', false);
        }
    }

    public function test_the_home_page_is_translated(): void
    {
        $this->get('/?lang=en')->assertOk()
            ->assertSee('Prepare your worlds, run your sessions, bring the campaign to life')
            ->assertSee('<html lang="en"', false);
    }

    public function test_the_home_page_presents_today_s_features(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Préparer', 'À la table', 'Entre les séances'])
            ->assertSee('Une carte de bataille partagée')
            ->assertSee('Des dés pour toute la table')
            ->assertSee('Une grande carte de campagne')
            ->assertSee('Le livre des aventures')
            ->assertSee('Mes feuilles')
            ->assertSee('Découvrez sans rien préparer');
    }

    public function test_screenshots_follow_the_page_language(): void
    {
        foreach (array_keys(Locale::available()) as $code) {
            $this->assertFileExists(public_path("images/home-session-{$code}.webp"));
            $this->assertFileExists(public_path("images/home-player-{$code}.webp"));
        }

        $this->get('/?lang=de')->assertOk()
            ->assertSee(asset('images/home-session-de.webp'))
            ->assertSee(asset('images/home-player-de.webp'))
            ->assertDontSee('home-session-fr.webp');
    }

    public function test_help_is_readable_without_an_account(): void
    {
        $this->get(route('help'))->assertOk()
            ->assertSee('Comment inviter mes joueurs ?')
            ->assertSee('Votre compte')
            ->assertSee('Comment protéger mon compte avec la double authentification ?')
            ->assertSee('À quoi sert le mode Session ?')
            ->assertSee('Puis-je prendre des notes et les partager ?')
            ->assertSee(route('bugs.create'));
    }

    public function test_robots_and_sitemap_point_to_public_pages_only(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Disallow: /campagnes')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));

        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.url('/').'?lang=fr</loc>', false)
            ->assertSee(route('help').'?lang=de', false)
            ->assertDontSee('/campagnes');
    }

    public function test_the_community_discord_is_linked_by_default(): void
    {
        $this->assertSame('https://discord.gg/jzFKK69bn', config('codexflow.discord_url'));

        $this->get('/')->assertOk()
            ->assertSee('Rejoindre la communauté sur Discord')
            ->assertSee('href="https://discord.gg/jzFKK69bn"', false);
    }

    public function test_the_discord_link_can_be_hidden_or_replaced(): void
    {
        config(['codexflow.discord_url' => null]);
        $this->get('/')->assertOk()->assertDontSee('Rejoindre la communauté sur Discord');

        config(['codexflow.discord_url' => 'https://discord.gg/sagawyn']);
        $this->get('/')->assertOk()
            ->assertSee('Rejoindre la communauté sur Discord')
            ->assertSee('href="https://discord.gg/sagawyn"', false);
        $this->get('/aide')->assertOk()->assertSee('href="https://discord.gg/sagawyn"', false);

        $this->actingAs(User::factory()->create())
            ->get(route('campaigns.index'))->assertOk()
            ->assertSee('href="https://discord.gg/sagawyn"', false);
    }
}
