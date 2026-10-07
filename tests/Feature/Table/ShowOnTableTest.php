<?php

namespace Tests\Feature\Table;

use App\Enums\CampaignRole;
use App\Livewire\Table\ShowButton;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class ShowOnTableTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Entity $morel;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Entity::FILES_DISK);
        $this->gm = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->morel = Entity::factory()->for($this->gm, 'owner')->create([
            'name' => 'Morel',
            'campaign_id' => $this->campaign->id,
            'summary' => 'Antiquaire',
            'gm_notes' => 'Travaille pour le Culte',
            'image_path' => UploadedFile::fake()->image('morel.jpg')->store('entities', Entity::FILES_DISK),
        ]);
    }

    private function screen(): TestResponse
    {
        return $this->actingAs($this->gm)->get(route('table.screen', $this->campaign));
    }

    public function test_the_gm_shows_a_sheet_its_portrait_an_illustration_or_a_rule_from_their_pages(): void
    {
        $this->actingAs($this->gm)->get(route('entities.show', [$this->campaign, $this->morel]))->assertSee('Afficher à la table')->assertSee('Portrait seul');

        Livewire::actingAs($this->gm)
            ->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'entity', 'itemId' => $this->morel->id])
            ->call('show')
            ->assertSee('À la table')
            ->assertDispatched('table-changed');
        $this->screen()->assertSee('Morel')->assertSee('Antiquaire')->assertDontSee('Travaille pour le Culte');

        // Le portrait seul : l'image, sans le nom.
        Livewire::actingAs($this->gm)->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'portrait', 'itemId' => $this->morel->id])->call('show');
        $this->screen()->assertDontSee('Antiquaire')->assertSee(route('table.image', $this->campaign), false);
        $this->actingAs($this->gm)->get(route('table.image', $this->campaign))->assertOk();

        $illustration = new Attachment(['zone' => 'gm', 'disk' => Entity::FILES_DISK, 'path' => UploadedFile::fake()->image('boutique.png')->store('attachments', Entity::FILES_DISK), 'original_name' => 'boutique.png', 'mime_type' => 'image/png', 'size' => 10]);
        $illustration->owner()->associate($this->gm);
        $this->morel->attachments()->save($illustration);

        $this->actingAs($this->gm)->get(route('entities.show', [$this->campaign, $this->morel]))->assertSeeLivewire(ShowButton::class);
        Livewire::actingAs($this->gm)->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'attachment', 'itemId' => $illustration->id])->call('show');
        $this->actingAs($this->gm)->get(route('table.file', $this->campaign))->assertOk()->assertHeader('Content-Type', 'image/png');

        $rule = new Rule(['title' => 'Poursuite', 'summary' => 'Trois jets d’opposition.', 'procedure' => 'Le plus rapide choisit le terrain.', 'gm_notes' => 'Truquer si besoin']);
        $rule->owner()->associate($this->gm);
        $rule->campaign()->associate($this->campaign);
        $rule->save();

        $this->actingAs($this->gm)->get(route('rules.show', [$this->campaign, $rule]))->assertSee('Afficher à la table');
        Livewire::actingAs($this->gm)->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'rule', 'itemId' => $rule->id])->call('show');
        $this->screen()->assertSee('Poursuite')->assertSee('Le plus rapide choisit le terrain.')->assertDontSee('Truquer si besoin');
    }

    public function test_only_the_gm_shows_and_only_what_belongs_to_the_campaign(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        Livewire::actingAs($player)
            ->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'entity', 'itemId' => $this->morel->id])
            ->assertForbidden();

        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        $stranger = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Inconnu', 'campaign_id' => $other->id]);

        Livewire::actingAs($this->gm)
            ->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'entity', 'itemId' => $stranger->id])
            ->call('show')
            ->assertNotFound();

        Livewire::actingAs($this->gm)
            ->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'scene', 'itemId' => 1])
            ->assertHasErrors();

        $this->assertNull($this->campaign->fresh()->table_display);
    }
}
