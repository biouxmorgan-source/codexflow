<?php

namespace Tests\Feature;

use App\Livewire\Documents\Show as DocumentShow;
use App\Livewire\Library\GameShow;
use App\Livewire\Library\WorldShow;
use App\Livewire\Scenarios\Index as ScenarioIndex;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Backlog après la recette v0.34.0 : le même éditeur que les fiches pour les descriptions
 * de jeu, de monde, de scénario et de document.
 */
class BacklogV11Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
    }

    public function test_scenario_summary_uses_the_rich_editor_and_renders_links(): void
    {
        $mira = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Mira']);

        Livewire::actingAs($this->gm)->test(ScenarioIndex::class, ['campaign' => $this->campaign])
            ->assertSeeHtml('class="rich-editor"')
            ->set('name', 'La lettre')
            ->set('summary', "**Prologue** : trouver [[Mira|{$mira->id}]].")
            ->call('save')
            ->assertHasNoErrors()
            ->assertSeeHtml('<strong>Prologue</strong>')
            ->assertSeeHtml(route('entities.show', [$this->campaign, $mira]))
            ->assertDontSee('**Prologue**');

        $this->assertSame([['id' => $mira->id, 'name' => 'Mira', 'type' => $mira->type->name]],
            Livewire::actingAs($this->gm)->test(ScenarioIndex::class, ['campaign' => $this->campaign])->instance()->suggestEntities('mi'));
    }

    public function test_document_description_uses_the_rich_editor(): void
    {
        Storage::fake('local');
        $document = new Document([
            'title' => 'Lettre', 'disk' => Document::DISK, 'original_name' => 'lettre.pdf', 'mime_type' => 'application/pdf', 'size' => 10,
            'path' => UploadedFile::fake()->create('lettre.pdf', 1, 'application/pdf')->store('documents', Document::DISK),
        ]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        Livewire::actingAs($this->gm)->test(DocumentShow::class, ['campaign' => $this->campaign, 'document' => $document])
            ->assertSeeHtml('class="rich-editor"')
            ->set('description', "- d'une main tremblante\n- **signée**")
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame("- d'une main tremblante\n- **signée**", $document->fresh()->description);
    }

    public function test_game_and_world_descriptions_use_the_rich_editor_without_entry_links(): void
    {
        $this->campaign->gameSystem->update(['description' => 'Un jeu **de brume**.']);

        foreach ([[GameShow::class, ['gameSystem' => $this->campaign->gameSystem]], [WorldShow::class, ['world' => $this->campaign->world]]] as [$component, $params]) {
            $page = Livewire::actingAs($this->gm)->test($component, $params)->set('editing', true)
                ->assertSeeHtml('class="rich-editor"')
                ->assertDontSee('[[ ]]');
            $this->assertSame([], $page->instance()->suggestEntities('a'));
        }

        Livewire::actingAs($this->gm)->test(GameShow::class, ['gameSystem' => $this->campaign->gameSystem])
            ->assertSeeHtml('<strong>de brume</strong>');
    }
}
