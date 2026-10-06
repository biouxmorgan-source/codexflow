<?php

namespace Tests\Feature\Search;

use App\Enums\CampaignRole;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\User;
use App\Models\World;
use App\Support\Search\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    private Entity $aldric;

    private Scene $arrival;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);

        $this->aldric = Entity::factory()->for($this->gm, 'owner')->for($world)->create([
            'name' => 'Aldric le tavernier',
            'summary' => 'Tient le Poney depuis vingt ans.',
            'gm_notes' => 'Indicateur secret de la Guilde.',
        ]);
        $this->aldric->tags()->sync(Tag::idsFromInput($this->gm, 'taverne, intrigue'));

        $scenario = $this->campaign->scenarios()->create(['name' => 'Une nuit']);
        $this->arrival = $scenario->scenes()->create(['name' => 'Arrivée à la grange', 'position' => 1, 'description' => 'Un orage gronde.']);
        $this->arrival->entities()->attach($this->aldric->id, ['position' => 0]);
    }

    /** Parcours de recette 10 : retrouver un PNJ par son nom, ses tags et les scènes auxquelles il est lié. */
    public function test_finds_an_npc_from_its_name_its_tags_and_its_scenes(): void
    {
        foreach (['tavernier', 'intrigue', 'grange'] as $query) {
            $titles = $this->titles($query, 'entities');
            $this->assertSame(['Aldric le tavernier'], $titles, "Recherche « {$query} »");
        }

        // Le nom de la scène retrouve aussi la scène elle-même.
        $this->assertSame(['Arrivée à la grange'], $this->titles('grange', 'scenes'));

        $this->actingAs($this->gm)
            ->get(route('search.index', [$this->campaign, 'q' => 'grange']))
            ->assertOk()
            ->assertSeeInOrder(['Fiches', 'Aldric le tavernier', 'Scènes :', 'Arrivée à la <mark', 'Scènes', 'Arrivée à la <mark'], false);
    }

    public function test_search_ignores_accents_case_and_needs_every_word(): void
    {
        $this->assertSame(['Arrivée à la grange'], $this->titles('ARRIVEE', 'scenes'));
        $this->assertSame(['Aldric le tavernier'], $this->titles('aldric poney'));
        $this->assertSame([], $this->titles('aldric dragon'));
    }

    public function test_gm_finds_words_from_the_gm_zone_and_sees_where_they_come_from(): void
    {
        $results = (new GlobalSearch($this->campaign, $this->gm, 'indicateur'))->run(['entities']);

        $this->assertSame('Zone MJ', $results['entities'][0]->snippet['label']);
        $this->assertStringContainsString('Indicateur secret', $results['entities'][0]->snippet['text']);
    }

    /** Parcours de recette 12 (côté serveur) : un joueur ne trouve rien par un mot de la zone MJ. */
    public function test_a_player_never_finds_gm_only_content(): void
    {
        $player = User::factory()->create();
        $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);

        $this->assertSame([], (new GlobalSearch($this->campaign, $player, 'indicateur'))->run());

        $this->actingAs($player)
            ->get(route('search.index', [$this->campaign, 'q' => 'indicateur']))
            ->assertOk()
            ->assertDontSee('Aldric')
            ->assertSee('Aucun résultat');

        $this->actingAs(User::factory()->create())
            ->get(route('search.index', [$this->campaign, 'q' => 'aldric']))
            ->assertForbidden();
    }

    public function test_content_from_other_campaigns_and_worlds_stays_out(): void
    {
        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        Entity::factory()->for($this->gm, 'owner')->for($other)->create(['name' => 'Aldric le faux']);
        $otherScenario = $other->scenarios()->create(['name' => 'Ailleurs']);
        $otherScenario->scenes()->create(['name' => 'Arrivée ailleurs']);

        $this->assertSame(['Aldric le tavernier'], $this->titles('aldric'));
        $this->assertSame(['Arrivée à la grange'], $this->titles('arrivee', 'scenes'));
    }

    public function test_finds_rules_documents_and_session_notes(): void
    {
        $rule = new Rule(['title' => 'Bagarre de taverne', 'procedure' => 'Chaque coup retire un point de condition.']);
        $rule->owner()->associate($this->gm);
        $rule->campaign()->associate($this->campaign);
        $rule->save();

        $session = $this->campaign->playSessions()->create(['number' => 1, 'started_at' => now(), 'ended_at' => now()]);
        $note = $session->notes()->make(['body' => 'Les joueurs promettent une condition à Mira.']);
        $note->author()->associate($this->gm);
        $note->save();

        $results = (new GlobalSearch($this->campaign, $this->gm, 'condition'))->run();

        $this->assertSame(['rules', 'notes'], array_keys($results));
        $this->assertSame('Bagarre de taverne', $results['rules'][0]->title);
        $this->assertSame('Session 1', $results['notes'][0]->title);
        $this->assertSame(route('sessions.show', [$this->campaign, $session]), $results['notes'][0]->url);
    }

    public function test_filters_by_entity_type(): void
    {
        $place = EntityType::query()->availableTo($this->gm)->where('id', '!=', $this->aldric->entity_type_id)->firstOrFail();

        $this->assertSame([], $this->titles('aldric', null, $place->id));
        $this->assertSame(['Aldric le tavernier'], $this->titles('aldric', null, $this->aldric->entity_type_id));
    }

    public function test_highlight_escapes_and_ignores_accents(): void
    {
        $html = (string) GlobalSearch::highlight('<b>Arrivée</b> au Poney', ['arrivee', 'poney']);

        $this->assertStringContainsString('&lt;b&gt;', $html);
        $this->assertStringContainsString('>Arrivée</mark>', $html);
        $this->assertStringContainsString('>Poney</mark>', $html);
    }

    /** @return list<string> */
    private function titles(string $query, ?string $kind = null, ?int $entityTypeId = null): array
    {
        $results = (new GlobalSearch($this->campaign, $this->gm, $query))->run($kind ? [$kind] : null, $entityTypeId);

        return collect($results)->flatten(1)->pluck('title')->all();
    }
}
