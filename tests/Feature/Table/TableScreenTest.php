<?php

namespace Tests\Feature\Table;

use App\Enums\CampaignRole;
use App\Livewire\Sessions\Live;
use App\Livewire\Table\Screen;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TableScreenTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Enfants de la Peur']);
    }

    private function document(string $title, string $mime = 'image/jpeg', ?Campaign $campaign = null): Document
    {
        $document = new Document(['title' => $title, 'disk' => 'local', 'path' => 'documents/x', 'original_name' => 'x', 'mime_type' => $mime, 'size' => 10]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($campaign ?? $this->campaign);
        $document->save();

        return $document;
    }

    public function test_only_the_game_master_opens_the_table_screen(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $this->actingAs($this->gm)->get(route('table.screen', $this->campaign))
            ->assertOk()
            ->assertSee('Les Enfants de la Peur')
            ->assertSee('Plein écran')
            ->assertDontSee('Se déconnecter');

        $this->actingAs($player)->get(route('table.screen', $this->campaign))->assertForbidden();
    }

    public function test_the_game_master_shows_a_map_then_clears_the_screen(): void
    {
        $map = $this->document('Carte de Boston');

        $live = Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('start')
            ->call('showDocument', $map->id)
            ->assertSeeHtml('<span class="font-medium">Carte de Boston</span>');
        $this->assertEquals(['kind' => 'document', 'id' => $map->id], array_diff_key($this->campaign->fresh()->table_display, ['at' => 0]));

        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])
            ->assertSeeHtml('src="'.route('documents.file', $map).'"')
            ->assertSeeHtml('alt="Carte de Boston"');

        $live->call('clearTable')->assertSeeHtml('<span class="font-medium">Écran vide</span>');
        $this->assertNull($this->campaign->fresh()->table_display);

        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])
            ->assertDontSeeHtml(route('documents.file', $map));
    }

    public function test_the_game_master_picks_a_document_in_the_list(): void
    {
        $map = $this->document('Plan de Nalanda');

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->set('tableDocumentId', (string) $map->id)
            ->call('showDocument')
            ->assertHasNoErrors()
            ->assertSet('tableDocumentId', '');

        $this->assertSame($map->id, $this->campaign->fresh()->table_display['id']);
    }

    public function test_a_document_from_another_campaign_cannot_be_shown(): void
    {
        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        $secret = $this->document('Plan du repaire', campaign: $other);

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('showDocument', $secret->id)
            ->assertHasErrors('tableDocumentId');

        $this->assertNull($this->campaign->fresh()->table_display);
    }

    public function test_a_sheet_is_shown_without_its_game_master_zone(): void
    {
        $npc = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create([
            'name' => 'Professeur Armitage',
            'summary' => 'Bibliothécaire de Miskatonic',
            'gm_notes' => 'Membre du culte',
        ]);

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->set('tableEntityId', $npc->id)
            ->call('showEntity')
            ->assertHasNoErrors();

        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])
            ->assertSee('Professeur Armitage')
            ->assertSee('Bibliothécaire de Miskatonic')
            ->assertDontSee('Membre du culte');
    }

    public function test_an_announcement_is_shown_and_a_deleted_item_leaves_the_screen_empty(): void
    {
        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->set('tableText', 'Trois jours plus tard…')
            ->call('showText');

        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])->assertSee('Trois jours plus tard…');

        $map = $this->document('Carte de Boston');
        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])->call('showDocument', $map->id);
        $map->delete();

        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])
            ->assertDontSee('Carte de Boston')
            ->assertSee('Les Enfants de la Peur');
    }
}
