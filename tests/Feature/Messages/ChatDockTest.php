<?php

namespace Tests\Feature\Messages;

use App\Enums\CampaignRole;
use App\Livewire\ChatDock;
use App\Models\Campaign;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Message;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatDockTest extends TestCase
{
    use RefreshDatabase;

    private User $gm;

    private Campaign $campaign;

    /** @var array<string, array{0: User, 1: PlayerCharacter}> */
    private array $table = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::factory()->create(['name' => 'Morgane']);
        $this->campaign = Campaign::factory()->for($this->gm, 'owner')->create();

        foreach (['Harvey' => 'Alex', 'Jack' => 'Sam'] as $characterName => $playerName) {
            $player = User::factory()->create(['name' => $playerName]);
            $this->campaign->members()->attach($player, ['role' => CampaignRole::Player->value]);
            $entity = Entity::factory()->for($this->gm, 'owner')->for($this->campaign)->create(['name' => $characterName, 'entity_type_id' => EntityType::standard('character')->id]);
            $this->table[$characterName] = [$player, $this->campaign->playerCharacters()->create(['entity_id' => $entity->id, 'user_id' => $player->id])];
        }
    }

    public function test_the_dock_is_on_campaign_pages_but_not_on_the_messages_page(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];

        $this->actingAs($alex)->get(route('characters.show', [$this->campaign, $harvey]))->assertSeeLivewire(ChatDock::class);
        $this->actingAs($alex)->get(route('messages.index', $this->campaign))->assertDontSeeLivewire(ChatDock::class);
    }

    public function test_players_chat_in_the_group_and_privately_with_the_gm(): void
    {
        [$alex, $harvey] = $this->table['Harvey'];
        [$sam] = $this->table['Jack'];

        // Le MJ a un onglet Groupe et un par personnage ; le joueur, MJ et Groupe.
        $gmDock = Livewire::actingAs($this->gm)->test(ChatDock::class, ['campaign' => $this->campaign]);
        $this->assertSame(['group', (string) $harvey->id, (string) $this->table['Jack'][1]->id], $gmDock->instance()->tabs->pluck('key')->all());

        $gmDock->call('select', (string) $harvey->id)->set('body', 'Secret pour Harvey')->call('send')->assertHasNoErrors();

        Livewire::actingAs($alex)->test(ChatDock::class, ['campaign' => $this->campaign])
            ->call('select', 'group')
            ->set('body', 'Je fouille la bibliothèque.')
            ->call('send')
            ->assertHasNoErrors();

        $groupMessage = Message::whereNull('player_character_id')->sole();
        $this->assertSame($harvey->id, $groupMessage->sender_character_id);
        $this->assertSame('Harvey', $groupMessage->senderLabel());

        $this->assertSame('Harvey au groupe : Je fouille la bibliothèque.', $sam->unreadNotifications()->sole()->data['text']);
        $this->assertSame(1, $this->gm->unreadNotifications()->count());

        // Ouvrir l'onglet Groupe vaut lecture, notification comprise.
        session()->forget('chat.'.$this->campaign->id);
        $samDock = Livewire::actingAs($sam)->test(ChatDock::class, ['campaign' => $this->campaign])->call('select', 'group');
        $samDock->assertSee('Je fouille la bibliothèque.')->assertSee('Harvey');
        $samDock->call('select', 'gm')->assertDontSee('Secret pour Harvey');
        $this->assertSame(0, $sam->unreadNotifications()->count());
    }

    public function test_opening_a_tab_marks_it_read(): void
    {
        [$alex] = $this->table['Harvey'];

        Livewire::actingAs($this->gm)->test(ChatDock::class, ['campaign' => $this->campaign])
            ->call('select', 'group')->set('body', 'Pause de 10 minutes.')->call('send');
        // Le panneau ouvert est mémorisé dans la session du navigateur : ici, un autre navigateur.
        session()->forget('chat.'.$this->campaign->id);

        $dock = Livewire::actingAs($alex)->test(ChatDock::class, ['campaign' => $this->campaign]);
        $this->assertSame(1, $dock->instance()->unread);
        $this->assertSame(1, Message::unreadCount($alex, $this->campaign));

        $dock->call('select', 'group');
        $this->assertSame(0, Message::unreadCount($alex, $this->campaign));
        $this->assertSame(0, $alex->unreadNotifications()->count());
    }

    public function test_a_player_cannot_open_another_characters_conversation(): void
    {
        [$alex] = $this->table['Harvey'];
        [, $jack] = $this->table['Jack'];

        Livewire::actingAs($alex)->test(ChatDock::class, ['campaign' => $this->campaign])
            ->call('select', (string) $jack->id)
            ->assertNotFound();
    }
}
