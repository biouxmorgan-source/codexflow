<?php

namespace Tests\Feature\Account;

use App\Livewire\Account\Preferences;
use App\Models\User;
use App\Support\Locale;
use App\Support\TranslationKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_interface_follows_the_browser_language_and_defaults_to_french(): void
    {
        $this->withHeader('Accept-Language', 'fr-FR')->get(route('login'))->assertOk()->assertSee('<html lang="fr"', false)->assertSee('Se connecter');

        $this->withHeader('Accept-Language', 'en-GB,en;q=0.9')->get(route('login'))
            ->assertSee('<html lang="en"', false)
            ->assertSee('Log in');

        // Langue non proposée : la suivante dans la liste du navigateur, sinon le français.
        $this->withHeader('Accept-Language', 'ja,de;q=0.8')->get(route('login'))->assertSee('<html lang="de"', false);
        $this->withHeader('Accept-Language', 'ja')->get(route('login'))->assertSee('<html lang="fr"', false);
    }

    public function test_a_visitor_picks_a_language_on_the_welcome_pages(): void
    {
        $this->withHeader('Accept-Language', 'fr')->get(route('login', ['lang' => 'es']))->assertSee('<html lang="es"', false);
        $this->withHeader('Accept-Language', 'fr')->get(route('register'))->assertSee('<html lang="es"', false);

        $this->get(route('login', ['lang' => 'xx']))->assertSee('<html lang="es"', false);
    }

    public function test_the_account_language_wins_over_the_browser(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withHeader('Accept-Language', 'de')->get(route('campaigns.index'))->assertSee('<html lang="de"', false);
        $this->assertSame('de', Locale::for($user->fresh()));

        Livewire::actingAs($user)->test(Preferences::class)
            ->assertSee('Langue')
            ->set('locale', 'it')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('preferences'));

        $this->assertSame('it', $user->fresh()->preferences['locale']);
        $this->actingAs($user->fresh())->withHeader('Accept-Language', 'de')->get(route('campaigns.index'))->assertSee('<html lang="it"', false);

        // Revenir à « comme le navigateur ».
        Livewire::actingAs($user->fresh())->test(Preferences::class)->set('locale', '')->call('save');
        $this->assertArrayNotHasKey('locale', $user->fresh()->preferences);

        Livewire::actingAs($user->fresh())->test(Preferences::class)->set('locale', 'xx')->call('save')->assertHasErrors('locale');
    }

    public function test_every_language_has_every_text(): void
    {
        $this->assertNotEmpty(TranslationKeys::all());

        foreach (array_keys(Locale::available()) as $locale) {
            foreach (['auth', 'pagination', 'passwords', 'validation', 'changelog'] as $group) {
                $this->assertFileExists(lang_path("$locale/$group.php"));
            }

            if ($locale === Locale::DEFAULT) {
                continue;
            }

            $this->assertSame([], TranslationKeys::missing($locale), "Textes sans traduction en $locale (php artisan lang:missing $locale)");

            // Mêmes variables (:name…) dans la traduction que dans le texte français.
            foreach (json_decode(file_get_contents(lang_path("$locale.json")), true) as $french => $translated) {
                preg_match_all('/:[a-z_]+/', $french, $expected);
                preg_match_all('/:[a-z_]+/', $translated, $found);
                $this->assertEqualsCanonicalizing(array_unique($expected[0]), array_unique($found[0]), "$locale : « $french »");
            }
        }
    }
}
