<?php

namespace Tests\Feature\Demo;

use App\Livewire\Admin\DemoLibrary as AdminDemoLibrary;
use App\Livewire\Campaigns\DemoLibrary;
use App\Models\Campaign;
use App\Models\DemoLibraryItem;
use App\Models\Entity;
use App\Models\User;
use App\Support\Plans\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DemoLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Campaign $source;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        // Le premier compte de l'installation l'administre.
        $this->admin = User::factory()->create();
        $this->source = Campaign::factory()->for($this->admin, 'owner')->create(['name' => 'Les Cryptes d’Ambre']);
        Entity::factory()->create(['campaign_id' => $this->source->id, 'world_id' => null, 'name' => 'Sire Odran', 'gm_notes' => 'Il ment.']);
    }

    private function publish(array $fields = []): DemoLibraryItem
    {
        Livewire::actingAs($this->admin)->test(AdminDemoLibrary::class)
            ->call('create')
            ->set('campaignId', (string) $this->source->id)
            ->set('title', $fields['title'] ?? 'Les Cryptes d’Ambre')
            ->set('gameName', 'Ambre & Acier')
            ->set('publisher', 'Éditions du Lac')
            ->set('storeUrl', $fields['storeUrl'] ?? 'https://boutique.example/ambre')
            ->set('locale', 'fr')
            ->call('save')
            ->assertHasNoErrors();

        $item = DemoLibraryItem::latest('id')->firstOrFail();
        $item->update(['published' => true]);

        return $item;
    }

    public function test_only_the_administrator_manages_the_library(): void
    {
        $gm = User::factory()->create();

        $this->actingAs($gm)->get(route('admin.demo-library'))->assertForbidden();
        Livewire::actingAs($gm)->test(AdminDemoLibrary::class)->assertForbidden();
    }

    public function test_the_administrator_turns_a_campaign_into_a_hidden_demo_then_publishes_it(): void
    {
        $component = Livewire::actingAs($this->admin)->test(AdminDemoLibrary::class)
            ->call('create')
            ->set('campaignId', (string) $this->source->id)
            ->set('title', 'Les Cryptes d’Ambre')
            ->set('gameName', 'Ambre & Acier')
            ->set('storeUrl', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors('storeUrl')
            ->set('storeUrl', 'https://boutique.example/ambre')
            ->call('save')
            ->assertHasNoErrors();

        $item = DemoLibraryItem::sole();
        $this->assertFalse($item->published, 'Une nouvelle démo reste cachée tant qu’elle n’est pas publiée.');
        $this->assertSame($this->source->id, $item->source_campaign_id);
        Storage::disk('local')->assertExists($item->archive_path);
        $this->assertGreaterThan(0, $item->archive_size);

        $gm = User::factory()->create();
        $this->actingAs($gm)->get(route('demo-library'))->assertOk()->assertDontSee('Les Cryptes d’Ambre', false);

        $this->actingAs($this->admin);
        $component->call('togglePublished', $item->id);
        $this->actingAs($gm)->get(route('demo-library'))->assertSee('Les Cryptes d’Ambre', false)->assertSee('https://boutique.example/ambre');

        // Mettre à jour reprend le contenu de la campagne et remplace l'ancienne archive.
        $old = $item->archive_path;
        $this->actingAs($this->admin);
        $component->call('refresh', $item->id);
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($item->fresh()->archive_path);

        $component->call('delete', $item->id);
        Storage::disk('local')->assertMissing($item->fresh()?->archive_path ?? $item->archive_path);
        $this->assertSame(0, DemoLibraryItem::count());
    }

    public function test_a_gm_loads_their_own_copy_with_a_link_to_the_store(): void
    {
        $item = $this->publish();
        $gm = User::factory()->create();

        Livewire::actingAs($gm)->test(DemoLibrary::class)
            ->call('load', $item->id)
            ->assertHasNoErrors()
            ->assertRedirect();

        $copy = Campaign::where('user_id', $gm->id)->sole();
        $this->assertSame('Les Cryptes d’Ambre', $copy->name);
        $this->assertTrue($copy->is_demo);
        $this->assertSame($item->id, $copy->demo_library_item_id);
        $this->assertTrue($copy->isGameMaster($gm));
        $this->assertSame('Il ment.', Entity::where('campaign_id', $copy->id)->sole()->gm_notes);
        $this->assertSame(1, $item->fresh()->loads_count);

        // Sa copie lui appartient : la modifier ne touche ni la démo ni la campagne d'origine.
        $copy->update(['name' => 'Ma partie']);
        $this->assertSame('Les Cryptes d’Ambre', $this->source->fresh()->name);

        $this->actingAs($gm)->get(route('campaigns.show', $copy))
            ->assertSee('Démo de « Ambre &amp; Acier ».', false)
            ->assertSee('https://boutique.example/ambre');
    }

    public function test_loading_a_demo_follows_the_plan_limits_and_hidden_demos_stay_out_of_reach(): void
    {
        $item = $this->publish();
        Plans::save(['trial_weeks' => 0, 'free_max_campaigns' => 1]);
        $gm = User::factory()->create();
        Campaign::factory()->for($gm, 'owner')->create();

        Livewire::actingAs($gm)->test(DemoLibrary::class)
            ->call('load', $item->id)
            ->assertHasErrors('demo');
        $this->assertSame(1, $gm->ownedCampaigns()->count());

        $other = User::factory()->create();
        $item->update(['published' => false]);
        Livewire::actingAs($other)->test(DemoLibrary::class)->call('load', $item->id)->assertNotFound();
        $this->assertSame(0, $other->ownedCampaigns()->count());
    }

    public function test_a_demo_account_waits_until_it_is_unlocked(): void
    {
        $item = $this->publish();
        $visitor = User::factory()->create(['plan' => Plans::DEMO]);

        Livewire::actingAs($visitor)->test(DemoLibrary::class)
            ->assertSee('La bibliothèque s’ouvre quand le compte est débloqué.', false)
            ->call('load', $item->id)
            ->assertForbidden();
    }
}
