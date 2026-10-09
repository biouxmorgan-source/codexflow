<?php

namespace Tests\Feature;

use App\Livewire\Search\Everywhere;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\GameSystem;
use App\Models\Rule;
use App\Models\User;
use App\Models\World;
use App\Support\Search\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Backlog après la recette v0.34.0, lot « recherche » : un test par évolution.
 */
class BacklogV7Test extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_other_french_forms_of_a_word(): void
    {
        $gm = User::factory()->create();
        $campaign = Campaign::factory()->for($gm, 'owner')->create();
        Entity::factory()->for($gm, 'owner')->for($campaign)->create(['name' => 'Le phare', 'description' => 'Une lanterne brille au sommet.']);
        Entity::factory()->for($gm, 'owner')->for($campaign)->create(['name' => 'Les quais', 'description' => 'Des lanternes éteintes et des filets.']);

        $names = fn (string $query) => collect((new GlobalSearch($campaign, $gm, $query))->run()['entities'] ?? [])->pluck('title')->sort()->values()->all();

        $this->assertSame(['Le phare', 'Les quais'], $names('lanternes'));
        $this->assertSame(['Le phare', 'Les quais'], $names('lanterne'));
        $this->assertSame(['Les quais'], $names('éteinte'));
        // La sous-chaîne marche toujours : un début de mot suffit.
        $this->assertSame(['Le phare', 'Les quais'], $names('lantern'));
    }

    public function test_home_search_includes_worlds_and_games_without_a_campaign(): void
    {
        $gm = User::factory()->create();
        $other = User::factory()->create();
        $world = World::factory()->for($gm, 'owner')->create(['name' => 'Ostara', 'description' => 'Un archipel de brume.']);
        Entity::factory()->for($gm, 'owner')->for($world)->create(['name' => 'Capitaine Vael', 'description' => 'Contrebandière de brume.']);
        $game = GameSystem::factory()->for($gm, 'owner')->create(['name' => 'Voiles']);
        $rule = new Rule(['title' => 'Tempête de brume']);
        $rule->owner()->associate($gm);
        $rule->gameSystem()->associate($game);
        $rule->save();
        World::factory()->for($other, 'owner')->create(['name' => 'Brume étrangère']);

        Livewire::actingAs($gm)->test(Everywhere::class, ['q' => 'brume'])
            ->assertSee(['Mondes et jeux sans campagne', 'Ostara', 'Capitaine Vael', 'Règle · Voiles'])
            ->assertSeeHtml('href="'.route('worlds.show', $world).'"')
            ->assertDontSee('Brume étrangère');

        // Rattaché à une campagne, le monde est cherché avec elle, plus dans cette liste.
        Campaign::factory()->for($gm, 'owner')->create(['world_id' => $world->id, 'game_system_id' => $game->id]);
        Livewire::actingAs($gm)->test(Everywhere::class, ['q' => 'brume'])->assertDontSee('Mondes et jeux sans campagne');
    }
}
