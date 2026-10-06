<?php

namespace Tests\Feature\Campaigns;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_are_defined_per_campaign_not_per_account(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $alicesCampaign = Campaign::factory()->for($alice, 'owner')->create();
        $bobsCampaign = Campaign::factory()->for($bob, 'owner')->create();
        $bobsCampaign->members()->attach($alice, ['role' => CampaignRole::Player->value]);

        $this->assertTrue($alice->can('update', $alicesCampaign));
        $this->assertTrue($alice->can('view', $bobsCampaign));
        $this->assertFalse($alice->can('update', $bobsCampaign));
        $this->assertSame(CampaignRole::Player, $bobsCampaign->roleOf($alice));
    }

    public function test_non_members_cannot_view_a_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertFalse(User::factory()->create()->can('view', $campaign));
    }
}
