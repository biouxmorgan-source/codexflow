<?php

namespace Tests\Feature\Members;

use App\Enums\CampaignRole;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Entities\Form as EntityForm;
use App\Livewire\Members\Index;
use App\Models\Campaign;
use App\Models\CampaignInvitation;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Tag;
use App\Models\User;
use App\Models\World;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Campaign $campaign;

    private Entity $morel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Morgane']);
        $world = World::factory()->for($this->owner, 'owner')->create();
        $this->campaign = Campaign::factory()->for($this->owner, 'owner')->create(['world_id' => $world->id]);
        $this->morel = Entity::factory()->for($this->owner, 'owner')->for($world)->create(['name' => 'Morel', 'gm_notes' => 'Travaille pour le Culte d’Ambre']);
    }

    private function join(CampaignRole $role, string $name): User
    {
        Livewire::actingAs($this->owner)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->set('label', $name)
            ->set('role', $role->value)
            ->call('invite')
            ->assertHasNoErrors();

        $invitation = CampaignInvitation::latest('id')->firstOrFail();
        $this->assertSame($role, $invitation->role);

        $user = User::factory()->create(['name' => $name]);
        $this->actingAs($user)->post(route('invitations.accept', $invitation->token));
        $this->assertSame($role, $this->campaign->roleOf($user));

        return $user;
    }

    public function test_a_co_gm_prepares_with_the_owner_but_does_not_administer(): void
    {
        $type = new EntityType(['name' => 'Culte']);
        $type->user_id = $this->owner->id;
        $type->save();
        $tag = new Tag(['name' => 'intrigue']);
        $tag->owner()->associate($this->owner);
        $tag->save();
        $this->morel->tags()->attach($tag);

        $coGm = $this->join(CampaignRole::GameMaster, 'Sam');

        $this->actingAs($coGm)->get(route('campaigns.index'))->assertSee('Co-MJ');
        $this->actingAs($coGm)->get(route('campaigns.show', $this->campaign))->assertOk()->assertSee('Morel')->assertDontSee('Supprimer la campagne');
        $this->actingAs($coGm)->get(route('entities.show', [$this->campaign, $this->morel]))->assertOk()->assertSee('Culte d’Ambre');
        $this->actingAs($coGm)->get(route('sessions.live', $this->campaign))->assertOk();

        // Il voit les types et les tags du propriétaire, et ce qu'il crée reste dans la campagne du propriétaire.
        Livewire::actingAs($coGm)
            ->test(EntityForm::class, ['campaign' => $this->campaign])
            ->assertSee('Culte')
            ->assertSee('intrigue')
            ->set('name', 'Le Culte d’Ambre')
            ->set('entityTypeId', (string) $type->id)
            ->set('tags', 'intrigue')
            ->call('save')
            ->assertHasNoErrors();

        $cult = Entity::where('name', 'Le Culte d’Ambre')->sole();
        $this->assertSame($this->owner->id, $cult->user_id);
        $this->assertSame([$tag->id], $cult->tags()->pluck('tags.id')->all());

        Livewire::actingAs($coGm)
            ->test(EntityForm::class, ['campaign' => $this->campaign, 'entity' => $this->morel])
            ->set('summary', 'Un antiquaire trop aimable.')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame('Un antiquaire trop aimable.', $this->morel->fresh()->summary);

        // Membres : il les voit, mais n'invite pas, ne change pas les rôles et ne retire personne.
        Livewire::actingAs($coGm)->test(Index::class, ['campaign' => $this->campaign])
            ->assertOk()
            ->assertSee('Morgane')
            ->assertDontSee("Créer le lien d'invitation")
            ->call('invite')
            ->assertForbidden();
        Livewire::actingAs($coGm)->test(Index::class, ['campaign' => $this->campaign])->call('changeRole', $coGm->id, 'player')->assertForbidden();
        Livewire::actingAs($coGm)->test(Index::class, ['campaign' => $this->campaign])->call('remove', $this->owner->id)->assertForbidden();
        Livewire::actingAs($coGm)->test(CampaignShow::class, ['campaign' => $this->campaign])->call('delete')->assertForbidden();
        $this->assertModelExists($this->campaign);

        // Retiré de la campagne, il n'a plus accès à rien.
        Livewire::actingAs($this->owner)->test(Index::class, ['campaign' => $this->campaign])->call('remove', $coGm->id);
        $this->actingAs($coGm)->get(route('entities.show', [$this->campaign, $this->morel]))->assertForbidden();
    }

    public function test_a_spectator_only_watches_the_table_screen(): void
    {
        $this->assertFalse((bool) $this->campaign->fresh()->table_shared);
        $spectator = $this->join(CampaignRole::Spectator, 'Lou');

        $this->actingAs($spectator)->get(route('campaigns.index'))->assertSee('Spectateur')->assertSee(route('table.screen', $this->campaign), false);
        $this->actingAs($spectator)->get(route('table.screen', $this->campaign))->assertOk();

        // La page de la campagne le renvoie vers l'écran de table ; le reste lui est fermé.
        $this->actingAs($spectator)->get(route('campaigns.show', $this->campaign))->assertRedirect(route('table.screen', $this->campaign));

        foreach (['messages.index', 'search.index', 'characters.index', 'members.index', 'journal.index'] as $route) {
            $this->actingAs($spectator)->get(route($route, $this->campaign))->assertForbidden();
        }
        $this->actingAs($spectator)->get(route('entities.show', [$this->campaign, $this->morel]))->assertForbidden();
    }

    public function test_the_owner_changes_roles_but_stays_game_master(): void
    {
        $player = $this->join(CampaignRole::Player, 'Alex');

        Livewire::actingAs($this->owner)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->call('changeRole', $player->id, CampaignRole::GameMaster->value)
            ->assertHasNoErrors();
        $this->assertSame(CampaignRole::GameMaster, $this->campaign->roleOf($player));
        $this->actingAs($player)->get(route('campaigns.show', $this->campaign))->assertOk();

        Livewire::actingAs($this->owner)
            ->test(Index::class, ['campaign' => $this->campaign])
            ->call('changeRole', $this->owner->id, CampaignRole::Player->value)
            ->assertForbidden();
        $this->assertSame(CampaignRole::GameMaster, $this->campaign->roleOf($this->owner));
    }
}
