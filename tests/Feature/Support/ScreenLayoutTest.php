<?php

namespace Tests\Feature\Support;

use App\Enums\CampaignRole;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Maps\Show as MapShow;
use App\Livewire\Sessions\Live;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\TableMap;
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
            ->assertSee('Largeur de « Musique »')
            ->call('saveLayout', [
                ['id' => 'music', 'column' => 1, 'span' => 3],
                ...$this->place(1, 2, 'now', 'cards', 'secrets', 'rules-docs', 'notes'),
                ...$this->place(3, 1, 'pins', 'to-play', 'table', 'give'),
            ])
            ->call('hideBlock', 'give');

        $layout = ScreenLayout::for($this->gm->fresh(), 'session');
        $this->assertSame(['music', 'now', 'cards', 'secrets', 'rules-docs', 'notes', 'pins', 'to-play', 'table', 'give'], array_column($layout['blocks'], 'id'));
        $this->assertSame(['id' => 'music', 'column' => 1, 'span' => 3], $layout['blocks'][0]);
        $this->assertSame(['give'], $layout['hidden']);

        $this->actingAs($this->gm->fresh())->get(route('sessions.live', $this->campaign))->assertOk()
            ->assertSeeInOrder(['data-block="music" data-column="1" data-span="3"', 'data-block="now"', 'data-block="pins"', 'data-block="to-play"'], false)
            ->assertDontSee('data-block="give"', false);

        // Le co-MJ garde la disposition par défaut.
        $this->assertFalse(ScreenLayout::for($this->coGm->fresh(), 'session')['custom']);
        $this->actingAs($this->coGm)->get(route('sessions.live', $this->campaign))->assertOk()
            ->assertSeeInOrder(['data-block="now" data-column="1" data-span="2"', 'data-block="notes"', 'data-block="to-play" data-column="3" data-span="1"', 'data-block="music"', 'data-block="give"'], false);
    }

    public function test_hidden_blocks_come_back_and_default_layout_resets(): void
    {
        $component = Livewire::actingAs($this->gm)
            ->test(CampaignShow::class, ['campaign' => $this->campaign])
            ->call('toggleArranging')
            ->call('hideBlock', 'tools')
            ->assertSee('Afficher « Outils de la campagne »')
            // « Outils », masqué, n'est pas envoyé : il garde sa place.
            ->call('saveLayout', [['id' => 'universe', 'column' => 1, 'span' => 2], ['id' => 'session', 'column' => 3, 'span' => 1]]);

        $layout = ScreenLayout::for($this->gm->fresh(), 'campaign');
        $this->assertSame(['universe', 'session', 'zones', 'tools', 'features', 'duplicate', 'export'], array_column($layout['blocks'], 'id'));
        $this->assertSame([2, 1, 3], array_column(array_slice($layout['blocks'], 0, 3), 'span'));
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
        ScreenLayout::save($this->gm, 'session', [
            ['id' => 'now', 'column' => 2, 'span' => 2],
            ['id' => 'now', 'column' => 1, 'span' => 1],
            ['id' => 'admin', 'column' => 1, 'span' => 1],
            42,
            ['id' => 'notes', 'column' => 9, 'span' => 7],
            ['id' => 'pins', 'column' => 'x', 'span' => null],
        ], ['secret-stuff', 'music']);

        $layout = ScreenLayout::for($this->gm->fresh(), 'session');
        // Un bloc oublié reprend sa place par défaut, après ceux qui le précèdent normalement.
        $this->assertSame(['now', 'cards', 'secrets', 'rules-docs', 'notes', 'to-play', 'music', 'table', 'give', 'pins'], array_column($layout['blocks'], 'id'));
        $this->assertSame(['id' => 'now', 'column' => 2, 'span' => 2], $layout['blocks'][0]);
        // Trop large, trop à droite : ramené dans les trois colonnes.
        $this->assertSame(['id' => 'notes', 'column' => 1, 'span' => 3], $layout['blocks'][4]);
        $this->assertSame(['id' => 'pins', 'column' => 1, 'span' => 1], $layout['blocks'][9]);
        $this->assertSame(['music'], $layout['hidden']);
    }

    public function test_a_layout_saved_by_columns_keeps_its_look(): void
    {
        // Disposition enregistrée avant le choix des largeurs : une colonne principale et une colonne de droite.
        $this->gm->forceFill(['preferences' => ['layouts' => ['session' => [
            'columns' => ['main' => ['music', 'now'], 'side' => ['pins', 'to-play']],
            'hidden' => ['give'],
        ]]]])->save();

        $layout = ScreenLayout::for($this->gm->fresh(), 'session');
        $this->assertSame(
            [...$this->place(1, 2, 'music', 'now', 'cards', 'secrets', 'rules-docs', 'notes'), ...$this->place(3, 1, 'pins', 'to-play', 'table', 'give')],
            $layout['blocks'],
        );
        $this->assertSame(['give'], $layout['hidden']);
    }

    public function test_the_gm_arranges_the_blocks_of_a_map_page(): void
    {
        $document = new Document(['title' => 'Crypte', 'disk' => 'local', 'path' => 'documents/map.png', 'original_name' => 'map.png', 'mime_type' => 'image/png', 'size' => 10]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();
        $map = new TableMap(['name' => 'Crypte', 'grid_size' => 20]);
        $map->forceFill(['width' => 400, 'height' => 300]);
        $map->campaign()->associate($this->campaign);
        $map->document()->associate($document);
        $map->save();
        $map->refresh();

        Livewire::actingAs($this->gm)
            ->test(MapShow::class, ['campaign' => $this->campaign, 'map' => $map])
            ->assertSee('Organiser l’écran')
            ->call('toggleArranging')
            ->assertSee('Déplacer « Carte »')
            ->assertSee('Largeur de « Jetons »')
            ->call('saveLayout', [['id' => 'map', 'column' => 1, 'span' => 3], ...$this->place(1, 1, 'tokens', 'templates', 'grid')])
            ->call('hideBlock', 'name');

        $layout = ScreenLayout::for($this->gm->fresh(), 'map');
        $this->assertSame(['map', 'tokens', 'templates', 'initiative', 'grid', 'name'], array_column($layout['blocks'], 'id'));
        $this->assertSame(['name'], $layout['hidden']);

        $this->actingAs($this->gm->fresh())->get(route('maps.show', [$this->campaign, $map]))->assertOk()
            ->assertSeeInOrder(['data-block="map" data-column="1" data-span="3"', 'data-block="tokens" data-column="1" data-span="1"'], false)
            ->assertDontSee('id="map-name"', false);

        // Le co-MJ garde la carte à gauche et les réglages à droite.
        $this->actingAs($this->coGm)->get(route('maps.show', [$this->campaign, $map]))->assertOk()
            ->assertSeeInOrder(['data-block="map" data-column="1" data-span="2"', 'data-block="tokens" data-column="3" data-span="1"', 'id="map-name"'], false);
    }

    /** @return list<array{id: string, column: int, span: int}> */
    private function place(int $column, int $span, string ...$ids): array
    {
        return array_map(fn ($id) => ['id' => $id, 'column' => $column, 'span' => $span], $ids);
    }
}
