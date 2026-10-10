<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Health;
use App\Models\Campaign;
use App\Models\RequestMetric;
use App\Models\User;
use App\Models\UserLogin;
use App\Support\Monitoring\ResponseTimes;
use App\Support\SystemHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_only_the_administrator_opens_the_health_tab(): void
    {
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $this->actingAs(User::factory()->create())->get(route('admin.health'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.health'))->assertOk()
            ->assertSee(['Santé', 'Fréquentation', 'Tables de jeu', 'Espace occupé', 'Serveur']);
    }

    public function test_it_counts_people_logins_and_players_per_game_master(): void
    {
        $gm = User::factory()->create();
        $first = Campaign::factory()->for($gm, 'owner')->create();
        $second = Campaign::factory()->for($gm, 'owner')->create();
        [$anna, $bruno, $chloe] = User::factory()->count(3)->create();
        $first->members()->attach([$anna->id => ['role' => 'player'], $bruno->id => ['role' => 'player']]);
        $second->members()->attach([$anna->id => ['role' => 'player'], $chloe->id => ['role' => 'player']]);

        // Une personne connectée trois fois, une autre une fois ; une connexion trop ancienne ne compte pas.
        foreach ([1, 2, 3] as $days) {
            UserLogin::create(['user_id' => $gm->id, 'logged_in_at' => now()->subDays($days)]);
        }
        UserLogin::create(['user_id' => $anna->id, 'logged_in_at' => now()->subDay()]);
        UserLogin::create(['user_id' => $bruno->id, 'logged_in_at' => now()->subDays(60)]);

        $health = Livewire::actingAs($this->admin)->test(Health::class);

        $usage = $health->instance()->usage;
        $this->assertSame(2, $usage['people']);
        $this->assertSame(4, $usage['logins']);
        $this->assertSame(2.0, $usage['per_person']);
        $this->assertSame(5, $usage['accounts']);

        $tables = $health->instance()->tables;
        $this->assertSame(2, $tables['campaigns']);
        $this->assertSame(1, $tables['gms']);
        $this->assertSame(3, $tables['players']);
        $this->assertEquals(2, $tables['players_per_campaign']);
        // Anna joue dans les deux campagnes : le MJ a trois joueurs distincts, pas quatre.
        $this->assertEquals(3, $tables['players_per_gm']);
        $this->assertSame(2, $tables['max_players']);
        $this->assertSame(3, $tables['players_only']);
        $this->assertSame(1, $tables['without_campaign']);

        // Sur 90 jours, la connexion de Bruno entre dans le compte.
        $this->assertSame(3, $health->set('period', 90)->instance()->usage['people']);
        $health->assertSee('Joueurs par MJ');
    }

    public function test_each_request_is_timed_and_slow_hours_warn_the_administrator(): void
    {
        $this->get('/')->assertOk();
        $this->assertSame(1, (int) RequestMetric::sum('requests'));

        ResponseTimes::record(200);
        ResponseTimes::record(1500);
        ResponseTimes::record(4000, error: true);

        $metric = RequestMetric::first();
        $this->assertSame(4, $metric->requests);
        $this->assertSame(4000, $metric->max_ms);
        $this->assertSame(2, $metric->slow);
        $this->assertSame(1, $metric->very_slow);
        $this->assertSame(1, $metric->errors);
        $this->assertCount(24, ResponseTimes::hourly());

        // Trop peu de requêtes pour juger : pas d'alerte.
        $this->assertNull(ResponseTimes::warning());

        foreach (range(1, 60) as $i) {
            ResponseTimes::record($i % 5 === 0 ? 1200 : 100);
        }
        $this->assertStringContainsString('Le serveur ralentit', ResponseTimes::warning());
        $this->assertContains(ResponseTimes::warning(), SystemHealth::warnings());

        Livewire::actingAs($this->admin)->test(Health::class)->assertSee(['Lentes (> 1 s)', 'Le serveur ralentit']);
    }
}
