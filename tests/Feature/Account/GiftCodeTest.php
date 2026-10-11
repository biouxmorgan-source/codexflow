<?php

namespace Tests\Feature\Account;

use App\Livewire\Account\Rewards;
use App\Livewire\Admin\Users;
use App\Models\GiftCode;
use App\Models\User;
use App\Support\GiftCodes;
use App\Support\Plans\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Espace « Mes récompenses » : codes cadeaux des contributeurs du Kickstarter. */
class GiftCodeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $backer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_admin' => true])->save();
        $this->backer = User::factory()->create(['name' => 'Léa', 'founder_since' => '2027-02-25']);
        Plans::save(['trial_weeks' => 0]);
    }

    public function test_the_administrator_gives_codes_that_the_backer_sees_and_shares(): void
    {
        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $this->backer->id)
            ->set('backerReward', 'The Whole Table')
            ->call('save')
            ->call('edit', $this->backer->id)
            ->set('giftCount', '4')->set('giftMonths', '12')
            ->call('issueGifts')
            ->assertHasNoErrors();

        $this->assertSame(4, $this->backer->giftCodes()->count());
        $code = $this->backer->giftCodes()->first();
        $this->assertMatchesRegularExpression('/^SW-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $code->code);
        $this->assertSame('The Whole Table', $code->note);

        $this->actingAs($this->backer)->get(route('rewards'))->assertOk()
            ->assertSee('The Whole Table')
            ->assertSee('4 codes sur 4 restent à offrir', false)
            ->assertSee(route('gifts.show', $code->code));
    }

    public function test_a_friend_opens_the_link_signs_in_and_gets_premium_and_the_backer_sees_who(): void
    {
        [$gift] = GiftCodes::issue($this->backer, 1, 12);
        $friend = User::factory()->create(['name' => 'Noé']);

        $this->get(route('gifts.show', $gift->code))->assertOk()->assertSee('Léa')->assertSee('12 mois de SagaWyn Premium');
        $this->assertSame(route('gifts.show', $gift->code), session('url.intended'));

        $this->actingAs($friend)->post(route('gifts.redeem', $gift->code))->assertRedirect(route('rewards'));

        $friend->refresh();
        $this->assertSame(Plans::PREMIUM, Plans::effective($friend));
        $this->assertSame(today()->addMonthsNoOverflow(12)->toDateString(), $friend->plan_ends_at->toDateString());
        $this->assertSame($friend->id, $gift->fresh()->redeemed_by);

        $this->actingAs($this->backer)->get(route('rewards'))->assertSee('Utilisé par Noé');
        // Utilisé une seule fois.
        $other = User::factory()->create();
        $this->actingAs($other)->post(route('gifts.redeem', $gift->code))->assertSessionHas('problem', 'Ce code a déjà été utilisé.');
        $this->assertSame(Plans::FREE, Plans::effective($other->fresh()));
    }

    public function test_months_follow_the_current_premium_and_some_accounts_cannot_use_a_code(): void
    {
        $gifts = GiftCodes::issue($this->backer, 4, 6);
        $friend = User::factory()->create(['plan' => Plans::PREMIUM, 'plan_started_at' => today()->subMonth(), 'plan_ends_at' => today()->addMonths(2)]);

        Livewire::actingAs($friend)->test(Rewards::class)->set('code', strtolower(str_replace('-', ' ', $gifts[0]->code)))->call('redeem')->assertHasNoErrors();
        $this->assertSame(today()->addMonths(2)->addMonthsNoOverflow(6)->toDateString(), $friend->fresh()->plan_ends_at->toDateString());
        $this->assertSame(today()->subMonth()->toDateString(), $friend->fresh()->plan_started_at->toDateString());

        $lifetime = User::factory()->create(['plan' => Plans::PREMIUM, 'plan_ends_at' => null]);
        $subscriber = User::factory()->create(['subscription_status' => 'active', 'plan' => Plans::PREMIUM, 'plan_ends_at' => today()->addMonth()]);

        Livewire::actingAs($lifetime)->test(Rewards::class)->set('code', $gifts[1]->code)->call('redeem')->assertHasErrors('code');
        Livewire::actingAs($subscriber)->test(Rewards::class)->set('code', $gifts[1]->code)->call('redeem')->assertHasErrors('code');
        Livewire::actingAs($this->admin)->test(Rewards::class)->set('code', $gifts[1]->code)->call('redeem')->assertHasErrors('code');
        Livewire::actingAs($friend)->test(Rewards::class)->set('code', 'SW-AAAA-AAAA')->call('redeem')->assertHasErrors('code');
        $this->assertFalse($gifts[1]->fresh()->isRedeemed());
    }

    public function test_guessing_codes_is_limited(): void
    {
        $user = User::factory()->create();
        $test = Livewire::actingAs($user)->test(Rewards::class);

        foreach (range(1, 10) as $i) {
            $test->set('code', 'SW-AAAA-AAA'.$i)->call('redeem');
        }

        [$gift] = GiftCodes::issue($this->backer, 1, 6);
        $test->set('code', $gift->code)->call('redeem')->assertHasErrors('code');
        $this->assertFalse($gift->fresh()->isRedeemed());
    }

    public function test_the_command_gives_codes(): void
    {
        $this->artisan('sagawyn:gift-codes', ['email' => $this->backer->email, 'count' => 2, 'months' => 12, '--note' => 'Club'])->assertSuccessful();

        $this->assertSame(2, GiftCode::where('owner_id', $this->backer->id)->where('note', 'Club')->count());
    }
}
