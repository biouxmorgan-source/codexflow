<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Http\Middleware\AvailableOffline;
use App\Livewire\Characters\Give;
use App\Livewire\Characters\Index as CharactersIndex;
use App\Livewire\Members\Index as MembersIndex;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\Search\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Finitions de la recette v0.25.0 : extrait de recherche, Donner à toute la table,
 * reprise d'un personnage par un joueur revenu, noms internes LoreMundi.
 */
class BacklogV4Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['name' => 'Les Ombres']);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    public function test_the_search_excerpt_names_the_custom_field_that_holds_the_word(): void
    {
        $job = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Profession', 'type' => FieldType::Text, 'zone' => Zone::Public, 'position' => 1]);
        $fear = $this->campaign->gameSystem->fieldDefinitions()->create(['name' => 'Phobie', 'type' => FieldType::Text, 'zone' => Zone::GameMaster, 'position' => 2]);
        $morel = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Morel', 'summary' => 'Un vieil homme']);
        $morel->setFieldValues([$job->id => 'Antiquaire à Arkham', $fear->id => 'Les profondeurs marines']);
        $morel->save();

        $gm = (new GlobalSearch($this->campaign, $this->gm, 'antiquaire'))->run(['entities'])['entities']->sole();
        $this->assertSame(['label' => 'Profession', 'text' => 'Antiquaire à Arkham'], $gm->snippet);

        $gmOnly = (new GlobalSearch($this->campaign, $this->gm, 'marines'))->run(['entities'])['entities']->sole();
        $this->assertSame('Phobie', $gmOnly->snippet['label']);

        // Le joueur qui connaît la fiche voit le champ public, jamais le champ MJ.
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $morel->id]);
        $player = (new GlobalSearch($this->campaign, $this->alex, 'antiquaire'))->run()['entities']->sole();
        $this->assertSame('Profession', $player->snippet['label']);
        $this->assertSame([], (new GlobalSearch($this->campaign, $this->alex, 'marines'))->run());
    }

    public function test_free_give_offers_a_whole_table_shortcut(): void
    {
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Lydia', 'entity_type_id' => EntityType::standard('character')->id]);
        $lydia = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id]);

        Livewire::actingAs($this->gm)->test(Give::class, ['campaign' => $this->campaign])
            ->assertSee('Tous les personnages actifs')
            ->assertSeeHtml('x-data="{ ids: JSON.parse(')
            ->set('title', 'Le phare est éteint')
            ->set('selected', [(string) $this->harvey->id, (string) $lydia->id])
            ->call('give')
            ->assertHasNoErrors();

        $this->assertSame(1, $lydia->grants()->count());
        $this->assertSame(1, $this->harvey->grants()->count());
    }

    public function test_a_returning_player_gets_their_old_character_back_in_one_click(): void
    {
        Livewire::actingAs($this->gm)->test(MembersIndex::class, ['campaign' => $this->campaign])->call('remove', $this->alex->id);

        $this->harvey->refresh();
        $this->assertNull($this->harvey->user_id);
        $this->assertSame($this->alex->id, $this->harvey->previous_user_id);

        // Tant qu'Alex n'est pas revenu, rien n'est proposé ; l'ancien joueur est rappelé.
        Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $this->campaign])
            ->assertSee('anciennement Alex')
            ->assertDontSee('Lui rendre Harvey');

        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);

        Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $this->campaign])
            ->assertSee('Alex est de retour dans la campagne.')
            ->call('giveBack', $this->harvey->id)
            ->assertDontSee('Lui rendre Harvey');

        $this->harvey->refresh();
        $this->assertSame($this->alex->id, $this->harvey->user_id);
        $this->assertNull($this->harvey->previous_user_id);
        $this->assertNotNull($this->harvey->assigned_at);

        // Confié à un autre joueur : l'ancien est retenu aussi.
        $bea = User::factory()->create();
        $this->campaign->members()->attach($bea, ['role' => CampaignRole::Player->value]);
        Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $this->campaign])->call('assign', $this->harvey->id, (string) $bea->id);
        $this->assertSame($this->alex->id, $this->harvey->fresh()->previous_user_id);
    }

    public function test_internal_names_follow_the_loremundi_brand(): void
    {
        $this->assertSame('X-LoreMundi-Offline', AvailableOffline::HEADER);
        $this->assertStringContainsString("const PAGES = 'loremundi-pages'", file_get_contents(public_path('sw.js')));
        $this->assertStringContainsString("key.startsWith('codexflow-')", file_get_contents(public_path('sw.js')), 'Les anciens caches sont effacés.');

        $this->campaign->forceFill(['table_shared' => true])->save();
        $this->actingAs($this->alex)->get(route('characters.show', [$this->campaign, $this->harvey]))
            ->assertSee('target="loremundi-table"', false)
            ->assertDontSee('codexflow-table');
    }
}
