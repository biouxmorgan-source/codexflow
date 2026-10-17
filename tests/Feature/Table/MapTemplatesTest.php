<?php

namespace Tests\Feature\Table;

use App\Enums\CampaignRole;
use App\Livewire\Maps\Show;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\MapTemplate;
use App\Models\MapToken;
use App\Models\TableMap;
use App\Models\User;
use App\Support\TableDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Gabarits de zone : le MJ pose un cercle, un cône… et voit quels jetons sont pris dedans. */
class MapTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    private TableMap $map;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['table_shared' => true]);
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);

        $path = UploadedFile::fake()->image('map.png', 1000, 1000)->store('documents', 'local');
        $document = new Document(['title' => 'Plaine', 'disk' => 'local', 'path' => $path, 'original_name' => 'map.png', 'mime_type' => 'image/png', 'size' => 10]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        $this->map = new TableMap(['name' => 'Plaine', 'grid_size' => 10, 'scale_value' => 1.5, 'scale_unit' => 'm']);
        $this->map->forceFill(['width' => 1000, 'height' => 1000]);
        $this->map->campaign()->associate($this->campaign);
        $this->map->document()->associate($document);
        $this->map->save();
        $this->map->refresh();
    }

    private function token(string $label, float $x, float $y): MapToken
    {
        return $this->map->tokens()->create(['label' => $label, 'x' => $x, 'y' => $y, 'size' => 1, 'hidden' => false, 'show_label' => true]);
    }

    public function test_shapes_catch_the_tokens_whose_centre_is_inside(): void
    {
        $at = fn (string $shape, float $size, ?float $size2 = null, float $angle = 0) => new MapTemplate(['shape' => $shape, 'size' => $size, 'size2' => $size2, 'x' => 500, 'y' => 500, 'angle' => $angle]);

        // Cercle de rayon 3 cases (30 px).
        $this->assertTrue($at('circle', 3)->contains(530, 500, 10));
        $this->assertFalse($at('circle', 3)->contains(522, 522, 10));
        // Carré de 4 cases, centré : ±20 px.
        $this->assertTrue($at('square', 4)->contains(519, 481, 10));
        $this->assertFalse($at('square', 4)->contains(525, 500, 10));
        // Rectangle 6 × 2, tourné de 90° : il s'étire à la verticale.
        $this->assertTrue($at('rectangle', 6, 2, 90)->contains(505, 528, 10));
        $this->assertFalse($at('rectangle', 6, 2, 90)->contains(528, 505, 10));
        // Cône de 6 cases vers la droite : large de 3 cases de part et d'autre au bout.
        $this->assertTrue($at('cone', 6)->contains(555, 525, 10));
        $this->assertFalse($at('cone', 6)->contains(520, 515, 10));
        $this->assertFalse($at('cone', 6)->contains(480, 500, 10), 'Rien derrière le point de départ.');
        // Ligne de 10 × 1 vers le bas.
        $this->assertTrue($at('line', 10, 1, 90)->contains(503, 590, 10));
        $this->assertFalse($at('line', 10, 1, 90)->contains(510, 590, 10));
    }

    public function test_the_game_master_places_a_template_and_sees_who_is_caught(): void
    {
        $this->map->setView(500, 500, 1);
        $this->map->save();
        $this->token('Harvey', 520, 500);
        $this->token('Loup', 700, 700);
        TableDisplay::show($this->campaign, 'map', $this->map->id);

        $editor = Livewire::actingAs($this->gm)->test(Show::class, ['campaign' => $this->campaign, 'map' => $this->map])
            ->set('templateShape', 'circle')
            ->set('templateSize', '3')
            ->call('addTemplate')
            ->assertHasNoErrors()
            ->assertSee('Cercle, rayon 4,5 m')
            ->assertSee('1 touché')
            ->assertSee('Harvey');

        $template = $this->map->templates()->sole();
        $this->assertSame([500.0, 500.0, false], [$template->x, $template->y, $template->hidden]);

        $editor->call('moveTemplate', $template->id, 690, 690)->assertSee('Loup')->assertDontSee('Harvey, Loup');
        $editor->call('rotateTemplate', $template->id, '-90')->call('resizeTemplate', $template->id, '5');
        $template->refresh();
        $this->assertSame([270.0, 5.0], [$template->angle, $template->size]);

        $editor->set('templateShape', 'line')->set('templateSize2', '')->call('addTemplate')->assertHasErrors('templateSize2');
        $editor->call('resizeTemplate', $template->id, '0')->assertHasErrors('size');

        // Visible à la table ; masqué, il disparaît de l'écran des joueurs.
        $this->actingAs($this->player)->get(route('table.screen', $this->campaign))->assertSee('template-'.$template->id, false);
        $this->actingAs($this->gm);
        $editor->call('toggleTemplate', $template->id);
        $this->actingAs($this->player)->get(route('table.screen', $this->campaign))->assertDontSee('template-'.$template->id, false);

        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'map' => $this->map])->assertForbidden();

        $this->actingAs($this->gm);
        $editor->call('deleteTemplate', $template->id);
        $this->assertSame(0, $this->map->templates()->count());
    }
}
