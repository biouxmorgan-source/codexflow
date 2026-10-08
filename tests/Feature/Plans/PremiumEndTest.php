<?php

namespace Tests\Feature\Plans;

use App\Actions\Demo\LoadDemoCampaign;
use App\Enums\CampaignRole;
use App\Livewire\Admin\PlanSettings;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\TableMap;
use App\Models\User;
use App\Support\Plans\Plans;
use App\Support\TableDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Fin d'un abonnement ou d'un essai : les fonctions Premium se coupent, aucune donnée ne part.
 * Les fonctions d'une campagne suivent la formule de son propriétaire, pas celle du visiteur.
 */
class PremiumEndTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        Plans::save(['free_features' => [], 'trial_weeks' => 0]);
        User::factory()->create(); // le premier compte est administrateur : ce n'est pas notre MJ
        $this->gm = User::factory()->create(['plan' => Plans::PREMIUM, 'plan_ends_at' => now()->addMonth()]);
        $this->campaign = app(LoadDemoCampaign::class)->handle($this->gm, 'fr');
        Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Seconde table']);
    }

    public function test_an_expired_subscription_switches_features_off_and_keeps_every_campaign_and_its_data(): void
    {
        $map = TableMap::where('campaign_id', $this->campaign->id)->sole();
        TableDisplay::show($this->campaign, 'map', $map->id);
        $message = new Message(['body' => 'À jeudi !']);
        $message->campaign()->associate($this->campaign);
        $message->sender()->associate($this->gm);
        $message->save();
        $counts = fn () => [$this->gm->ownedCampaigns()->count(), $this->campaign->localEntities()->count(), TableMap::count(), Message::count(), $this->campaign->documents()->count()];
        $before = $counts();

        $this->travel(2)->months();
        $this->assertSame(Plans::FREE, Plans::effective($this->gm->fresh()));
        $this->assertSame($before, $counts(), 'Rien n’est effacé à l’expiration.');

        // Les campagnes restent ouvertes ; les fonctions Premium sont signalées, pas cachées, et refusées par le serveur.
        $this->actingAs($this->gm)->get(route('campaigns.show', $this->campaign))
            ->assertOk()
            ->assertSee('Fonction Premium')
            ->assertSee('✦', false)
            ->assertDontSee(route('maps.index', $this->campaign));
        $this->actingAs($this->gm)->get(route('campaigns.show', Campaign::where('name', 'Seconde table')->sole()))->assertOk();
        $this->actingAs($this->gm)->get(route('maps.index', $this->campaign))->assertForbidden();
        $this->actingAs($this->gm)->get(route('table.screen', $this->campaign))->assertForbidden();

        try {
            TableDisplay::show($this->campaign->fresh(), 'map', $map->id);
            $this->fail('L’écran de table ne s’utilise plus.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        TableDisplay::clear($this->campaign->fresh());

        $this->expectException(ValidationException::class);
        Plans::ensureCanCreateCampaign($this->gm->fresh());
    }

    public function test_a_player_gets_the_owners_features_whatever_their_own_plan(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
        $this->campaign->update(['table_shared' => true]);

        $this->assertSame(Plans::FREE, Plans::effective($player));
        $this->assertTrue($player->can('use-feature', ['table', $this->campaign]));
        $this->assertFalse($player->can('use-feature', ['table']));

        $this->travel(2)->months();
        $this->assertFalse($player->can('use-feature', ['table', $this->campaign->fresh()]));
    }

    public function test_a_gift_period_opens_premium_features_to_free_accounts_between_two_dates(): void
    {
        $this->travel(2)->months();
        $gm = $this->gm->fresh();
        $this->assertFalse(Plans::allows($gm, 'maps'));

        Plans::save(['gift_starts_on' => today()->subDay()->toDateString(), 'gift_ends_on' => today()->addDays(13)->toDateString()]);
        $this->assertTrue(Plans::allows($gm, 'maps'));
        $this->assertSame(1, Plans::maxCampaigns($gm), 'Le cadeau ne porte que sur les fonctions.');
        $this->actingAs($gm)->get(route('campaigns.index'))->assertSee('Période offerte');

        $this->travel(14)->days();
        $this->assertFalse(Plans::allows($gm, 'maps'));
        $this->actingAs($gm)->get(route('campaigns.index'))->assertDontSee('Période offerte');
    }

    public function test_the_admin_sets_a_gift_period(): void
    {
        $admin = User::where('is_admin', true)->firstOrFail();

        Livewire::actingAs($admin)->test(PlanSettings::class)
            ->set('giftStartsOn', '2026-12-20')->set('giftEndsOn', '2026-12-10')->call('save')->assertHasErrors('giftEndsOn')
            ->set('giftEndsOn', '2027-01-03')->call('save')->assertHasNoErrors();

        $this->assertSame(['2026-12-20', '2027-01-03'], [Plans::settings()['gift_starts_on'], Plans::settings()['gift_ends_on']]);
    }
}
