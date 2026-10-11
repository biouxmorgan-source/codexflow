<?php

namespace Tests\Feature\Characters;

use App\Enums\CampaignRole;
use App\Enums\FieldType;
use App\Enums\Zone;
use App\Livewire\Characters\Index;
use App\Livewire\Characters\Show;
use App\Livewire\Fields\Manage;
use App\Livewire\Members\Index as MembersIndex;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FieldDefinition;
use App\Models\PlayerCharacter;
use App\Models\User;
use App\Support\Archive\CampaignExport;
use App\Support\Archive\CampaignImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Le MJ laisse les joueurs modifier tout leur personnage : champs par groupe, portrait, résumé, description. */
class PlayerEditsCharacterTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $player;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(PlayerCharacter::DISK);
        Storage::fake(Entity::FILES_DISK);

        $this->gm = User::factory()->create();
        $this->player = User::factory()->create();
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->player, ['role' => CampaignRole::Player->value]);
    }

    private function field(string $name, string $group, Zone $zone = Zone::Public): FieldDefinition
    {
        return $this->campaign->gameSystem->fieldDefinitions()->create([
            'name' => $name, 'group' => $group, 'type' => FieldType::Number, 'zone' => $zone,
            'entity_type_id' => EntityType::standard('character')->id,
        ]);
    }

    private function character(): PlayerCharacter
    {
        Livewire::actingAs($this->gm)->test(Index::class, ['campaign' => $this->campaign])
            ->set('name', 'Agathe')->set('playerId', (string) $this->player->id)->call('create')->assertHasNoErrors();

        return PlayerCharacter::latest('id')->firstOrFail();
    }

    public function test_the_game_master_makes_a_whole_group_or_every_public_field_editable_in_one_click(): void
    {
        $strength = $this->field('FOR', 'Caractéristiques');
        $power = $this->field('POU', 'Caractéristiques');
        $listen = $this->field('Écouter', 'Compétences');
        $madness = $this->field('Folie latente', 'Caractéristiques', Zone::GameMaster);

        $page = Livewire::actingAs($this->gm)->test(Manage::class, ['campaign' => $this->campaign])
            ->assertSee('Tous modifiables par le joueur')
            ->call('setPlayerEditable', 'Caractéristiques', true)
            ->assertSee('Plus modifiables par le joueur');

        $this->assertTrue($strength->fresh()->player_editable);
        $this->assertTrue($power->fresh()->player_editable);
        $this->assertFalse($listen->fresh()->player_editable, 'Un autre groupe ne bouge pas.');
        $this->assertFalse($madness->fresh()->player_editable, 'La zone MJ reste hors de portée des joueurs.');

        $page->call('setPlayerEditable', null, true);
        $this->assertTrue($listen->fresh()->player_editable);
        $this->assertFalse($madness->fresh()->player_editable);

        $page->call('setPlayerEditable', null, false);
        $this->assertSame(0, FieldDefinition::where('player_editable', true)->count());

        // Un joueur n'y a pas accès.
        $this->actingAs($this->player)->get(route('fields.index', $this->campaign))->assertForbidden();
    }

    public function test_players_edit_portrait_summary_and_description_only_when_the_game_master_allows_it(): void
    {
        $character = $this->character();

        // Sans l'option, pas de bouton « Modifier » ni d'édition possible.
        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign, 'character' => $character])
            ->assertDontSee('Modifier ma fiche')
            ->call('edit')->assertForbidden();

        Livewire::actingAs($this->gm)->test(MembersIndex::class, ['campaign' => $this->campaign])
            ->assertSee('Les joueurs modifient le portrait, le résumé et la description de leur personnage')
            ->call('togglePlayersEditProfile');
        $this->assertTrue($this->campaign->fresh()->players_edit_profile);

        $character->entity->update(['gm_notes' => 'Un vrai pouvoir']);

        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign->fresh(), 'character' => $character])
            ->call('edit')
            ->assertSee('Modifier ma fiche')
            ->set('profileSummary', 'Voyante parisienne')
            ->set('profileDescription', 'Cheveux **noir corbeau**.')
            ->set('portrait', UploadedFile::fake()->image('agathe.jpg', 200, 200))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Voyante parisienne');

        $entity = $character->entity->fresh();
        $this->assertSame('Voyante parisienne', $entity->summary);
        $this->assertSame('Cheveux **noir corbeau**.', $entity->description);
        $this->assertSame('Un vrai pouvoir', $entity->gm_notes, 'Les notes MJ ne bougent pas.');
        $this->assertTrue($entity->hasImage());
        Storage::disk(Entity::FILES_DISK)->assertExists($entity->image_path);

        // Retirer le portrait.
        $old = $entity->image_path;
        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign->fresh(), 'character' => $character])
            ->call('edit')->set('removePortrait', true)->call('save')->assertHasNoErrors();
        $this->assertFalse($character->entity->fresh()->hasImage());
        Storage::disk(Entity::FILES_DISK)->assertMissing($old);

        // Une fiche verrouillée ne se modifie plus.
        $character->update(['locked' => true]);
        Livewire::actingAs($this->player)->test(Show::class, ['campaign' => $this->campaign->fresh(), 'character' => $character->fresh()])
            ->call('edit')->assertForbidden();
    }

    public function test_the_option_travels_with_the_campaign_archive(): void
    {
        $this->campaign->forceFill(['players_edit_profile' => true])->save();

        $path = (new CampaignExport($this->campaign->fresh()))->write();
        $copy = (new CampaignImport($this->gm))->handle($path);

        $this->assertTrue($copy->players_edit_profile);
    }
}
