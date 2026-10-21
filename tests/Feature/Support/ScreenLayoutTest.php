<?php

namespace Tests\Feature\Support;

use App\Enums\CampaignRole;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Sessions\Live;
use App\Models\Campaign;
use App\Models\User;
use App\Support\ScreenLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * « Organiser l'écran » : chaque MJ range les blocs de la page de campagne et du mode Session
 * pour lui seul. Purement visuel : les autres comptes gardent leur disposition.
 */
class ScreenLayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $coGm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->coGm = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->coGm, ['role' => CampaignRole::GameMaster->value]);
    }

    public function test_the_gm_moves_a_session_block_to_the_other_column_for_themselves_only(): void
    {
        $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now()]);

        Livewire::actingAs($this->gm)
            ->test(Live::class, ['campaign' => $this->campaign])
            ->call('toggleArranging')
            ->assertSee('Organiser l’écran')
            ->assertSee('Déplacer « Musique »')
            ->call('saveLayout', ['main' => ['music', 'now', 'cards', 'secrets', 'rules-docs', 'notes'], 'side' => ['pins', 'to-play', 'table', 'give']])
            ->call('hideBlock', 'give');

        $layout = ScreenLayout::for($this->gm->fresh(), 'session');
        $this->assertSame(['music', 'now', 'cards', 'secrets', 'rules-docs', 'notes'], $layout['columns']['main']);
        $this->assertSame(['pins', 'to-play', 'table', 'give'], $layout['columns']['side']);
        $this->assertSame(['give'], $layout['hidden']);

        $this->actingAs($this->gm->fresh())->get(route('sessions.live', $this->campaign))->assertOk()
            ->assertSeeInOrder(['data-block="music"', 'data-block="now"', 'data-block="pins"', 'data-block="to-play"'], false)
            ->assertDontSee('data-block="give"', false);

        // Le co-MJ garde la disposition par défaut.
        $this->assertFalse(ScreenLayout::for($this->coGm->fresh(), 'session')['custom']);
        $this->actingAs($this->coGm)->get(route('sessions.live', $this->campaign))->assertOk()
            ->assertSeeInOrder(['data-block="now"', 'data-block="notes"', 'data-block="to-play"', 'data-block="music"', 'data-block="give"'], false);
    }

    public function test_hidden_blocks_come_back_and_default_layout_resets(): void
    {
        $component = Livewire::actingAs($this->gm)
            ->test(CampaignShow::class, ['campaign' => $this->campaign])
            ->call('toggleArranging')
            ->call('hideBlock', 'tools')
            ->assertSee('Afficher « Outils de la campagne »')
            ->call('saveLayout', ['main' => ['universe', 'session']]);

        $this->assertSame(['universe', 'session', 'zones', 'tools', 'features', 'duplicate', 'export'], ScreenLayout::for($this->gm->fresh(), 'campaign')['columns']['main']);
        $this->actingAs($this->gm->fresh())->get(route('campaigns.show', $this->campaign))->assertOk()
            ->assertSeeInOrder(['data-block="universe"', 'data-block="session"'], false)
            ->assertDontSee('data-block="tools"', false);

        $component->call('showBlock', 'tools');
        $this->assertSame([], ScreenLayout::for($this->gm->fresh(), 'campaign')['hidden']);

        $component->call('resetLayout');
        $this->assertFalse(ScreenLayout::for($this->gm->fresh(), 'campaign')['custom']);
        $this->assertArrayNotHasKey('layouts', $this->gm->fresh()->preferences ?? []);
    }

    public function test_a_saved_layout_ignores_unknown_or_repeated_blocks_and_keeps_new_ones(): void
    {
        ScreenLayout::save($this->gm, 'session', ['main' => ['now', 'now', 'admin', 42], 'side' => ['notes'], 'other' => ['pins']], ['secret-stuff', 'music']);

        $layout = ScreenLayout::for($this->gm->fresh(), 'session');
        $this->assertSame(['now', 'cards', 'secrets', 'rules-docs'], $layout['columns']['main']);
        // Un bloc oublié reprend sa colonne par défaut, après ceux qui le précèdent normalement.
        $this->assertSame(['to-play', 'music', 'table', 'give', 'pins', 'notes'], $layout['columns']['side']);
        $this->assertSame(['music'], $layout['hidden']);
    }
}
