<?php

namespace Tests\Feature\Admin;

use App\Enums\CampaignRole;
use App\Livewire\Admin\PlanSettings;
use App\Livewire\Admin\Users;
use App\Models\Campaign;
use App\Models\User;
use App\Support\Plans\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Marqueur Fondateur : les contributeurs du Kickstarter ont un badge et de l'espace en plus. */
class FounderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email' => 'morgan@example.com']);
        Plans::save(['trial_weeks' => 0, 'free_storage_mb' => 30, 'premium_storage_mb' => 1024]);
    }

    public function test_the_administrator_marks_a_founder_who_gets_the_storage_bonus(): void
    {
        $user = User::factory()->create(['email' => 'lea@example.com']);
        $this->assertSame(30 * 1024 * 1024, Plans::storageLimit($user));

        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $user->id)
            ->set('isFounder', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Fondateur');

        $user->refresh();
        $this->assertTrue($user->isFounder());
        $this->assertSame(today()->toDateString(), $user->founder_since->toDateString());
        $this->assertSame((30 + 500) * 1024 * 1024, Plans::storageLimit($user));

        // Le bonus s'ajoute aussi au premium, et se règle dans « Formules ».
        Livewire::actingAs($this->admin)->test(PlanSettings::class)->set('founderBonusMb', 1024)->call('save')->assertHasNoErrors();
        $user->forceFill(['plan' => 'premium'])->save();
        $this->assertSame(2048 * 1024 * 1024, Plans::storageLimit($user->fresh()));

        // Un stockage propre au compte remplace le calcul.
        $user->forceFill(['storage_quota_mb' => 100])->save();
        $this->assertSame(100 * 1024 * 1024, Plans::storageLimit($user->fresh()));
    }

    public function test_saving_keeps_the_original_founder_date_and_unmarking_removes_it(): void
    {
        $user = User::factory()->create(['founder_since' => '2027-02-25']);

        Livewire::actingAs($this->admin)->test(Users::class)->call('edit', $user->id)->call('save');
        $this->assertSame('2027-02-25', $user->fresh()->founder_since->toDateString());

        Livewire::actingAs($this->admin)->test(Users::class)->call('edit', $user->id)->set('isFounder', false)->call('save');
        $this->assertFalse($user->fresh()->isFounder());
        $this->assertSame(30 * 1024 * 1024, Plans::storageLimit($user->fresh()));
    }

    public function test_the_badge_shows_in_preferences_and_to_the_game_master(): void
    {
        $gm = User::factory()->create();
        $founder = User::factory()->create(['name' => 'Léa', 'founder_since' => '2027-02-25']);
        $campaign = Campaign::factory()->for($gm, 'owner')->create();
        $campaign->members()->attach($founder, ['role' => CampaignRole::Player->value]);

        $this->actingAs($founder)->get(route('preferences'))->assertOk()->assertSee('★ Fondateur')->assertSee('Merci d’avoir soutenu SagaWyn sur Kickstarter');
        $this->actingAs($gm)->get(route('members.index', $campaign))->assertOk()->assertSee('★ Fondateur');
        $this->actingAs($gm)->get(route('preferences'))->assertDontSee('★ Fondateur');
    }
}
