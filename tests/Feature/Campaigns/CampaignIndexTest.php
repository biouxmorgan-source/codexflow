<?php

namespace Tests\Feature\Campaigns;

use App\Enums\CampaignRole;
use App\Enums\CampaignStatus;
use App\Livewire\Campaigns\Index;
use App\Models\Campaign;
use App\Models\GameSystem;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_state_is_shown(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('campaigns.index'))
            ->assertOk()
            ->assertSee('Aucune campagne pour l');
    }

    public function test_a_user_only_sees_campaigns_they_belong_to(): void
    {
        $user = User::factory()->create();
        Campaign::factory()->for($user, 'owner')->create(['name' => 'Ma campagne']);
        Campaign::factory()->create(['name' => 'Campagne des autres']);

        $this->actingAs($user)
            ->get(route('campaigns.index'))
            ->assertSee('Ma campagne')
            ->assertDontSee('Campagne des autres');
    }

    public function test_creating_a_campaign_with_new_game_and_world_makes_the_author_gm(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('creating', true)
            ->set('name', 'La Couronne de cendres')
            ->set('gameChoice', 'new')
            ->set('newGameName', 'Ryuutama')
            ->set('worldChoice', 'new')
            ->set('newWorldName', 'Valdaria')
            ->call('create')
            ->assertHasNoErrors()
            ->assertSee('La Couronne de cendres');

        $campaign = Campaign::sole();
        $this->assertSame('Ryuutama', $campaign->gameSystem->name);
        $this->assertSame('Valdaria', $campaign->world->name);
        $this->assertSame(CampaignRole::GameMaster, $campaign->roleOf($user));
    }

    /** Parcours de recette 1 : un jeu, un monde, puis deux campagnes qui partagent ce monde. */
    public function test_two_campaigns_can_share_the_same_world(): void
    {
        $user = User::factory()->create();
        $game = GameSystem::factory()->for($user, 'owner')->create();
        $world = World::factory()->for($user, 'owner')->create();
        Campaign::factory()->for($user, 'owner')->for($game)->for($world)->create(['name' => 'Campagne A']);

        foreach (['Campagne B', 'Campagne C'] as $name) {
            Livewire::actingAs($user)
                ->test(Index::class)
                ->set('name', $name)
                ->set('gameChoice', (string) $game->id)
                ->set('worldChoice', (string) $world->id)
                ->call('create')
                ->assertHasNoErrors();
        }

        $this->assertSame(1, World::count());
        $this->assertSame(3, $world->campaigns()->count());
    }

    /** Seuls les jeux et mondes d'une campagne en cours ou archivée sont proposés, pas ceux d'une campagne supprimée. */
    public function test_only_games_and_worlds_in_use_or_archived_are_offered(): void
    {
        $user = User::factory()->create();
        $usedGame = GameSystem::factory()->for($user, 'owner')->create(['name' => 'Jeu en cours']);
        $usedWorld = World::factory()->for($user, 'owner')->create(['name' => 'Monde en cours']);
        Campaign::factory()->for($user, 'owner')->for($usedGame)->for($usedWorld)->create();
        $archivedGame = GameSystem::factory()->for($user, 'owner')->create(['name' => 'Jeu archivé']);
        $archivedWorld = World::factory()->for($user, 'owner')->create(['name' => 'Monde archivé']);
        Campaign::factory()->for($user, 'owner')->for($archivedGame)->for($archivedWorld)->create(['status' => CampaignStatus::Archived, 'archived_at' => now()]);
        $oldGame = GameSystem::factory()->for($user, 'owner')->create(['name' => 'Jeu supprimé']);
        $oldWorld = World::factory()->for($user, 'owner')->create(['name' => 'Monde supprimé']);
        Campaign::factory()->for($user, 'owner')->for($oldGame)->for($oldWorld)->create()->delete();

        $component = Livewire::actingAs($user)->test(Index::class)->set('creating', true);

        $this->assertSame(['Jeu archivé', 'Jeu en cours'], $component->instance()->gameSystems->pluck('name')->all());
        $this->assertSame(['Monde archivé', 'Monde en cours'], $component->instance()->worlds->pluck('name')->all());
        $component->assertDontSee('Jeu supprimé')
            ->assertDontSee('Monde supprimé')
            ->set('name', 'Intrusion')
            ->set('gameChoice', (string) $oldGame->id)
            ->set('worldChoice', (string) $oldWorld->id)
            ->call('create')
            ->assertHasErrors(['gameChoice', 'worldChoice']);
    }

    public function test_a_user_cannot_attach_someone_elses_world(): void
    {
        $user = User::factory()->create();
        $foreignWorld = World::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('name', 'Intrusion')
            ->set('gameChoice', 'new')
            ->set('newGameName', 'Jeu')
            ->set('worldChoice', (string) $foreignWorld->id)
            ->call('create')
            ->assertHasErrors('worldChoice');

        $this->assertSame(0, Campaign::count());
    }

    public function test_name_and_new_game_name_are_required(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Index::class)
            ->set('gameChoice', 'new')
            ->call('create')
            ->assertHasErrors(['name' => 'required', 'newGameName' => 'required_if']);
    }
}
