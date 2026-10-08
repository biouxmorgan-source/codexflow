<?php

namespace Tests\Feature\Public;

use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_presents_loremundi_to_search_engines(): void
    {
        $response = $this->get('/')->assertOk()
            ->assertSee('L’assistant du maître de jeu, pour toutes vos campagnes')
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical" href="'.url('/').'?lang=fr">', false)
            ->assertSee('"@type":"WebApplication"', false)
            ->assertSee(asset('images/og-loremundi.png'));

        foreach (array_keys(Locale::available()) as $code) {
            $response->assertSee('hreflang="'.$code.'"', false);
        }
    }

    public function test_the_home_page_is_translated(): void
    {
        $this->get('/?lang=en')->assertOk()
            ->assertSee('The game master’s assistant, for all your campaigns')
            ->assertSee('<html lang="en"', false);
    }

    public function test_help_is_readable_without_an_account(): void
    {
        $this->get(route('help'))->assertOk()
            ->assertSee('Comment inviter mes joueurs ?')
            ->assertSee('Votre compte')
            ->assertSee('Comment protéger mon compte avec la double authentification ?')
            ->assertSee('Connectez-vous pour signaler un problème')
            ->assertDontSee(route('bugs.create'));
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
}
