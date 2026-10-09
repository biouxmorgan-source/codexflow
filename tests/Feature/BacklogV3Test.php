<?php

namespace Tests\Feature;

use App\Enums\CampaignRole;
use App\Livewire\Library\GameShow;
use App\Livewire\Library\WorldShow;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Models\World;
use App\Support\Plans\StorageUsage;
use App\Support\TableDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Évolutions de recette « PDF et documents » : lecteur pdf.js, feuille avec son nom d'origine,
 * PDF à la table, scénario dans « Utilisé par », image des pages jeu et monde.
 */
class BacklogV3Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
    }

    public function test_pdfs_open_in_the_built_in_viewer_on_the_document_page_the_sheet_and_the_table_screen(): void
    {
        $document = $this->document('Lettre de Mira');

        $this->actingAs($this->gm)->get(route('documents.show', [$this->campaign, $document]))
            ->assertOk()
            ->assertSee('x-data="pdfViewer(', false)
            ->assertSee(route('documents.file', $document))
            ->assertDontSee('<iframe', false);

        // La feuille de personnage garde son nom d'origine.
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
        $harvey->forceFill([
            'sheet_path' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')->store('character-sheets', PlayerCharacter::DISK),
            'sheet_name' => 'Fiche_Harvey_v2.pdf',
            'sheet_size' => 10240,
        ])->save();

        $this->actingAs($this->alex)->get(route('characters.show', [$this->campaign, $harvey]))
            ->assertOk()
            ->assertSee('Fiche_Harvey_v2.pdf')
            ->assertSee('x-data="pdfViewer(', false)
            ->assertDontSee('<iframe', false);

        // À la table : une page à la fois, ajustée à l'écran.
        TableDisplay::showDocument($this->campaign, $document);
        $this->actingAs($this->gm)->get(route('table.screen', $this->campaign))
            ->assertOk()
            ->assertSee("pdfViewer('".str_replace('/', '\\/', route('table.file', $this->campaign)), false)
            ->assertSee("'screen', 1, true)", false)
            ->assertDontSee('<iframe', false);
    }

    public function test_used_by_names_the_scenario_of_each_scene(): void
    {
        $document = $this->document('Plan de la cave');
        $scenario = $this->campaign->scenarios()->create(['name' => 'Une nuit au manoir']);
        $scene = $scenario->scenes()->create(['name' => 'La cave']);
        $scene->documents()->attach($document, ['position' => 1]);

        $this->actingAs($this->gm)->get(route('documents.show', [$this->campaign, $document]))
            ->assertSeeInOrder(['Utilisé par', 'La cave', '(Une nuit au manoir)']);
    }

    public function test_game_and_world_pages_take_an_image_that_only_their_owner_sees(): void
    {
        $world = World::factory()->for($this->gm, 'owner')->create();
        $game = $this->campaign->gameSystem;

        Livewire::actingAs($this->gm)->test(WorldShow::class, ['world' => $world])
            ->set('image', UploadedFile::fake()->image('carte.png', 400, 300))
            ->assertHasNoErrors();
        Livewire::actingAs($this->gm)->test(GameShow::class, ['gameSystem' => $game])
            ->set('image', UploadedFile::fake()->image('couverture.jpg', 300, 400))
            ->assertHasNoErrors();

        $world->refresh();
        $this->assertNotNull($world->image_path);
        Storage::disk('local')->assertExists($world->image_path);
        $this->assertGreaterThan(0, StorageUsage::bytes($this->gm, fresh: true));

        $this->actingAs($this->gm)->get(route('worlds.show', $world))->assertSee(route('worlds.image', $world));
        $this->actingAs($this->gm)->get(route('games.show', $game))->assertSee(route('games.image', $game));
        $this->actingAs($this->gm)->get(route('worlds.image', $world))->assertOk();
        $this->actingAs($this->alex)->get(route('worlds.image', $world))->assertForbidden();
        $this->actingAs($this->alex)->get(route('games.image', $game))->assertForbidden();

        // Un fichier qui n'est pas une image est refusé.
        Livewire::actingAs($this->gm)->test(WorldShow::class, ['world' => $world])
            ->set('image', UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))
            ->assertHasErrors('image');

        // Retirer l'image efface le fichier.
        $path = $world->image_path;
        Livewire::actingAs($this->gm)->test(WorldShow::class, ['world' => $world])->call('removeImage');
        $this->assertNull($world->fresh()->image_path);
        Storage::disk('local')->assertMissing($path);

        // Supprimer un jeu efface aussi son image.
        $gamePath = $game->fresh()->image_path;
        Livewire::actingAs($this->alex)->test(WorldShow::class, ['world' => $world])->assertForbidden();
        $this->campaign->delete();
        $game->fresh()->delete();
        Storage::disk('local')->assertMissing($gamePath);
    }

    private function document(string $title): Document
    {
        $document = new Document([
            'title' => $title,
            'disk' => Document::DISK,
            'path' => UploadedFile::fake()->create($title.'.pdf', 10, 'application/pdf')->store('documents', Document::DISK),
            'original_name' => $title.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10240,
        ]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        return $document;
    }
}
