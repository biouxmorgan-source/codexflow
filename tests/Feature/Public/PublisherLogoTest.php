<?php

namespace Tests\Feature\Public;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_publisher_logo_appears_where_the_publisher_is_named(): void
    {
        $horizontal = 'images/autistic-intelligence-horizontal.svg';

        $this->get('/')->assertOk()
            ->assertSee($horizontal)
            ->assertSee('images/autistic-intelligence.png', false);
        $this->get(route('login'))->assertOk()->assertSee($horizontal);
        $this->get(route('privacy'))->assertOk()
            ->assertSee('images/autistic-intelligence.svg')
            ->assertSee('aria-label="Autistic Intelligence"', false);

        $this->actingAs(User::factory()->create())
            ->get(route('preferences'))->assertOk()->assertSee($horizontal);

        foreach (['autistic-intelligence.svg', 'autistic-intelligence-horizontal.svg', 'autistic-intelligence.png'] as $file) {
            $this->assertFileExists(public_path('images/'.$file));
        }
    }
}
