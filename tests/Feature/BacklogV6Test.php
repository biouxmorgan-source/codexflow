<?php

namespace Tests\Feature;

use App\Actions\Characters\GiveToCharacters;
use App\Enums\CampaignRole;
use App\Enums\Zone;
use App\Livewire\Characters\Show as CharacterShow;
use App\Livewire\Fields\Manage as FieldsManage;
use App\Livewire\Library\GameShow;
use App\Livewire\Library\WorldShow;
use App\Livewire\Members\Index as MembersIndex;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityRelation;
use App\Models\EntityType;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\World;
use App\Support\Notify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Backlog après la recette v0.34.0, lot « joueurs et rôles » : un test par évolution.
 */
class BacklogV6Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $coGm;

    private User $alex;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->coGm = User::factory()->create(['name' => 'Camille']);
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $world = World::factory()->for($this->gm, 'owner')->create(['name' => 'Valdaria']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create(['world_id' => $world->id]);
        $this->campaign->members()->attach($this->coGm, ['role' => CampaignRole::GameMaster->value]);
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    private function sheet(string $name): Entity
    {
        return Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $name]);
    }

    private function attach(Entity $entity, string $name, string $zone): Attachment
    {
        $attachment = new Attachment(['zone' => $zone, 'disk' => Entity::FILES_DISK, 'path' => UploadedFile::fake()->image($name)->store('attachments', Entity::FILES_DISK), 'original_name' => $name, 'mime_type' => 'image/png', 'size' => 10]);
        $attachment->owner()->associate($this->gm);
        $entity->attachments()->save($attachment);

        return $attachment;
    }

    private function relate(Entity $from, Entity $to, string $label, Zone $zone): void
    {
        $relation = new EntityRelation(['label' => $label, 'zone' => $zone]);
        $relation->owner()->associate($this->gm);
        $relation->from()->associate($from);
        $relation->to()->associate($to);
        $relation->save();
    }

    public function test_a_revealed_sheet_shows_its_public_illustrations_and_relations_to_known_sheets(): void
    {
        Storage::fake(Entity::FILES_DISK);
        $morel = $this->sheet('Morel');
        $guild = $this->sheet('La Guilde');
        $cult = $this->sheet('Le Culte');
        $public = $this->attach($morel, 'boutique.png', 'public');
        $secret = $this->attach($morel, 'repaire.png', 'gm');
        $this->relate($morel, $guild, 'membre de', Zone::Public);
        $this->relate($morel, $cult, 'espion de', Zone::Public);
        $this->relate($morel, $guild, 'trahit', Zone::GameMaster);
        $this->actingAs($this->gm);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $morel->id]);
        app(GiveToCharacters::class)->handle($this->campaign, [$this->harvey->id], ['kind' => 'entity', 'entity_id' => $guild->id]);

        $this->actingAs($this->alex)->get(route('characters.entity', [$this->campaign, $this->harvey, $morel]))
            ->assertOk()
            ->assertSee(['Illustrations', 'membre de', 'La Guilde'])
            ->assertSee(route('characters.entity-attachment', [$this->campaign, $this->harvey, $morel, $public]), false)
            // Jamais une fiche inconnue, une relation de la zone MJ, ni un fichier de la zone MJ.
            ->assertDontSee(['Le Culte', 'trahit', 'repaire.png']);

        $this->actingAs($this->alex)->get(route('characters.entity-attachment', [$this->campaign, $this->harvey, $morel, $public]))->assertOk();
        $this->actingAs($this->alex)->get(route('characters.entity-attachment', [$this->campaign, $this->harvey, $morel, $secret]))->assertNotFound();
        $this->actingAs($this->alex)->get(route('characters.entity-attachment', [$this->campaign, $this->harvey, $cult, $public]))->assertNotFound();
    }

    public function test_a_co_game_master_reads_the_game_and_world_pages_and_manages_fields_when_allowed(): void
    {
        $world = $this->campaign->world;
        $game = $this->campaign->gameSystem;

        Livewire::actingAs($this->coGm)->test(WorldShow::class, ['world' => $world])
            ->assertOk()
            ->assertSee(['Valdaria', 'Lecture seule : géré par le propriétaire.'])
            ->set('name', 'Autre')
            ->call('save')
            ->assertForbidden();
        Livewire::actingAs($this->coGm)->test(GameShow::class, ['gameSystem' => $game])->assertOk()->assertDontSee('Gérer les champs →');
        Livewire::actingAs($this->alex)->test(WorldShow::class, ['world' => $world])->assertForbidden();

        // Les champs : refusés, puis confiés par le propriétaire.
        Livewire::actingAs($this->coGm)->test(FieldsManage::class, ['campaign' => $this->campaign])->assertForbidden();
        Livewire::actingAs($this->coGm)->test(MembersIndex::class, ['campaign' => $this->campaign])->call('toggleCoGameMasterFields')->assertForbidden();
        Livewire::actingAs($this->gm)->test(MembersIndex::class, ['campaign' => $this->campaign])->call('toggleCoGameMasterFields');
        $this->assertTrue($this->campaign->fresh()->co_gm_manage_fields);
        Livewire::actingAs($this->coGm)->test(FieldsManage::class, ['campaign' => $this->campaign->fresh()])->assertOk();
        Livewire::actingAs($this->coGm)->test(GameShow::class, ['gameSystem' => $game])->assertSee('Gérer les champs →');
    }

    public function test_a_demoted_co_game_master_loses_the_notifications_received_as_game_master(): void
    {
        $this->actingAs($this->alex);
        Notify::gameMasters($this->campaign, 'message', 'Alex : un secret pour le MJ', route('messages.index', $this->campaign), $this->harvey);
        Notify::players($this->campaign, 'message', 'Message du MJ au groupe', route('messages.index', $this->campaign));
        $this->assertSame(1, $this->coGm->notifications()->count());

        $this->campaign->members()->updateExistingPivot($this->coGm->id, ['role' => CampaignRole::Player->value]);
        Notify::players($this->campaign, 'message', 'Message du MJ au groupe', route('messages.index', $this->campaign));
        $this->campaign->members()->updateExistingPivot($this->coGm->id, ['role' => CampaignRole::GameMaster->value]);

        Livewire::actingAs($this->gm)->test(MembersIndex::class, ['campaign' => $this->campaign])->call('changeRole', $this->coGm->id, CampaignRole::Player->value);

        $this->assertSame(['Message du MJ au groupe'], $this->coGm->notifications()->get()->pluck('data.text')->all());
    }

    public function test_a_co_game_master_downloads_the_archive_and_the_template_but_not_the_complete_backup(): void
    {
        Storage::fake('local');

        $this->actingAs($this->coGm)->get(route('archives.campaign', $this->campaign))->assertOk();
        $this->actingAs($this->coGm)->get(route('archives.template', $this->campaign))->assertOk();
        $this->actingAs($this->coGm)->get(route('archives.campaign', [$this->campaign, 'complete' => 1]))->assertForbidden();
        $this->actingAs($this->alex)->get(route('archives.campaign', $this->campaign))->assertForbidden();
        $this->actingAs($this->coGm)->get(route('campaigns.show', $this->campaign))
            ->assertSee('Télécharger l’archive')
            ->assertDontSee('Télécharger la sauvegarde complète');
    }

    public function test_players_see_a_campaign_feed_with_sessions_public_events_and_group_messages(): void
    {
        $this->campaign->playSessions()->create(['number' => 3, 'started_at' => now()->subDay()]);
        foreach ([['Le manoir brûle', Zone::Public], ['Le traître agit', Zone::GameMaster]] as [$title, $zone]) {
            $event = new TimelineEvent(['kind' => 'played', 'title' => $title, 'zone' => $zone]);
            $event->campaign()->associate($this->campaign);
            $event->user_id = $this->gm->id;
            $event->position = 1;
            $event->save();
        }
        foreach ([null, $this->harvey->id] as $characterId) {
            $message = new Message(['body' => $characterId ? 'Privé pour Harvey' : 'Rendez-vous jeudi']);
            $message->campaign()->associate($this->campaign);
            $message->sender()->associate($this->gm);
            $message->player_character_id = $characterId;
            $message->save();
        }

        Livewire::actingAs($this->alex)->test(CharacterShow::class, ['campaign' => $this->campaign, 'character' => $this->harvey])
            ->assertSee(['Fil de la campagne', 'Session 3', 'Le manoir brûle', 'MJ : Rendez-vous jeudi'])
            ->assertDontSee(['Le traître agit', 'Privé pour Harvey']);
    }
}
