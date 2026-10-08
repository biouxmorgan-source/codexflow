<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_the_public_page_to_guests_and_campaigns_to_members(): void
    {
        $this->get('/')->assertOk()->assertSee(route('register'))->assertSee(route('login'));
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('campaigns.index'));
    }

    public function test_login_screen_is_in_french(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Connexion')->assertSee('Mot de passe');
    }

    public function test_users_can_register_and_land_on_their_campaigns(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Morgane',
            'email' => 'mj@example.test',
            'password' => 'un-mot-de-passe-solide-42',
            'password_confirmation' => 'un-mot-de-passe-solide-42',
        ]);

        $response->assertRedirect(route('campaigns.index'));
        $this->assertAuthenticated();
    }

    public function test_users_can_log_in(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('campaigns.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_shows_a_french_error(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login'), ['email' => $user->email, 'password' => 'faux'])
            ->assertSessionHasErrors(['email' => 'Ces identifiants ne correspondent à aucun compte.']);

        $this->assertGuest();
    }

    public function test_campaigns_require_authentication(): void
    {
        $this->get(route('campaigns.index'))->assertRedirect(route('login'));
    }
}
