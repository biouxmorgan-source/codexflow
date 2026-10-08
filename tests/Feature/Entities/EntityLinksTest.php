<?php

namespace Tests\Feature\Entities;

use App\Livewire\Entities\Form;
use App\Livewire\Entities\Show;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\User;
use App\Models\World;
use App\Support\EntityLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EntityLinksTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private World $world;

    private Campaign $campaign;

    private Entity $shen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $this->world->id]);
        $this->shen = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Shen Chu']);
    }

    public function test_a_link_by_id_points_to_the_entity_and_follows_renames(): void
    {
        $html = (string) EntityLinks::render('Elle a croisé [[Shen Chu|'.$this->shen->id.']] hier.', $this->campaign);

        $this->assertStringContainsString('href="'.route('entities.show', [$this->campaign, $this->shen]).'"', $html);
        $this->assertStringContainsString('>Shen Chu</a>', $html);

        $this->shen->update(['name' => 'Shen Chu la Sage']);
        $html = (string) EntityLinks::render('[[Shen Chu|'.$this->shen->id.']]', $this->campaign);

        $this->assertStringContainsString('>Shen Chu la Sage</a>', $html);
    }

    public function test_a_link_typed_by_name_is_resolved_case_insensitively(): void
    {
        $html = (string) EntityLinks::render('Voir [[shen chu]].', $this->campaign);

        $this->assertStringContainsString('href="'.route('entities.show', [$this->campaign, $this->shen]).'"', $html);
    }

    public function test_unknown_or_foreign_entities_are_not_linked(): void
    {
        $elsewhere = Campaign::factory()->for($this->gm, 'owner')->create();
        $foreign = Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $elsewhere->id, 'world_id' => null, 'name' => 'Ailleurs']);

        $html = (string) EntityLinks::render('[[Personne]] et [[Ailleurs|'.$foreign->id.']]', $this->campaign);

        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringContainsString('Personne', $html);
        $this->assertStringContainsString('Ailleurs', $html);
    }

    public function test_text_is_escaped_and_line_breaks_kept(): void
    {
        $html = (string) EntityLinks::render("<script>alert(1)</script>\n[[<b>x</b>]]", $this->campaign);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);

        // Un simple saut de ligne reste un saut de ligne, comme avant la mise en forme.
        $this->assertStringContainsString('Première ligne<br>', (string) EntityLinks::render("Première ligne\nSeconde ligne", $this->campaign));
    }

    public function test_formatting_is_rendered_without_html_or_remote_images(): void
    {
        $html = (string) EntityLinks::render("## Indices\n\n- **Gras** et *italique*\n- [[Personne]]\n\n> Citation\n\n![pisteur](https://example.com/x.png) [site](javascript:alert(1))", $this->campaign);

        $this->assertStringContainsString('<h2>Indices</h2>', $html);
        $this->assertStringContainsString('<li><strong>Gras</strong> et <em>italique</em></li>', $html);
        $this->assertStringContainsString('<blockquote>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertSame('Indices Gras et italique Personne Citation site', EntityLinks::excerpt("## Indices\n\n- **Gras** et *italique*\n- [[Personne]]\n\n> Citation\n\n[site](https://example.com)"));
    }

    public function test_suggestions_list_entities_of_the_campaign_only(): void
    {
        $elsewhere = Campaign::factory()->for($this->gm, 'owner')->create();
        Entity::factory()->for($this->gm, 'owner')->create(['campaign_id' => $elsewhere->id, 'world_id' => null, 'name' => 'Shen Lo']);
        Entity::factory()->for($this->gm, 'owner')->for($this->world)->create(['name' => 'Mira']);

        $component = Livewire::actingAs($this->gm)->test(Form::class, ['campaign' => $this->campaign]);

        $suggestions = $component->instance()->suggestEntities('shen');

        $this->assertSame(['Shen Chu'], array_column($suggestions, 'name'));
        $this->assertSame($this->shen->id, $suggestions[0]['id']);
        $this->assertSame('Personnage', $suggestions[0]['type']);
    }

    public function test_entity_page_renders_links_and_backlinks(): void
    {
        $inn = Entity::factory()->for($this->gm, 'owner')->for($this->world)->create([
            'name' => 'Le Poney fringant',
            'description' => 'Tenue par [[Shen Chu|'.$this->shen->id.']].',
        ]);
        Entity::factory()->for($this->gm, 'owner')->for($this->world)->create([
            'name' => 'Rumeur',
            'gm_notes' => 'Concerne [[Shen Chu]].',
        ]);

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $inn])
            ->assertSeeHtml('href="'.route('entities.show', [$this->campaign, $this->shen]).'"');

        Livewire::actingAs($this->gm)
            ->test(Show::class, ['campaign' => $this->campaign, 'entity' => $this->shen])
            ->assertSee(['Cité dans', 'Le Poney fringant', 'Rumeur']);
    }
}
