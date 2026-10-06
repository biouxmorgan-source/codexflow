<?php

namespace Tests\Feature\Campaigns;

use App\Enums\CampaignRole;
use App\Livewire\Campaigns\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_deletes_a_campaign_and_its_own_entities_but_keeps_the_world(): void
    {
        Storage::fake(Entity::FILES_DISK);

        $gm = User::factory()->create();
        $world = World::factory()->for($gm, 'owner')->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create(['world_id' => $world->id]);

        $worldEntity = Entity::factory()->for($gm, 'owner')->for($world)->create();
        $worldEntity->stateIn($campaign)->fill(['status' => 'mort'])->save();
        $local = Entity::factory()->for($gm, 'owner')->create([
            'campaign_id' => $campaign->id,
            'world_id' => null,
            'image_path' => UploadedFile::fake()->image('a.png')->store('entities', Entity::FILES_DISK),
        ]);

        Livewire::actingAs($gm)
            ->test(Show::class, ['campaign' => $campaign])
            ->assertSee('Supprimer la campagne')
            ->call('delete')
            ->assertRedirect(route('campaigns.index'));

        $this->assertModelMissing($campaign);
        $this->assertModelMissing($local);
        Storage::disk(Entity::FILES_DISK)->assertMissing($local->image_path);
        $this->assertModelExists($worldEntity);
        $this->assertModelExists($world);
        $this->assertSame(0, $worldEntity->campaignStates()->count());
    }

    public function test_a_co_game_master_cannot_delete_someone_elses_campaign(): void
    {
        $owner = User::factory()->create();
        $coGm = User::factory()->create();
        $campaign = Campaign::factory()->for($owner, 'owner')->create();
        $campaign->members()->attach($coGm, ['role' => CampaignRole::GameMaster->value]);

        Livewire::actingAs($coGm)
            ->test(Show::class, ['campaign' => $campaign])
            ->assertDontSee('Supprimer la campagne')
            ->call('delete')
            ->assertForbidden();

        $this->assertModelExists($campaign);
    }
}
