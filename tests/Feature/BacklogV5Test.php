<?php

namespace Tests\Feature;

use App\Enums\CampaignRole;
use App\Livewire\Characters\Index as CharactersIndex;
use App\Livewire\Entities\Show as EntityShow;
use App\Livewire\Members\Index as MembersIndex;
use App\Livewire\Sessions\Live;
use App\Livewire\Table\Remote;
use App\Livewire\Table\Screen;
use App\Livewire\Table\ShowButton;
use App\Livewire\Tags\Manage as TagsManage;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\Tag;
use App\Models\User;
use App\Models\World;
use App\Support\TableDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Backlog après la recette v0.34.0, lot « confort du MJ et de la séance » : un test par évolution.
 */
class BacklogV5Test extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private User $alex;

    private Campaign $campaign;

    private PlayerCharacter $harvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->alex = User::factory()->create(['name' => 'Alex']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $sheet = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Harvey', 'entity_type_id' => EntityType::standard('character')->id]);
        $this->harvey = $this->campaign->playerCharacters()->create(['entity_id' => $sheet->id, 'user_id' => $this->alex->id]);
    }

    private function pdf(string $title = 'Lettre'): Document
    {
        $document = new Document(['title' => $title, 'disk' => 'local', 'path' => 'documents/lettre.pdf', 'original_name' => 'lettre.pdf', 'mime_type' => 'application/pdf', 'size' => 1]);
        $document->owner()->associate($this->gm);
        $document->campaign()->associate($this->campaign);
        $document->save();

        return $document;
    }

    private function tag(User $owner, string $name): Tag
    {
        $tag = new Tag(['name' => $name, 'color' => 'red']);
        $tag->user_id = $owner->id;
        $tag->save();

        return $tag;
    }

    private function message(string $body, User $sender, ?PlayerCharacter $character = null): Message
    {
        $message = new Message(['body' => $body]);
        $message->campaign()->associate($this->campaign);
        $message->sender()->associate($sender);
        $message->player_character_id = $this->harvey->id;
        $message->sender_character_id = $character?->id;
        $message->save();

        return $message;
    }

    public function test_the_campaign_status_shows_under_the_sheet_title(): void
    {
        $vigil = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Vigie']);

        Livewire::actingAs($this->gm)->test(EntityShow::class, ['campaign' => $this->campaign, 'entity' => $vigil])
            ->assertDontSee('Statut dans cette campagne')
            ->set('status', 'disparu')
            ->call('saveState')
            ->assertSeeHtml('title="Statut dans cette campagne">disparu</span>');
    }

    public function test_the_remote_and_the_document_page_turn_the_pages_of_the_pdf_at_the_table(): void
    {
        $document = $this->pdf();
        TableDisplay::show($this->campaign, 'document', $document->id);

        $remote = Livewire::actingAs($this->gm)->test(Remote::class, ['campaign' => $this->campaign])
            ->assertSee('Page 1')
            ->call('turn', 1)
            ->assertSee('Page 2');
        $this->assertSame(2, TableDisplay::page($this->campaign->fresh()));

        // L'écran du MJ a ouvert le PDF : 3 pages, la navigation s'arrête à la dernière.
        Livewire::actingAs($this->gm)->test(Screen::class, ['campaign' => $this->campaign])
            ->call('knowPages', 3)
            ->assertSee('table-pdf-page', false);
        $remote->call('turn', 1)->call('turn', 1)->assertSee('Page 3 / 3');
        $this->assertSame(3, TableDisplay::page($this->campaign->fresh()));

        // Depuis la page du document, retour en arrière ; le même affichage continue (pas de nouvelle clé).
        $key = TableDisplay::current($this->campaign->fresh())['key'];
        Livewire::actingAs($this->gm)->test(ShowButton::class, ['campaign' => $this->campaign, 'kind' => 'document', 'itemId' => $document->id])
            ->assertSee('Page 3 / 3')
            ->call('turn', -1);
        $this->assertSame(2, TableDisplay::page($this->campaign->fresh()));
        $this->assertSame($key, TableDisplay::current($this->campaign->fresh())['key']);

        // Le joueur qui suit l'écran voit la page du MJ, et ne la tourne pas pour les autres.
        $this->campaign->forceFill(['table_shared' => true])->save();
        Livewire::actingAs($this->alex)->test(Screen::class, ['campaign' => $this->campaign])
            ->assertSeeHtml("'screen', 2, false)")
            ->call('turnTo', 1)
            ->assertForbidden();
    }

    public function test_a_character_goes_back_to_an_older_player_who_returns_with_their_own_messages_only(): void
    {
        $bea = User::factory()->create(['name' => 'Béa']);
        $this->campaign->members()->attach($bea, ['role' => CampaignRole::Player->value]);
        $this->message('Alex écrit au MJ', $this->alex, $this->harvey);

        // Alex quitte la campagne ; Harvey passe à Béa, puis Béa le laisse.
        $this->travel(1)->minutes();
        Livewire::actingAs($this->gm)->test(MembersIndex::class, ['campaign' => $this->campaign])->call('remove', $this->alex->id);
        $this->travel(1)->minutes();
        Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $this->campaign])->call('assign', $this->harvey->id, (string) $bea->id);
        $this->message('Béa écrit au MJ', $bea, $this->harvey);
        $this->travel(1)->minutes();
        Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $this->campaign])->call('assign', $this->harvey->id, '');
        $this->assertSame($bea->id, $this->harvey->fresh()->previous_user_id);

        // Béa est toujours là, sans être « de retour » ; Alex revient : c'est à lui qu'on propose Harvey.
        $this->travel(1)->minutes();
        $this->campaign->members()->attach($this->alex, ['role' => CampaignRole::Player->value]);
        $this->campaign->playerCharacters()->create([
            'entity_id' => Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Ida'])->id,
            'user_id' => $bea->id,
        ]);
        Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $this->campaign])
            ->assertSee('Alex est de retour dans la campagne.')
            ->call('giveBack', $this->harvey->id);
        $this->assertSame($this->alex->id, $this->harvey->fresh()->user_id);

        // Alex retrouve sa conversation d'avant son départ, pas celle de Béa.
        $this->travel(1)->minutes();
        $this->message('Le MJ répond à Alex', $this->gm);
        $visible = Message::query()->visibleTo($this->alex, $this->campaign)->pluck('body')->all();
        $this->assertContains('Alex écrit au MJ', $visible);
        $this->assertContains('Le MJ répond à Alex', $visible);
        $this->assertNotContains('Béa écrit au MJ', $visible);
        $this->assertSame([], Message::query()->visibleTo($bea, $this->campaign)->whereNotNull('player_character_id')->pluck('body')->all());
    }

    public function test_session_mode_records_a_played_event_for_the_current_scene(): void
    {
        $scenario = $this->campaign->scenarios()->create(['name' => 'Le manoir']);
        $hall = $scenario->scenes()->create(['name' => 'Le hall', 'position' => 0]);
        $live = Livewire::actingAs($this->gm)->test(Live::class, ['campaign' => $this->campaign])
            ->call('start')
            ->call('setScene', $hall->id)
            ->set('noteBody', 'Les joueurs brûlent la lettre de [[Harvey|'.$this->harvey->entity_id.']]')
            ->call('addPlayedEvent')
            ->assertHasNoErrors()
            ->assertSee('Ajouté à la chronologie : Les joueurs brûlent la lettre de Harvey');

        $event = $this->campaign->timelineEvents()->sole();
        $this->assertSame('played', $event->kind);
        $this->assertSame('Les joueurs brûlent la lettre de Harvey', $event->title);
        $this->assertStringContainsString('[[Harvey|', $event->description);
        $this->assertSame($this->campaign->openSession()->id, $event->play_session_id);
        $this->assertSame($hall->id, $event->scene_id);
        $this->assertSame('', $live->get('noteBody'));
    }

    public function test_pregens_are_offered_first_and_apart_from_other_characters(): void
    {
        $world = World::factory()->for($this->gm, 'owner')->create();
        $this->campaign->world()->associate($world)->save();
        $character = EntityType::standard('character')->id;
        $pregen = Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Teska', 'entity_type_id' => $character]);
        $tag = $this->tag($this->gm, 'Prétiré');
        $pregen->tags()->attach($tag);
        Entity::factory()->for($this->gm, 'owner')->for($world)->create(['name' => 'Le traître', 'entity_type_id' => $character]);

        $groups = Livewire::actingAs($this->gm)->test(CharactersIndex::class, ['campaign' => $this->campaign->fresh()])
            ->assertSeeInOrder(['Prétirés', 'Teska', 'Autres personnages du monde', 'Le traître'])
            ->instance()->candidateGroups;
        $this->assertSame(['Prétirés', 'Autres personnages du monde (copiés dans la campagne)'], $groups->keys()->all());
    }

    public function test_tag_counters_open_the_list_of_tagged_items(): void
    {
        $tag = $this->tag($this->gm, 'intrigue');
        $vigil = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => 'Vigie']);
        $vigil->tags()->attach($tag);
        $this->pdf('Carte au trésor')->tags()->attach($tag);

        Livewire::actingAs($this->gm)->test(TagsManage::class)
            ->assertDontSee('Carte au trésor')
            ->call('toggleItems', $tag->id)
            ->assertSee(['Vigie', 'Carte au trésor', $this->campaign->name])
            ->assertSeeHtml('href="'.route('entities.show', [$this->campaign, $vigil]).'"');

        // Le tag d'un autre MJ ne se déplie pas.
        $other = $this->tag($this->alex, 'secret');
        Livewire::actingAs($this->gm)->test(TagsManage::class)->call('toggleItems', $other->id)->assertNotFound();
    }

    public function test_the_spectator_role_says_it_always_sees_the_table_screen(): void
    {
        $this->assertStringContainsString('même quand il n\'est pas partagé', CampaignRole::Spectator->description());
    }
}
