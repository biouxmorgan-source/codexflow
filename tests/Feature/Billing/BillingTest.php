<?php

namespace Tests\Feature\Billing;

use App\Models\Campaign;
use App\Models\User;
use App\Support\Billing\Billing;
use App\Support\Plans\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test';

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(); // le premier compte administre l'installation
        config([
            'services.stripe.secret' => 'sk_test_x',
            'services.stripe.webhook_secret' => self::SECRET,
            'services.stripe.prices' => ['monthly' => 'price_month', 'yearly' => 'price_year'],
        ]);
    }

    public function test_the_trial_starts_with_the_first_campaign_owned_not_at_sign_up(): void
    {
        $player = User::factory()->create();
        $this->travel(30)->days();
        Campaign::factory()->create()->members()->attach($player, ['role' => 'player']);

        $this->assertNull($player->fresh()->trial_started_at);
        $this->assertSame(Plans::FREE, Plans::effective($player->fresh()));

        Campaign::factory()->for($player, 'owner')->create();
        $player->refresh();

        $this->assertTrue($player->trial_started_at->isToday());
        $this->assertSame(Plans::TRIAL, Plans::effective($player));
        $this->assertNull(Plans::maxCampaigns($player));

        $this->travel(6 * 7 + 1)->days();
        $this->assertSame(Plans::FREE, Plans::effective($player->fresh()));
    }

    public function test_a_trial_opens_features_the_free_plan_lacks(): void
    {
        Plans::save(['free_features' => []]);
        $gm = User::factory()->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create();

        $this->actingAs($gm)->get(route('maps.index', $campaign))->assertOk();

        Plans::save(['trial_weeks' => 0]);
        $this->actingAs($gm)->get(route('maps.index', $campaign))->assertForbidden();
    }

    public function test_a_paid_subscription_makes_the_account_premium_until_the_end_of_the_period(): void
    {
        $user = User::factory()->create();
        $end = now()->addMonth()->startOfDay()->timestamp;

        $this->webhook('evt_1', 'customer.subscription.created', $this->subscription($user, 'active', $end))->assertOk();

        $user->refresh();
        $this->assertSame(Plans::PREMIUM, Plans::effective($user));
        $this->assertSame('sub_1', $user->stripe_subscription_id);
        $this->assertSame('cus_1', $user->stripe_customer_id);
        $this->assertSame(now()->addMonth()->toDateString(), $user->plan_ends_at->toDateString());

        // Résilié : le premium reste jusqu'à la fin de la période, puis la formule redevient gratuite.
        $this->webhook('evt_2', 'customer.subscription.deleted', $this->subscription($user, 'canceled', $end, ['ended_at' => now()->subDay()->timestamp]))->assertOk();
        $this->assertSame(Plans::FREE, Plans::effective($user->fresh()));

        // Un ancien événement arrivé en retard ne rend pas le premium.
        $this->webhook('evt_3', 'customer.subscription.updated', $this->subscription($user, 'active', $end))->assertOk();
        $this->assertSame(Plans::FREE, Plans::effective($user->fresh()));
    }

    public function test_the_webhook_refuses_a_bad_signature_and_applies_an_event_once(): void
    {
        $user = User::factory()->create();
        $payload = $this->event('evt_1', 'customer.subscription.created', $this->subscription($user, 'active', now()->addMonth()->timestamp));

        $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 't=1,v1=bad', 'CONTENT_TYPE' => 'application/json'], $payload)->assertStatus(400);
        $this->assertSame(Plans::FREE, Plans::effective($user->fresh()));

        $this->signed($payload)->assertOk();
        $user->forceFill(['plan' => Plans::FREE])->save();
        $this->signed($payload)->assertOk();

        $this->assertSame(Plans::FREE, $user->fresh()->plan);
    }

    public function test_preferences_offer_premium_and_the_portal_only_when_stripe_is_set_up(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('preferences'))
            ->assertSee('Passer Premium : Mensuel')
            ->assertSee('Passer Premium : Annuel')
            ->assertDontSee('Gérer mon abonnement');

        $user->forceFill(['stripe_customer_id' => 'cus_1', 'stripe_subscription_id' => 'sub_1', 'subscription_status' => 'active', 'plan' => Plans::PREMIUM, 'plan_ends_at' => now()->addMonth()])->save();
        $this->actingAs($user)->get(route('preferences'))
            ->assertDontSee('Passer Premium')
            ->assertSee('Gérer mon abonnement');

        config(['services.stripe.secret' => null]);
        $this->actingAs($user)->get(route('preferences'))->assertDontSee('Gérer mon abonnement');
        $this->actingAs($user)->post(route('billing.checkout', 'monthly'))->assertNotFound();
    }

    public function test_checkout_sends_the_account_to_stripe(): void
    {
        $user = User::factory()->create();
        $fake = new class extends Billing
        {
            public ?string $interval = null;

            public function checkoutUrl(User $user, string $interval): string
            {
                $this->interval = $interval;

                return 'https://checkout.stripe.com/c/pay/test';
            }
        };
        $this->app->instance(Billing::class, $fake);

        $this->actingAs($user)->post(route('billing.checkout', 'yearly'))->assertRedirect('https://checkout.stripe.com/c/pay/test');
        $this->assertSame('yearly', $fake->interval);
        $this->actingAs($user)->get(route('billing.success'))->assertRedirect(route('preferences'));
    }

    /** @return array<string, mixed> */
    private function subscription(User $user, string $status, int $periodEnd, array $extra = []): array
    {
        return $extra + [
            'id' => 'sub_1',
            'object' => 'subscription',
            'customer' => 'cus_1',
            'status' => $status,
            'start_date' => now()->timestamp,
            'cancel_at' => null,
            'metadata' => ['user_id' => (string) $user->id],
            'items' => ['object' => 'list', 'data' => [['id' => 'si_1', 'object' => 'subscription_item', 'current_period_end' => $periodEnd]]],
        ];
    }

    private function event(string $id, string $type, array $object): string
    {
        return json_encode(['id' => $id, 'object' => 'event', 'type' => $type, 'data' => ['object' => $object]]);
    }

    private function webhook(string $id, string $type, array $object)
    {
        return $this->signed($this->event($id, $type, $object));
    }

    private function signed(string $payload)
    {
        $time = time();
        $signature = hash_hmac('sha256', $time.'.'.$payload, self::SECRET);

        return $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}", 'CONTENT_TYPE' => 'application/json'], $payload);
    }
}
