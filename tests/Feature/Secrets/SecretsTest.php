<?php

namespace Tests\Feature\Secrets;

use App\Enums\CampaignRole;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Reveals\Index as RevealIndex;
use App\Livewire\Secrets\Index;
use App\Livewire\Secrets\Panel;
use App\Livewire\Sessions\Live;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\Entity;
use App\Models\PlayerCharacter;
use App\Models\Secret;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecretsTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private Entity $morel;

    private PlayerCharacter $harvey;

    private PlayerCharacter $jack;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create();
        $this->alex = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $this->morel = Entity::factory()->for($this->gm, 'owner')->create(['name' => 'Morel', 'campaign_id' => $this->campaign->id]);

        $sheet = fn (string $name) => Entity::factory()->for($this->gm, 'owner')->create(['name' => $name, 'campaign_id' => $this->campaign->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet('Harvey')->id, 'user_id' => $this->alex->id]);
        $this->jack = $this->campaign->playerCharacters()->create(['entity_id' => $sheet('Jack')->id]);
    }

    private function secret(): Secret
    {
        $scenario = $this->campaign->scenarios()->create(['name' => 'Acte I']);
        $scene = $scenario->scenes()->create(['name' => 'La boutique']);

        Livewire::actingAs($this->gm)
            ->test(Index::class, ['campaign' => $this->campaign, 'link' => 'entity:'.$this->morel->id])
            ->assertSet('entityIds', [$this->morel->id])
            ->set('title', 'Morel travaille pour le Culte d’Ambre')
            ->set('body', 'Il leur vend les reliques volées.')
            ->set('pickedSceneId', (string) $scene->id)
            ->call('save')
            ->assertHasNoErrors();

        return Secret::sole();
    }

    public function test_a_secret_is_linked_to_several_items_and_revealed_character_by_character(): void
    {
        $secret = $this->secret();
        $this->assertSame([$this->morel->id], $secret->entities->modelKeys());
        $this->assertCount(1, $secret->scenes);

        // Sur la fiche, le panneau montre le secret et qui le sait.
        $this->actingAs($this->gm)->get(route('entities.show', [$this->campaign, $this->morel]))->assertSee('Morel travaille pour le Culte d’Ambre');

        Livewire::actingAs($this->gm)
            ->test(Panel::class, ['campaign' => $this->campaign, 'items' => ['entity' => [$this->morel->id]]])
            ->call('revealSecret', $secret->id, $this->harvey->id);

        $grant = CharacterGrant::sole();
        $this->assertSame([$this->harvey->id, $secret->id, 'information'], [$grant->player_character_id, $grant->secret_id, $grant->kind]);

        // Le joueur le voit dans ses connaissances ; Jack ne le sait pas.
        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey])
            ->assertSee('Morel travaille pour le Culte d’Ambre')
            ->assertSee('Il leur vend les reliques volées.');

        // Révéler deux fois ne duplique rien ; « toute la table » complète.
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('revealSecret', $secret->id);
        $this->assertSame(2, CharacterGrant::count());

        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('forgetSecret', $secret->id, $this->jack->id);
        $this->assertSame([$this->harvey->id], $secret->grants()->pluck('player_character_id')->all());
        $this->assertTrue(ActivityLog::where('subject_type', 'grant')->where('event', 'deleted')->exists());

        // Supprimer le secret : Harvey garde ce qu'il a appris.
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])->call('delete', $secret->id);
        $this->assertModelMissing($secret);
        $this->assertSame('Morel travaille pour le Culte d’Ambre', $this->harvey->grants()->sole()->title);
    }

    public function test_reveals_keep_their_session_and_scene_and_can_be_undone(): void
    {
        $secret = $this->secret();
        $scene = $secret->scenes->sole();

        Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('start')
            ->call('setScene', $scene->id);
        $this->actingAs($this->gm)->get(route('sessions.live', $this->campaign))->assertSee('Morel travaille pour le Culte d’Ambre');

        Livewire::actingAs($this->gm)->test(Panel::class, ['campaign' => $this->campaign, 'items' => ['scene' => [$scene->id]]])
            ->call('revealSecret', $secret->id, $this->harvey->id);

        $grant = CharacterGrant::sole();
        $this->assertSame($this->campaign->openSession()->id, $grant->play_session_id);
        $this->assertSame($scene->id, $grant->scene_id);

        Livewire::actingAs($this->gm)->test(RevealIndex::class, ['campaign' => $this->campaign])
            ->assertSee('Morel travaille pour le Culte d’Ambre')
            ->assertSee('Session 1')
            ->assertSee('La boutique')
            ->call('undo', $grant->id);

        $this->assertModelMissing($grant);
    }

    public function test_secrets_are_reserved_to_game_masters_of_the_campaign(): void
    {
        $secret = $this->secret();

        $this->actingAs($this->alex)->get(route('secrets.index', $this->campaign))->assertForbidden();
        $this->actingAs($this->alex)->get(route('reveals.index', $this->campaign))->assertForbidden();
        $this->actingAs($this->alex)->get(route('entities.show', [$this->campaign, $this->morel]))->assertForbidden();

        $other = Campaign::factory()->for($this->gm, 'owner')->create();
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $other])->call('revealSecret', $secret->id)->assertNotFound();
        $this->assertSame(0, CharacterGrant::count());
    }
}
