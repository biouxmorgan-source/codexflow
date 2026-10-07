<?php

namespace Tests\Feature\Table;

use App\Actions\Duplication\DuplicateCampaign;
use App\Enums\CampaignRole;
use App\Livewire\Maps\Index;
use App\Livewire\Maps\Show;
use App\Livewire\Sessions\Live;
use App\Livewire\Table\Remote;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\MapToken;
use App\Models\TableMap;
use App\Models\User;
use App\Support\TableDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MapsTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);
    }

    private function image(string $title = 'Le manoir', int $width = 400, int $height = 300): Document
    {
        $path = UploadedFile::fake()->image('map.png', $width, $height)->store('documents', 'local');
        $document = new Document(['title' => $title, 'disk' => 'local', 'path' => $path, 'original_name' => 'map.png', 'mime_type' => 'image/png', 'size' => 10]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        return $document;
    }

    private function map(): TableMap
    {
        $document = $this->image();

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('documentId', (string) $document->id)
            ->assertSet('name', 'Le manoir')
            ->call('create')
            ->assertHasNoErrors();

        return TableMap::sole();
    }

    private function token(TableMap $map, string $label, bool $hidden, ?Entity $entity = null): MapToken
    {
        $token = new MapToken(['label' => $label, 'x' => 100, 'y' => 100, 'size' => 1, 'hidden' => $hidden, 'show_label' => true]);
        $token->entity()->associate($entity);
        $map->tokens()->save($token);

        return $token;
    }

    public function test_the_game_master_turns_an_image_into_a_map_and_only_they_prepare_it(): void
    {
        $map = $this->map();

        $this->assertSame([400, 300], [$map->width, $map->height]);
        $this->assertSame(20.0, $map->grid_size);
        $this->assertFalse($map->grid_enabled);

        $this->actingAs($this->gm)->get(route('maps.show', [$this->campaign, $map]))->assertOk()->assertSee('Le manoir');
        $this->actingAs($this->gm)->get(route('table.remote', $this->campaign))->assertOk();

        $this->actingAs($this->player)->get(route('maps.index', $this->campaign))->assertForbidden();
        $this->actingAs($this->player)->get(route('maps.show', [$this->campaign, $map]))->assertForbidden();
        $this->actingAs($this->player)->get(route('table.remote', $this->campaign))->assertForbidden();

        // Une carte d'une autre campagne n'est pas accessible par cette adresse.
        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->actingAs($this->gm)->get(route('maps.show', [$other, $map]))->assertNotFound();
    }

    /** Parcours : carte à l'écran de table, jetons masqués invisibles des joueurs, puis révélés. */
    public function test_hidden_tokens_never_reach_the_table_screen(): void
    {
        $map = $this->map();
        $npc = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'Le majordome', 'image_path' => 'entities/majordome.png']);
        Storage::disk('local')->put('entities/majordome.png', 'png');
        $hero = $this->token($map, 'Chang Mei', false);
        $butler = $this->token($map, 'Le majordome', true, $npc);

        $this->assertTrue(TableDisplay::show($this->campaign, 'map', $map->id));
        $this->campaign->forceFill(['table_shared' => true])->save();

        $this->actingAs($this->player)->get(route('table.screen', $this->campaign))
            ->assertOk()->assertSee('Chang Mei')->assertDontSee('Le majordome');
        $this->actingAs($this->player)->get(route('table.file', $this->campaign))->assertOk();
        $this->actingAs($this->player)->get(route('table.token', [$this->campaign, $butler]))->assertNotFound();

        Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'map' => $map])
            ->assertSee('Le majordome')
            ->call('toggleToken', $butler->id);

        $this->actingAs($this->player)->get(route('table.screen', $this->campaign))->assertSee('Le majordome');
        $this->actingAs($this->player)->get(route('table.token', [$this->campaign, $butler]))->assertOk();
        $this->actingAs($this->player)->get(route('table.token', [$this->campaign, $hero]))->assertNotFound();

        // La carte n'est plus affichée : plus rien n'est servi.
        TableDisplay::clear($this->campaign);
        $this->actingAs($this->player)->get(route('table.token', [$this->campaign, $butler]))->assertNotFound();
    }

    public function test_grid_scale_view_ruler_and_tokens_are_set_from_the_editor(): void
    {
        $map = $this->map();
        $npc = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'Gobelin']);

        $editor = Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'map' => $map])
            ->set('gridEnabled', true)
            ->set('gridSize', '50')
            ->set('gridOffsetX', '10')
            ->set('scaleValue', '1.5')
            ->set('scaleUnit', 'm')
            ->assertHasNoErrors()
            ->call('setView', 5000, -20, 20)
            ->set('tokenEntityId', $npc->id)
            ->call('addToken')
            ->call('addToken')
            ->assertHasErrors('tokenLabel')
            ->call('setRuler', 0, 0, 150, 0);

        $map->refresh();
        $this->assertTrue($map->grid_enabled);
        $this->assertSame([50.0, 10.0, 1.5, 'm'], [$map->grid_size, $map->grid_offset_x, $map->scale_value, $map->scale_unit]);
        // Vue bornée à la carte et au zoom ×8.
        $this->assertSame([400.0, 0.0, 8.0], [$map->view_x, $map->view_y, $map->view_zoom]);
        $this->assertSame('4,5 m', $map->rulerLabel());

        $token = $map->tokens()->sole();
        $this->assertSame('Gobelin', $token->label);
        $this->assertTrue($token->hidden, 'Un nouveau jeton arrive masqué.');

        $editor->call('moveToken', $token->id, 120, 80)->call('resizeToken', $token->id, '2')->call('toggleLabel', $token->id);
        $token->refresh();
        $this->assertSame([120.0, 80.0, 2.0, false], [$token->x, $token->y, $token->size, $token->show_label]);

        $editor->call('resizeToken', $token->id, '7')->assertHasErrors('size');

        $map->update(['scale_value' => null]);
        $this->assertSame('3 cases', $map->fresh()->rulerLabel());

        $editor->call('clearRuler');
        $this->assertNull($map->fresh()->ruler);

        $editor->call('deleteToken', $token->id);
        $this->assertSame(0, $map->tokens()->count());
    }

    /** Parcours : le MJ pilote l'écran depuis son téléphone pendant la scène. */
    public function test_the_remote_steps_through_the_scene_and_drives_the_shown_map(): void
    {
        $map = $this->map();
        $butler = $this->token($map, 'Le majordome', true);
        $npc = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'Aldric']);
        $handout = $this->image('Lettre anonyme');
        $scene = $this->campaign->scenarios()->create(['name' => 'Le manoir'])->scenes()->create(['name' => 'Le hall', 'position' => 1]);
        $scene->entities()->attach($npc, ['position' => 0]);
        $scene->documents()->attach($handout);

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])->call('start');

        $remote = Livewire::actingAs($this->gm)->test(Remote::class, ['campaign' => $this->campaign])
            ->assertSee(['Le hall', 'Aldric', 'Lettre anonyme'])
            ->call('next');
        $this->assertTrue(TableDisplay::isShowing($this->campaign->fresh(), 'entity', $npc->id));

        $remote->call('next');
        $this->assertTrue(TableDisplay::isShowing($this->campaign->fresh(), 'document', $handout->id));

        $remote->call('show', 'map', $map->id)
            ->call('zoom', true)
            ->call('pan', 1, 0)
            ->call('toggleToken', $butler->id);

        $map->refresh();
        $this->assertSame(1.25, $map->view_zoom);
        $this->assertSame(280.0, $map->view_x);
        $this->assertFalse($butler->fresh()->hidden);

        $remote->call('fit')->call('clear');
        $this->assertNull($this->campaign->fresh()->table_display);

        // Plus de carte affichée : les commandes de carte ne font rien.
        $remote->call('zoom', true)->assertNotFound();
    }

    public function test_deleting_a_shown_map_empties_the_screen_and_duplication_copies_maps(): void
    {
        $map = $this->map();
        $this->token($map, 'Chang Mei', false);
        $npc = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $this->campaign->id, 'world_id' => null, 'name' => 'Gobelin']);
        $this->token($map, 'Gobelin', true, $npc);
        $map->forceFill(['ruler' => ['x1' => 0, 'y1' => 0, 'x2' => 10, 'y2' => 10], 'view_zoom' => 3])->save();

        $copy = app(DuplicateCampaign::class)->handle($this->campaign, $this->gm);
        $copied = $copy->maps()->with('tokens.entity')->sole();
        $this->assertSame('Le manoir', $copied->name);
        $this->assertNotSame($map->document_id, $copied->document_id);
        $this->assertNull($copied->ruler);
        $this->assertSame(1.0, $copied->fresh()->view_zoom);
        $this->assertSame(['Chang Mei', 'Gobelin'], $copied->tokens->pluck('label')->all());
        $this->assertSame($copy->id, $copied->tokens[1]->entity->campaign_id);

        TableDisplay::show($this->campaign, 'map', $map->id);
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('delete', $map->id);
        $this->assertNull($this->campaign->fresh()->table_display);
        $this->assertSame(0, MapToken::whereNotIn('table_map_id', [$copied->id])->count());
    }
}
